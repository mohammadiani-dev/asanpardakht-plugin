<?php namespace APAPI;

use Avans\func;
use WP_REST_Request;

class Rest{

    public function __construct()
    {
        add_action('rest_api_init', array($this,'rest_api_creator'));
    }

    public function rest_api_creator(){
        $this->create_route('products/search','search_products');
        $this->create_route('users/search','search_users');
        $this->create_route('users/add','add_user','POST');
        $this->create_route('users/delete','delete_user','POST');
        $this->create_route('users/update','update_user','POST');
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

        if (!isset($request['query'])) {
            wp_send_json([
                'messege' => "query not set!",
                'code' => 401,
            ], 401);
        }

        wp_send_json(Funcs::searchProducts($request['query']));

    }

    public function search_users(WP_REST_Request $request){

        if (!current_user_can('administrator')) {
            wp_send_json([
                'messege' => "You don't have permission to access!",
                'code' => 403,
            ], 403);
        }

        if (!isset($request['query'])) {
            wp_send_json([
                'messege' => "query not set!",
                'code' => 401,
            ], 401);
        }

        wp_send_json(Funcs::searchUsers($request['query']));

    }

    public function add_user(WP_REST_Request $request){

        if (!current_user_can('administrator')) {
            wp_send_json([
                'messege' => "You don't have permission to access!",
                'code' => 403,
            ], 403);
        }

        if( !func::is_set($request['fullname']) ){
            wp_send_json([
                'result' => "fullname is not set!",
                'code' => 401,
            ], 401);
        }
        
        if( !func::is_set($request['mobile'])   ){
            wp_send_json([
                'result' => "mobile is not set!",
                'code' => 401,
            ], 401);
        }

        wp_send_json(Funcs::addUser($request));

    }

    public function delete_user(WP_REST_Request $request){

        if (!current_user_can('administrator')) {
            wp_send_json([
                'messege' => "You don't have permission to access!",
                'code' => 403,
            ], 403);
        }

        if( !func::is_set($request['mobile']) ){
            wp_send_json([
                'result' => "mobile is not set!",
                'code' => 401,
            ], 401);
        }

        $user = get_user_by("login",  trim($request['mobile'])  );

        if(!$user){
            wp_send_json([
                'result' => "user is not exist!",
                'code' => 401,
            ], 200);
        }

        $result = wp_delete_user( $user->ID );
        wp_send_json([
            'result' => $result,
            'code' => 200,
        ], 200);

    }

    public function update_user(WP_REST_Request $request){

        if (!current_user_can('administrator')) {
            wp_send_json([
                'messege' => "You don't have permission to access!",
                'code' => 403,
            ], 403);
        }

        if( !func::is_set($request['user_id']) ){
            wp_send_json([
                'result' => "user_id is not set!",
                'code' => 401,
            ], 401);
        }

        $user = get_user_by('ID',$request['user_id']);

        if(!$user){
            wp_send_json([
                'result' => "user is not exist!",
                'code' => 401,
            ], 200);
        }

        Funcs::updateUser( $user->ID , $request );

        wp_send_json([
            'result' => true,
            'code' => 200,
        ], 200);


    }


}




