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

class ListTablePrecos extends WP_List_Table2 {
    function __construct(){
        parent::__construct( array(
            'singular'  => 'preço',
            'plural'    => 'Preços',
            'ajax'      => false
        ) );
    }

    function column_default($item, $column_name){
        /* @var $preco Preco */
        $preco = $this->itemsObj[$item['id']];
        switch($column_name){
            case 'categoria':
                return $item['titulo'];
                break;
            case 'valor':
                return PLib::format_cash($item['valor']);
                break;
            case 'inscritos':
                return $preco->getQtdInscritos().'/'.$preco->getQtdConfirmados();
                break;
            case 'vagas':
                return $preco->vagas.'/'.$preco->getVagasRestantes();
                break;
            case 'status':
                return ($item['encerrado']=='1' ? "Encerrado" : "Atual");
                break;
            default:
                return $item[$column_name];
                //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        $actions = array(
            //'link'    => '<a href="'.$link.'" target=_blank>Link Wizard</a>',
            'edit'    => '<a href="admin.php?page=Precos&action=edit&id='.$item['id'].'">Editar</a>',
            'delete'    => '<a href="admin.php?page=Precos&action=delete&id='.$item['id'].'&id_evento='.$item['id_evento'].'">Excluir</a>'
        );


        return sprintf('%1$s %2$s',
            /*$1%s*/ '<b><a href="admin.php?page=Precos&action=edit&id='.$item['id'].'">'. $item['titulo'].'</a></b>',
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
            'title'     => 'Preço',
            'valor'     => 'Valor',
            'inscritos'     => 'Inscritos/Confirmados',
            'vagas'     => 'Vagas/Restante',
            'status'     => 'Status'
        );
        return $columns;
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

function ListTablePrecos($evento,$items,$subTitulo){
    $testListTable = new ListTablePrecos();
    $testListTable->prepare_items($items);

    ?>

    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <?php
            menuEvento($evento,'evento-configuracao',null, 'Configurações','Preços');
        ?>

        <a href="admin.php?page=Precos&id_evento=<?php echo $evento->id; ?>&action=add-new" class="add-new-h2">Novo Preço</a>

        <?php $testListTable->display() ?>
    </div>
    <?php
}