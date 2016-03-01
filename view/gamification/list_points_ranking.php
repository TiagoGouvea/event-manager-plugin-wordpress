<?php
/*
Plugin Name: Custom List Table Example
Plugin URI: http://www.mattvanandel.com/
Description: A highly documented plugin that demonstrates how to create custom List Tables using official WordPress APIs.
Version: 1.3
Author: Matt Van Andel
Author URI: http://www.mattvanandel.com
License: GPL2
*/

use lib\Gamification;
use TiagoGouvea\PLib;

if(!class_exists('WP_List_Table2')){
    require_once(plugin_dir_path(__FILE__).'/../../vendor/class-wp-list-table.php' );
}

class ListTablePointsRanking extends WP_List_Table2 {
    function __construct(){
        parent::__construct( array(
            'singular'  => 'inscrito',
            'plural'    => 'Inscritos',
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    function column_default($item, $column_name){
//        var_dump($item['*idLevel']);
//        var_dump($item);
        $level = Gamification::getInstance()->getLevel($item['idLevel']);
//        var_dump($level);
//        die();
        switch($column_name){
            case 'nivel':
                return $level->getTitle();
//                return "<pre>".print_r($inscricao->pessoa()->getExtrasArray(),true)."</pre>";// $inscricao->pessoa()->getExtrasExibicao();
            default:
                return $item[$column_name];
                //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        /* @var $inscricao Inscricao */
//        var_dump($item);

        $pessoa = Pessoas::getInstance()->getById($item['idUser']);
        $title='<img src="'.$pessoa->getPictureUrl().'" style="width:60px; margin-right: 8px;">';
        $title.='<a href="admin.php?page=Pessoas&action=view&id='.$item[idUser].'">'.$pessoa->nome.'</a>';

        return $title;
    }

    function column_cb($item){
        return null;
    }

    function get_columns(){
        $columns = array(
            'title'     => 'Usuário',
            'points'     => 'Pontos',
            'nivel'    => 'Nível',
        );
        return $columns;
    }

    function get_sortable_columns() {
        return null;
        $sortable_columns = array(
            'title'     => array('nome',false),     //true means it's already sorted
            'email'    => array('email',false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        return array();
    }

    function process_bulk_action() {
    }

    function prepare_items($items=null) {
        $per_page = 500;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $data=array();
        $dataObj=array();
        /* @var $items \TiagoGouvea\PHPGamification\UserScore[] */
        if ($items)
            foreach ($items as $k=>$item){
                $dataObj[$item->id]=$item;
                $data[$k]=$item->getPublicVars();
            }
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        $this->itemsObj = $dataObj;
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}


function ListTablePointsRanking($ranking){
    $testListTable = new ListTablePointsRanking();
    $testListTable->prepare_items($ranking);
    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <?php
            echo "<h3>Ranking </h3>";
        ?>

        <a href="admin.php?page=Gamification&action=addEventoPessoa" class="add-new-h2">Adicionar Evento a Pessoa</a>
        <a href="admin.php?page=Gamification&action=addBadgePessoa" class="add-new-h2">Adicionar Badge a Pessoa</a>

        <?php $testListTable->display() ?>
    </div>
    <?php
}