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

class ListTableDescontos extends WP_List_Table2 {
    function __construct(){
        parent::__construct( array(
            'singular'  => 'desconto',
            'plural'    => 'Descontos',
            'ajax'      => false
        ) );
    }

    function column_default($item, $column_name){
        /* @var $desconto Desconto */
        $desconto = $this->itemsObj[$item['id']];
        switch($column_name){
            case 'desconto':
                if ($item['desconto_por']=='percentual')
                    return $item['desconto'].'%';
                else
                    return PLib::format_cash($item['desconto']);
                break;
            case 'inscritos':
                $inscritos = $desconto->getQtdInscritos();
                if ($inscritos>0)
                    $inscritos="<span style='color:blue;'>".$inscritos."</span>";
                $confirmados = PLib::coalesce($desconto->getQtdConfirmados(),0);
                $restantes = $desconto->getQuantidadeRestante();
                if ($confirmados>0 && $restantes==0)
                    $confirmados="<span style='color:green;'>".$confirmados."</span>";
                else if ($confirmados>0)
                    $confirmados="<span style='color:blue;'>".$confirmados."</span>";
                return $inscritos.'/'.$confirmados;
                break;
            case 'pessoa':
                return '<a href="admin.php?page=Pessoas&action=view&id='.$desconto->id_pessoa.'">'.$desconto->pessoa()->nome.'</a>';
                break;
            case 'validade':
                return PLib::date_relative($desconto->data_validade);
                break;
            case 'vagas':
                $restantes = $desconto->getQuantidadeRestante();
                if ($restantes==0)
                    $restantes="<span style='color:green;'>".$restantes."</span>";
                return $desconto->quantidade.'/'.$restantes;
                break;
            default:
                return $item[$column_name];
                //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        $actions = array(
            'edit'    => '<a href="admin.php?page=Descontos&action=edit&id='.$item['id'].'">Editar</a>',
            'delete'    => '<a href="admin.php?page=Descontos&action=delete&id='.$item['id'].'&id_evento='.$item['id_evento'].'">Excluir</a>'
        );

        return sprintf('%1$s %2$s',
            /*$1%s*/ '<b><a href="admin.php?page=Descontos&action=edit&id='.$item['id'].'">'. $item['ticket'].'</a></b>',
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function get_columns(){
        $columns = array(
            'title'     => 'Ticket',
            'pessoa'     => 'Pessoa',
            'validade'     => 'Validade',
            'desconto'     => 'Desconto',
            'vagas'     => 'Quantidade/Restante',
            'inscritos'     => 'Inscritos/Confirmados',
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
        if ($items)
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

function ListTableDescontos($items,$subTitulo){
    $testListTable = new ListTableDescontos();
    $testListTable->prepare_items($items);
    ?>

    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>

        <a href="admin.php?page=Descontos&action=add-new" class="add-new-h2">Novo Ticket</a>

        <?php $testListTable->display() ?>
    </div>
    <?php
}