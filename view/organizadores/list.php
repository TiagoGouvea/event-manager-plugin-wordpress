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

if(!class_exists('WP_List_Table2')){
    require_once(plugin_dir_path(__FILE__).'/../../vendor/class-wp-list-table.php' );
}
class ListTableOrganizadores extends WP_List_Table2 {

    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'organizador',     //singular name of the listed records
            'plural'    => 'organizadores',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }


    function column_default($item, $column_name){
        switch($column_name){
            default:
                return $item[$column_name];
                //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        
        $nonce = wp_create_nonce("my_user_vote_nonce");
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">Editar</a>',$_REQUEST['page'],'edit',$item['id']),
            'exportEmails'      => '<a href="admin-ajax.php?page=AdminOrganizador&action=exportarInscritosOrganizadorCsv&id='.$item['id'].'">Exportar emails de todos os inscritos em CSV</a>'
        );
        
        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['titulo'],
            /*$2%s*/ $this->row_actions($actions)
        );
    }


    function column_cb($item){
        return null;
    }


    function get_columns(){
        $columns = array(
            'title'         => 'Organizador'
        );
        return $columns;
    }


    function get_sortable_columns() {
        return null;
        $sortable_columns = array(
            'titulo'     => array('nome',false)     //true means it's already sorted
        );
        return $sortable_columns;
    }


    function get_bulk_actions() {
        $actions = array(
        );
        return $actions;
    }

    function prepare_items($items=null) {
        global $wpdb; //This is used only if making any database queries
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data=array();
        foreach ($items as $k=>$item){
            if (get_class($item)=='Evento')
                $item=$item->toArray();
            $data[$k]=(array)$item;
            //echo "<pre>";var_dump($data[$k]);die();
        }

        $current_page = $this->get_pagenum();
        
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}


function ListTableOrganizadores($itens,$titulo){
    
    //Create an instance of our package class...
    $testListTable = new ListTableOrganizadores();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items($itens);
    
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2><?php echo $titulo; ?></h2>

        <a href="admin.php?page=Organizadores&action=add-new" class="add-new-h2">Novo Organizador</a>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $testListTable->display() ?>
        </form>
        
    </div>
    <?php
}