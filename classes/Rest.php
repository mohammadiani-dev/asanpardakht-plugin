<?php namespace APAPI;

use WP_REST_Request;

class Rest{

    public function __construct()
    {
        add_action('rest_api_init', array($this,'rest_api_creator'));
    }

    public function rest_api_creator(){

        $this->create_route('products/search','search_products');

    }

    public function create_route($namespace,$callback,$method = 'GET'){
        register_rest_route('asapi/v1', "/$namespace", array(
            'methods' => $method,
            'callback' => array($this,$callback),
            'permission_callback' => '__return_true',
        ));
    }

    public function search_products(WP_REST_Request $request){

        if (!current_user_can('administrator')) {
            wp_send_json([
                'messege' => "You don't have permission to access!",
                'code' => 403,
            ], 403);
        }

        wp_send_json(Products::searchProducts($request['query']));

    }

     


}




