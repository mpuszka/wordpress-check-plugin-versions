<?php
declare(strict_types=1);

/**
 * RestApi class
 */
class RestApi
{   
    /**
     * Constant reguest api method
     */
    private const REST_METHOD = 'POST';

    /**
     * Api route url
     *
     * @var string
     */
    private $api_url;

    /**
     * Api response data
     *
     * @var array
     */
    private $data = [];
    
    /**
     * Constructor
     *
     * @param string $url
     * @param array $data
     */
    public function __construct(string $url, array $data) 
    {   
        $this->api_url  = ('' === $url) ? 'listing' : $url;
        $this->data     = $data;

        add_action('rest_api_init', [$this, 'registerRestRoute']);
    }

    /**
     * Method to register rest api route
     *
     * @return void
     */
    public function registerRestRoute(): void
    {
        register_rest_route( 'check-plugin/v1/', $this->api_url, [
            'methods'   => self::REST_METHOD,
            'callback'  => [$this, 'rest_route_content'],
        ]);
    }
    
    /**
     * Response api route
     *
     * @return json
     */
    public function rest_route_content(): WP_REST_Response
    {   
        $response = new WP_REST_Response($this->data);
        $response->set_status(200);

        return $response;
    }
}