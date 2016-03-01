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
use TiagoGouvea\PHPGamification\Model\Event;

if (!class_exists('WP_List_Table2')) {
    require_once(plugin_dir_path(__FILE__) . '/../../vendor/class-wp-list-table.php');
}

class ListTableEvents extends WP_List_Table2
{
    function __construct()
    {
        parent::__construct(array(
            'singular' => 'evento',
            'plural' => 'eventos',
            'ajax' => false
        ));
    }

    function column_default($item, $column_name)
    {
        /* @var $item Event */
        switch ($column_name) {
            case 'description':
                return $item->getDescription();
//                return "<pre>".print_r($inscricao->pessoa()->getExtrasArray(),true)."</pre>";// $inscricao->pessoa()->getExtrasExibicao();
            case 'allow_repetitions':
                return ($item->getAllowRepetitions() ? "Sim" : "");
            case 'reach_required_repetitions':
                return $item->getRequiredRepetitions();
            case 'id_each_badge':
                if ($item->getIdEachBadge()){
                    $badge = Gamification::getInstance()->getBadge($item->getIdEachBadge());
                    return $badge->getTitle();
                }
                return null;
            case 'id_reach_badge':
                if ($item->getIdReachBadge()){
                    $badge = Gamification::getInstance()->getBadge($item->getIdReachBadge());
                    return $badge->getTitle();
                }
            case 'each_points':
                return $item->getEachPoints();
            case 'reach_points':
                return $item->getReachPoints();
//            case 'each_callback':
//                return $item->getEachCallback();
//            case 'reach_callback':
//                return $item->getReachCallback();

            case 'alias':
                return $item->getAlias();
            default:
                return null;
        }
    }

    function column_title($item)
    {
        /* @var $item Event */
        $title = '<div class=>
                    <a href="">' . $item->getDescription() . '</a><br>
                    ' . $item->getAlias() . '
                </div>';

        $actions = array();
        $actions['edit'] = '<a href="admin.php?page=eventos&action=edit&id=' . $item->getId() . '">Editar</a>';

        return $title . $this->row_actions($actions);
    }

    function column_cb($item)
    {
        return null;
    }

    function get_columns()
    {
        $columns = array(
            'title' => 'Descrição',
            'allow_repetitions' => 'Permite Repetição',
            'reach_required_repetitions' => 'Repetições Requeridas',
            'id_each_badge' => 'Badge - para cada',
            'id_reach_badge' => 'Badge - ao alcançar',
            'each_points' => 'Pontos - para cada',
            'reach_points' => 'Pontos - para alcançar',
//            'each_callback' => 'Callback - para cada',
//            'reach_callback' => 'Callback - ao alcançar',
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        return null;
    }

    function get_bulk_actions()
    {
        return array();
    }

    function process_bulk_action()
    {
    }

    function prepare_items($items = null)
    {
        $per_page = 1500;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $data = array();
        $dataObj = array();
        /* @var $item Event */
        foreach ($items as $k => $item) {
            $dataObj[$item->getId()] = $item;
            $data[$k] = $item;
        }
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items = $data;
        $this->itemsObj = $dataObj;
        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

}


function ListTableEvents($items, $subTitulo)
{
    $testListTable = new ListTableEvents();
    $testListTable->prepare_items($items);

    add_action('admin_head', 'my_column_width');
    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <?php
        echo "<h3>$subTitulo</h3>";
        $nonce = wp_create_nonce("my_user_vote_nonce");
        ?>
        <a href="admin.php?page=eventos&action=add-new" class="add-new-h2">Incluir Event</a>

        <?php $testListTable->display() ?>
    </div>
    <?php
}