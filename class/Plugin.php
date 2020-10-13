<?php
declare(strict_types=1);

/**
 * Plugin class
 */
class Plugin 
{   
    /**
     * Local wordpress version
     *
     * @var string
     */
    private $wp_version;

    /**
     * All plugins installed
     *
     * @var array
     */
    private $all_plugins;

    /**
     * All active plugins installed
     *
     * @var array
     */
    private $active_plugins;

    /**
     * Array with versions comparion of active plugins
     *
     * @var array
     */
    private $comparisons = [];

    /**
     * Plugins wordpress api url
     *
     * @var string
     */
    private $plugins_api = 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information';

    /**
     * Wordpress api version url
     *
     * @var string
     */
    private $wp_api = 'https://api.wordpress.org/core/version-check/1.7';

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->wp_version       = get_bloginfo('version');
        $this->all_plugins      = get_plugins();
        $this->active_plugins   = get_option('active_plugins'); 
    }

    /**
     * Return local wordpress version
     *
     * @return string
     */
    public function get_wp_version(): string
    {
        return $this->wp_version;
    }
    
    /**
     * Return wordpress plugin api url
     *
     * @return array
     */
    public function get_plugins_api(): string 
    {
        return $this->plugins_api;
    }

    /**
     * Return wordpress api version url
     *
     * @return string
     */
    public function get_wp_api(): string
    {
        return $this->wp_api;
    }
    
    /**
     * Return all plugins installed
     *
     * @return array
     */
    public function get_all_plugins(): array
    {
        return $this->all_plugins;
    }

    /**
     * Return all active plugins installed
     *
     * @return void
     */
    public function get_active_plugins()
    {
        return $this->active_plugins;
    }

    /**
     * Return lates wordpress version avaible in api
     *
     * @return string
     */
    public function get_latest_wordpress_version_avaible(): string
    {   
        $response = wp_remote_get($this->get_wp_api());

        if (200 == $response['response']['code']) 
        {
            $json_body  = $response['body'];
            $obj_body   = json_decode($json_body);
            $wp_version = $obj_body->offers[0]
                                    ->version;

            return $wp_version;
        }

        return '1.0';
    }

    /**
     * Return avaible plugin version by slug
     *
     * @param string $slug
     * @return string
     */
    public function get_plugin_version_from_repository(string $slug): ?string 
    {
        $url        = $this->get_plugins_api() . "&request[slugs][]={$slug}";
        $response   = wp_remote_get($url);
        $plugins    = json_decode($response['body']);

        foreach($plugins as $key => $plugin) {
            $version = $plugin->version;
        }

        return $version;
    }

    /**
     * Return comparison versions of plugins installed with api
     *
     * @return array
     */
    public function get_comparison_active_plugins_versions(): array 
    {
        $allPlugins     = $this->get_all_plugins(); 
        $activePlugins  = $this->get_active_plugins(); 
       
        $comparisons = [];
        foreach($allPlugins as $key => $value) 
        {
            if(in_array($key, $activePlugins)) 
            { 
                $slug           = explode('/',$key)[0]; 
                $repo_version   = $this->get_plugin_version_from_repository($slug);
                $key_from_name  = str_replace(':','',$value['Name']);
                $key_from_name  = str_replace(' ', '-', $key_from_name);

                $comparisons[$key_from_name] = [
                    'current'           => $value['Version'],
                    'latest'            => $repo_version,
                    'requires_update'   => ($repo_version > $value['Version']) ? true : false
                ];
            }
        }

        return $comparisons;
    }

    /**
     * Return array with specific format prepare for api
     *
     * @return array
     */
    public function prepare_api_data(): array
    {   
        $wp_version         = $this->get_wp_version();
        $wp_avaible_version = $this->get_latest_wordpress_version_avaible();

        $this->comparisons['wordpress'] = [
            'current'           => $this->get_wp_version(),
            'latest'            => $this->get_latest_wordpress_version_avaible(),
            'requires_update'   => ($wp_avaible_version > $wp_version) ? true : false
        ];

        $this->comparisons['plugins'] = $this->get_comparison_active_plugins_versions();

        return $this->comparisons;
    }
}