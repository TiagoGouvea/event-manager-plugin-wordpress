<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 02/08/14
 * Time: 15:00
 */

class Template {
    static function obterPorId($id) {
        global $wpdb;
        $post = $wpdb->get_row("SELECT * FROM wp_posts WHERE ID = '" . $id . "'", ARRAY_A);

        if ($post!=null){
            $metas = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE post_id = '" . $id . "'", ARRAY_A);
            //echo "<pre>";var_dump($metas);
            $data = array();
            foreach ($metas as $meta) {
                if ($meta['meta_value']!='')
                    $data[$meta['meta_key']] = $meta['meta_value'];
            }
            // var_dump($data); die();
            $registro = array_merge($post, $data);
            $registro = (object) $registro;
        }

        //echo "<pre>";// var_dump($registro); die();
        return $registro;
    }

    static function getTodos() {
        global $wpdb;
        $registros = $wpdb->get_results("SELECT * FROM wp_posts where post_type='tgo_template' and post_status='publish'");
        foreach ($registros as $key => $registro) {
            $registros[$key] = self::obterPorId($registro->ID);
        }

        return $registros;
    }

    static function getTodosArray(){
        $registros = self::getTodos();
        $array=array();
        foreach ($registros as $registro)
            $array[$registro->ID]=$registro->post_title;
        return $array;
    }

} 