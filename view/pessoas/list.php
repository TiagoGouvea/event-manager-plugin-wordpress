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

use TiagoGouvea\PLib;

if(!class_exists('WP_List_Table2')){
    require_once(plugin_dir_path(__FILE__).'/../../vendor/class-wp-list-table.php' );
}

class ListTablePessoas extends WP_List_Table2 {
    function __construct(){
        parent::__construct( array(
            'singular'  => 'pessoa',
            'plural'    => 'pessoas',
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    function column_default($item, $column_name){
        /** @var $pessoa Pessoa */
        $pessoa = $this->itemsObj[$item['id']];
        switch($column_name){
            case 'extras':
                return $pessoa->getExtrasExibicao(null,false);
//                return "<pre>".print_r($inscricao->pessoa()->getExtrasArray(),true)."</pre>";// $inscricao->pessoa()->getExtrasExibicao();
            case 'inscritos':
                return $pessoa->getCountInscricoes().'/'.$pessoa->getCountConfirmados();
//                return "<pre>".print_r($inscricao->pessoa()->getExtrasArray(),true)."</pre>";// $inscricao->pessoa()->getExtrasExibicao();
            default:
                return $item[$column_name];
                //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        /* @var $pessoa Pessoa */
        $pessoa = $this->itemsObj[$item['id']];
        $title='<div class=list_pessoa_title>
                    <a href="admin.php?page=Pessoas&action=view&id='.$item[id].'">'.PLib::truncate_words(Plib::capitalize_name($pessoa->nome,3),30).'</a><br>
                    <img src="'.$pessoa->getPictureUrl(80).'" style="width:80px; margin-right: 8px;"><br>
                    '.$pessoa->getExtrasExibicao(null,true) .'
                </div>';

        $actions = array();
//        if ($pessoa->get PLib::coalesce($inscricao->confirmado,0) == 0 )
//            $actions['delete'] = '<a href="admin.php?page=Inscricoes&action=delete&id=' . $item['id'] . '&id_evento=' . $item['id_evento'] . '">Excluir</a>';

        return $title. $this->row_actions($actions);
    }

    function column_cb($item){
        return null;
    }

    function get_columns(){
        $columns = array(
            'title'     => 'Nome',
            'extras'     => 'Extras',
            'inscritos'     => 'Inscriçoes / <br>Confirmados'
//            'inscricao'    => 'Situação Inscrição',
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
        foreach ($items as $k=>$item){
            $dataObj[$item->id]=$item;
            $data[$k]=(array)$item;
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


function ListTablePessoas($items,$subTitulo, $filter){
    $testListTable = new ListTablePessoas();
    $testListTable->prepare_items($items);

    add_action('admin_head', 'my_column_width');

    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <?php
            //menuEvento($evento,'inscricao-list',null, 'Inscrições');
            echo "<h3>$subTitulo</h3>";
            $nonce = wp_create_nonce("my_user_vote_nonce");
        ?>
        <a href="admin.php?page=Pessoas&action=add-new" class="add-new-h2">Incluir Pessoa</a>
        <a href="admin.php?page=Pessoas&action=extras" class="add-new-h2">Agrupar por Extras</a>
        <a href="admin.php?page=Pessoas&action=genderize" class="add-new-h2">Genderize</a>

        <?php $testListTable->display() ?>
    </div>
    <?php
}