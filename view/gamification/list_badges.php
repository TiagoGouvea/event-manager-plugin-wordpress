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

use TiagoGouvea\PHPGamification\Model\Badge;
use TiagoGouvea\PLib;

if(!class_exists('WP_List_Table2')){
    require_once(plugin_dir_path(__FILE__).'/../../vendor/class-wp-list-table.php' );
}

class ListTableBadges extends WP_List_Table2 {
    function __construct(){
        parent::__construct( array(
            'singular'  => 'pessoa',
            'plural'    => 'pessoas',
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    function column_default($item, $column_name){
        /* @var $item Badge */
        switch($column_name){
            case 'description':
                return $item->getDescription();
//                return "<pre>".print_r($inscricao->pessoa()->getExtrasArray(),true)."</pre>";// $inscricao->pessoa()->getExtrasExibicao();
            case 'imagem':
                return '<img src="'.get_template_directory_uri() . '/img/gamification/' . $item->getAlias() . '.png'.'" style="width: 80px;"/>';
            case 'alias':
                return $item->getAlias();
            default:
                return $item[$column_name];
                //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        /* @var $item Badge */
        $title='<div class=>
                    <a href="">'.$item->getTitle().'</a><br>
                </div>';

        $actions = array();
            $actions['edit'] = '<a href="admin.php?page=badges&action=edit&id=' . $item->getId() . '">Editar</a>';


        return $title. $this->row_actions($actions);
    }

    function column_cb($item){
        return null;
    }

    function get_columns(){
        $columns = array(
            'title'     => 'Nome',
            'imagem'     => 'Imagem',
            'alias'     => 'Alias',
            'description'     => 'Descrição'
        );
        return $columns;
    }

    function get_sortable_columns() {
        return null;
    }

    function get_bulk_actions() {
        return array();
    }

    function process_bulk_action() {
    }

    function prepare_items($items=null) {
        $per_page = 1500;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $data=array();
        $dataObj=array();
        /* @var $item Badge */
        foreach ($items as $k=>$item){
            $dataObj[$item->getId()]=$item;
            $data[$k]=$item;
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


function ListTableBadges($items,$subTitulo){
    $testListTable = new ListTableBadges();
    $testListTable->prepare_items($items);

    add_action('admin_head', 'my_column_width');
    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <?php
            echo "<h3>$subTitulo</h3>";
            $nonce = wp_create_nonce("my_user_vote_nonce");
        ?>
        <a href="admin.php?page=badges&action=add-new" class="add-new-h2">Incluir Badge</a>

        <?php $testListTable->display() ?>
    </div>
    <?php
}