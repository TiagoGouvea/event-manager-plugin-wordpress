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

class ListTableCategorias extends WP_List_Table2 {
    function __construct(){
        parent::__construct( array(
            'singular'  => 'categoria',
            'plural'    => 'Categorias',
            'ajax'      => false
        ) );
    }

    function column_default($item, $column_name){
        switch($column_name){
            case 'categoria':
                return $item['titulo'];
                break;
            default:
                return $item[$column_name];
                //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        /* @var $inscricao Inscricao */
        $actions = array(
            //'link'    => '<a href="'.$link.'" target=_blank>Link Wizard</a>',
            'edit'    => '<a href="admin.php?page=Categorias&action=edit&id='.$item['id'].'">Editar</a>',
            'delete'    => '<a href="admin.php?page=Categorias&action=delete&id='.$item['id'].'&id_evento='.$item['id_evento'].'">Excluir</a>'
        );


        return sprintf('%1$s %2$s',
            /*$1%s*/ '<b><a href="admin.php?page=Categorias&action=edit&id='.$item['id'].'">'. $item['titulo'].'</a></b>',
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item){
        return null;
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns(){
        $columns = array(
            'title'     => 'Categoria'
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
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
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

function ListTableCategorias($evento,$items,$subTitulo){
    $testListTable = new ListTableCategorias();
    //Plib::var_dump($items);die();
    $testListTable->prepare_items($items);

    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <?php
            menuEvento($evento,'evento-configuracao',null, 'Configurações', 'Categorias');
        ?>

        <a href="admin.php?page=Categorias&id_evento=<?php echo $evento->id; ?>&action=add-new" class="add-new-h2">Nova Categoria</a>

        <?php $testListTable->display() ?>
    </div>
    <?php
}