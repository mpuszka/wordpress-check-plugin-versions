<?php
declare(strict_types=1);

/**
 * CheckAdmin class
 */
class CheckAdmin
{   
    /**
     * Constant with dashboard position number
     */
    private const DASHBOARD_POSITION = 65;

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'check_plugin_register_setting']);
    }

    /**
     * Add plugin page
     *
     * @return void
     */
    public function add_plugin_page(): void
    {
        add_menu_page(
            'Check Plugin', 
            'Check Plugin', 
            'manage_options', 
            'check-plugin', 
            [$this, 'check_plugin_content'],
            'dashicons-star-half',
            self::DASHBOARD_POSITION
        );
    }

    /**
     * Plugin content
     *
     * @return void
     */
    public function check_plugin_content(): void 
    {   
        echo '<div class="wrap">
        <h1>Check plugin</h1>
        <form method="post" action="options.php">';
    
            settings_fields( 'check_plugin_settings' ); 
            do_settings_sections( 'check-plugin-slug' ); 
            submit_button();
    
        echo '</form>
        </div>';
    }

    /**
     * Registration setting
     *
     * @return void
     */
    public function check_plugin_register_setting(): void 
    {        
        register_setting(
            'check_plugin_settings',
            'check_plugin_api_url',
            [$this, 'sanitize']
        );

        register_setting(
            'check_plugin_settings',
            'check_plugin_flag'
        );

        add_settings_section(
            'check_plugin_section_id',
            '', 
            '', 
            'check-plugin-slug' 
        );
        
        add_settings_field(
            'check_plugin_flag',
            'Active',
            [$this, 'check_plugin_flag_field_html'], 
            'check-plugin-slug',
            'check_plugin_section_id', 
            [
                'label_for' => 'check_plugin_flag'
            ]
        );

        add_settings_field(
            'check_plugin_api_url',
            'Url address',
            [$this, 'check_plugin_api_url_field_html'], 
            'check-plugin-slug',
            'check_plugin_section_id', 
            array( 
                'label_for' => 'check_plugin_api_url' 
            )
        );
    }

    /**
     * Api url field
     *
     * @return void
     */
    public function check_plugin_api_url_field_html(): void
    {
 
        $text = get_option( 'check_plugin_api_url' );
     
        printf(
            '<input type="text" id="check_plugin_api_url" name="check_plugin_api_url" value="%s" required />',
            esc_attr($text)
        );
     
    }

    /**
     * Api flag field
     *
     * @return void
     */
    public function check_plugin_flag_field_html(): void
    {
        $checked = (get_option( 'check_plugin_flag' ) == 1) ? 'checked' : '';
     
        echo '<input type="checkbox" id="check_plugin_flag" name="check_plugin_flag" value="1" '. $checked .' />';
    }

    /**
     * Sanitize for url field
     *
     * @param string $input
     * @return string
     */
    public function sanitize(string $input): string
    {   
        if (!isset($input) || '' === $input)
        {
            $input = 'listing';
        }  

        return trim($input, '/');
    }
}