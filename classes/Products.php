<?php namespace APAPI;

class Products{


    public static function searchProducts($s){
            $return = array();
            $search_string = esc_attr(trim($s));
            $args = array(
                'post_type' => array('product','product_variation'),
                'orderby' => 'title',
                'order' => 'ASC',
                'numberposts' => 200,
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
                            $return[] = array(
                                'ID' => (int)$child,
                                'title' => trim($title_ex[0]),
                                'atts' => $atts_trimed,
                            );
                        }
                    } else {
                        $return[] = array(
                            'ID' => (int)$post->get_ID(),
                            'title' => trim($post->get_name()),
                            'atts' => [],
                        );
                    }
                }
            }

            return $return;
    }
}