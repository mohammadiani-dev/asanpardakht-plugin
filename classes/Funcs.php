<?php namespace APAPI;

use TotalPollVendors\TotalCore\Helpers\Strings;
use WP_User;

class Funcs{

    
    public static function get_umeta($user_id,$type){
        return get_user_meta( $user_id, $type , true );
    }
    
    public static function is_set($var){
        if(!empty($var) && isset($var)){
            return true;
        }
        return false;
    }

    public static function searchProducts($str){
            $return = array();
            $search_string = esc_attr(trim($str));
            $args = array(
                'post_type' => array('product','product_variation'),
                'orderby' => 'title',
                'order' => 'ASC',
                'numberposts' => 20,
                's' => $search_string,
            );

            $posts = wc_get_products($args);
            if (!empty($posts)) {
                foreach ($posts as $index=>$post) {
                    if ($post->get_type() == 'variable') {
                        $product = wc_get_product($post->get_id());
                        $childs = $product->get_children();
                        foreach ($childs as $child) {
                            $product = wc_get_product($child);
                            $title_ex = explode("-",$product->get_name());
                            $atts = explode(',',$title_ex[1]);
                            $atts_trimed = [];
                            foreach($atts  as $att){
                                $atts_trimed[] = trim($att);
                            }

                            $postThumb = get_the_post_thumbnail_url( $child , 'thumbnail' );
                            $salePrice = $product->get_sale_price();
                            $stockQuantity = $product->get_stock_quantity();

                            $return[] = array(
                                'id' => (int)$child,
                                'title' => trim($title_ex[0]),
                                'image' => $postThumb,
                                'price' => (float)$product->get_price(),
                                'sale_price' => !empty($salePrice) ? (float)$salePrice : false,
                                'stock' => null !== $stockQuantity ? $stockQuantity : false,
                                'attributes' => $atts_trimed,
                            );
                        }
                    } else {
                        $postThumb = get_the_post_thumbnail_url( $post->get_ID() , 'thumbnail' );
                        $salePrice = $post->get_sale_price();
                        $stockQuantity = $post->get_stock_quantity();

                        $return[] = array(
                            'id' => (int)$post->get_ID(),
                            'title' => trim($post->get_name()),
                            'image' => $postThumb,
                            'price' => (float)$post->get_price(),
                            'sale_price' => !empty($salePrice) ? (float)$salePrice : false,
                            'stock' => null !== $stockQuantity ? $stockQuantity : false,
                            'attributes' => [],
                        );
                    }
                }
            }

            return $return;
    }

    public static function searchUsers($str){
        $return = array();

        $args = array(
            'order' => 'DESC',
            'orderby' => 'ID',
            'search' => '*'.esc_attr(trim($str)).'*',
            'search_columns' => array( 'user_login', 'user_login', 'user_nicename', 'user_email'),
            'number' => 20,
        );


        $users = get_users($args);
        if (!empty($users)) {
            foreach ($users as $index=>$user) {  
                $avatar = get_avatar_url($user->ID);   
                $mobile = self::get_user_mobile($user->ID);           
                $return[] = array(
                    'id' => (int)$user->ID,
                    // 'image' => $avatar,
                    'fullname' => $user->display_name,
                    'username' => $user->user_login,
                    'mobile' => null !== $mobile ? $mobile : "",
                    'address' => self::get_user_address($user->ID),
                    'zipcode' => self::get_user_zipcode($user->ID),
                );

            }
        }
        
        return $return;
    }

    public static function get_user_zipcode($user_id){
        return self::get_umeta($user_id,'billing_postcode');
    }

    public static function get_user_address($user_id){

        $as_address = get_user_meta($user_id, 'as_order_address', true);

        if(self::is_set($as_address)){
            return $as_address;
        }

        $state    = self::get_umeta($user_id,'billing_state');
        $city     = self::get_umeta($user_id,'billing_city');
        $address1 = self::get_umeta($user_id,'billing_address_1');
        $address2 = self::get_umeta($user_id,'billing_address_2');


        $return = [];
        
        if(self::is_set($state)){
            $return[] = $state;
        }
        if(self::is_set($city)){
            $return[] = $city;
        }
        if(self::is_set($address1)){
            $return[] = $address1;
        }
        if(self::is_set($address2)){
            $return[] = $address2;
        }

        return implode("-",$return);

    }

    public static function get_user_mobile($user_id , $order=null){

        if(self::is_set($order)){
            $mobile = $order->get_billing_phone();
            if(self::is_set($mobile)){
                return $mobile;
            }
        }

        if((int)$user_id <= 0){
            return null;
        }

        $user_mobile = get_user_meta($user_id, 'billing_phone', true);

        if (self::is_set($user_mobile)) {
            return $user_mobile;
        }

        $digits_phone = get_user_meta($user_id,'digits_phone',true);
        if (self::is_set($digits_phone)) {
            return $digits_phone;
        }

        $wupp_mobile = get_user_meta($user_id, 'wupp_mobile', true);
        if (self::is_set($wupp_mobile)) {
            $wupp_country_code = get_user_meta($user_id, 'wupp_country_code', true);
            return $wupp_country_code.$wupp_mobile;
        }

        $wpyarud_phone = get_user_meta($user_id, 'wpyarud_phone', true);

        if (self::is_set($wpyarud_phone)) {
            return $wpyarud_phone;
        }


        return null;
    }

    public static function addUser($data){

        $fullname = esc_html($data['fullname']);
        $mobile   = esc_html($data['mobile']);
        $zipcode  = esc_html($data['zipcode']);
        $address  = esc_html($data['address']);

        $user_login = sanitize_user( $mobile );
        if(username_exists( $user_login )){
            return [
                'result' => "user has exist!",
                'code' => 401,
            ];
        }
        
        $user_pass = wp_generate_password( 8, false );
        $user_id   = wp_create_user( $user_login , $user_pass );

        if(! $user_id || is_wp_error( $user_id )){
            return [
                'result' => 'wordpress create user error!',
                'code' => 500,
            ];
        }
        
        update_user_meta($user_id, 'billing_phone'  , $mobile);
        update_user_meta($user_id, 'as_order_address', $address);
        update_user_meta($user_id, 'billing_postcode', $zipcode);
        update_user_meta($user_id, 'as_user_fullname', $fullname);
        update_user_meta($user_id, 'display_name', $fullname );
        
        update_user_meta( $user_id, 'default_password_nag', true );

        do_action( 'register_new_user', $user_id );

        return [
            'result' => $user_id,
            'code' => 200,
        ];
    }

    public static function updateUser($user_id , $data){

        if(self::is_set(    trim($data['mobile'])     )){
            update_user_meta($user_id, 'billing_phone'  , trim($data['mobile']) );
        }

        if(self::is_set(    trim($data['address'])     )){
            update_user_meta($user_id, 'as_order_address', trim($data['address']) );
        }

        if(self::is_set(    trim($data['zipcode'])     )){
            update_user_meta($user_id, 'billing_postcode', trim($data['zipcode']) );
        }

        if(self::is_set(    trim($data['zipcode'])     )){
            update_user_meta($user_id, 'as_user_fullname', trim($data['fullname']) );
            update_user_meta($user_id, 'display_name', trim($data['fullname']) );
        }

    }




}