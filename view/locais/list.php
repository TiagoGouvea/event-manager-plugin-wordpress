<?php

if(!class_exists('WP_List_Table2')){
    require_once(plugin_dir_path(__FILE__).'/../../vendor/class-wp-list-table.php' );
}

class ListTableLocais extends WP_List_Table2 {
    function __construct(){
        global $status, $page;
        parent::__construct( array(
            'singular'  => 'local',     //singular name of the listed records
            'plural'    => 'locais',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

    }

    function column_default($item, $column_name){
        switch($column_name){
//            case 'inscritos':
//                global $wpdb;
//                $qtd = $wpdb->get_row("select count(*) as qtd from ev_locais where id_preco=".$item['id']);
//                $qtd=$qtd->qtd;
//                if ($qtd==0) $qtd=null;
//                return $qtd;
//                break;
            default:
                return $item[$column_name];
            //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">Editar</a>',$_REQUEST['page'],'edit',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Apagar</a>',$_REQUEST['page'],'delete',$item['id']),
        );
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['titulo'],
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item){
        return;
    }

    function get_columns(){
        $columns = array(
            'title'     => 'Titulo'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('titulo',false)
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        return array();
    }

    function process_bulk_action() {
    }

    function prepare_items($items=null) {
        global $wpdb; //This is used only if making any database queries
        $per_page = 30;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $data=array();
        foreach ($items as $k=>$item){
            $data[$k]=(array)$item;
        }

        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}


function ListTableLocais($itens){

    //Create an instance of our package class...
    $testListTable = new ListTableLocais();
    //Fetch, prepare, sort, and filter our data...
    if ($itens!=null)
        $testListTable->prepare_items($itens);
    ?>
    <div class="wrap">

        <div id="icon-users" class="icon32"><br/></div>
        <h2>Locais</h2>

        <a href="admin.php?page=Locais&action=add-new" class="add-new-h2">Novo Local</a>

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