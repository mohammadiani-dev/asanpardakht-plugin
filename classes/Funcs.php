<?php namespace APAPI;

class Funcs{


    public static function searchProducts($s){
            $return = array();
            $search_string = esc_attr(trim($s));
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
                                'image' => $postThumb !== false ? $postThumb : "",
                                'price' => (float)$product->get_price(),
                                'sale_price' => empty($salePrice) ? -1 : (float)$salePrice,
                                'stock' => null !== $stockQuantity ? $stockQuantity : 99999,
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
                            'image' => $postThumb !== false ? $postThumb : "",
                            'price' => (float)$post->get_price(),
                            'sale_price' => empty($salePrice) ? -1 : (float)$salePrice,
                            'stock' => null !== $stockQuantity ? $stockQuantity : 99999,
                            'attributes' => [],
                        );
                    }
                }
            }

            return $return;
    }

    public static function searchUsers($s){

    }


}