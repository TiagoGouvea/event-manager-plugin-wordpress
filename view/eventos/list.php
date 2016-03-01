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

if (!class_exists('WP_List_Table2')) {
    require_once(plugin_dir_path(__FILE__) . '/../../vendor/class-wp-list-table.php');
}

class ListTableEventos extends WP_List_Table2
{


    function __construct()
    {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'evento', //singular name of the listed records
            'plural' => 'eventos', //plural name of the listed records
            'ajax' => false //does this table support ajax?
        ));
    }

    function column_default($item, $column_name)
    {
        $idEvento = $item['id'];
        /* @var $evento Evento */
        $evento = $this->itemsObj[$idEvento];
//        var_dump($evento);
//        var_dump($item);
        switch ($column_name) {
            // Obter total de visitantes
            case 'inscritos':
                global $wpdb;
                $return = null;
                $qtd = $evento->qtdPreInscritos();
                if ($qtd > 0)
                    $return ='Pré-inscritos: '.$qtd.'<br>';
                $return.=$evento->qtdInscritos() . " / " . $evento->qtdConfirmados().'<br>';

                if ($evento->pago=='pago'){
                    $return.="Visitantes Inscritos: <b>$evento->conversaoVisitantesInscritos</b><br>";
                    if (!$evento->preInscricao())
                        $return.="Inscritos Confirmados: <b>$evento->conversaoInscritosConfirmados</b><br>";
                }

                return $return;
                break;
            // Obter total de visitantes e pageviews
            case 'visitas':
                global $wpdb;
                // Visitantes unicos
                $qtd = getVisitantesEvento($idEvento);
                // PageView
                $qtd2 = getPageViewsEvento($idEvento);

                return $qtd . " / " . $qtd2;
                break;
            case 'data':
                if ($item['data'] == null) return null;
                $data = PLib::date_relative($item['data'] . " " . $item['hora'], false, false);
                if (strtotime($item['data']) > time()) {
                    $dias = PLib::days_between_dates($item['data']);
                    if ($dias>0)
                        $data .= "<br>" . $dias . " dias restantes";
                }
                return $data;
                break;
            case 'algomais':
                $return = null;
                if ($evento->noFuturo()) {
                    if ($evento->beta)
                        $return = '[BETA]<br>';
                    if ($evento->preInscricao())
                        $return.='[PRÉ-INSCRIÇÃO]<BR>';

                    if ($evento->pago == 'pago' && !$evento->preInscricao()){
                        $preco = $evento->getPrecoAtual();
                        if ($preco!=null) {
                            $vagasRestantes = $preco->getVagasRestantes();
                            if ($vagasRestantes <= 2)
                                $vagasRestantes = "<span style='color:orangered;'>$vagasRestantes</span>";
                            if ($vagasRestantes <= 0)
                                $vagasRestantes = "<span style='color:red;'>$vagasRestantes</span>";
                            $return .= "<b>Lote:</b> " . $preco->titulo . ' - ' . PLib::format_cash($evento->valor()) . ' - Vagas Restantes Lote: ' . $vagasRestantes . '<br>';
                        }
                    }
                    if ($evento->noFuturo() && !$evento->preInscricao()){
                        $vagasDisponiveis = $evento->vagasDisponiveis();
                        if ($vagasDisponiveis<=2)
                            $vagasDisponiveis = "<span style='color:orangered;'>$vagasDisponiveis</span>";
                        if ($vagasDisponiveis<=0)
                            $vagasDisponiveis = "<span style='color:red;'>$vagasDisponiveis</span>";
                        $return.="<b>Vagas Disponíveis Evento:</b> ".$vagasDisponiveis;
                    }

                    $erros = $evento->getErros();
                    if (count($erros['error']) > 0)
                        $return .= '<br><span style="color:red;"><b>Erros: </b>' . count($erros['error']) . '</span>';
                } else {
                    if ($evento->hasAvaliacao()){
                        $avaliacao1 = $evento->getAvaliacaoMediaPergunta(1);
                        $avaliacao2 = $evento->getAvaliacaoMediaPergunta(2);
                        $avaliacao3 = $evento->getAvaliacaoMediaPergunta(3);
                        $avaliacao = ($avaliacao1+$avaliacao2+$avaliacao3)/3;
                        $avaliacao = number_format(round(($avaliacao1+$avaliacao2+$avaliacao3)/3,1),1,'.','');
                        $return.='Avaliação<br><div id="score_'.$evento->id.'"></div>';
                        $return.="
                            <script>
                                jQuery(document).ready(function () {
                                    jQuery('#score_{$evento->id}').raty({
                                        score: $avaliacao,
                                        readOnly: true,
                                        starHalf : 'star-half-big.png',
                                        starOff : 'star-off-big.png',
                                        starOn  : 'star-on-big.png',
                                        path: '". plugins_url('/Eventos/public/img/')."'
                                    });
                                });
                            </script>";
                    }
                }
                return $return;
                break;
            default:
                return $item[$column_name];
            //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item)
    {
        $evento = $this->itemsObj[$item['id']];
        //Build row actions
        $actions = array(
            'edit' => sprintf('<a href="%s&action=%s&post=%s">Editar</a>', 'post.php?', 'edit', $item['id']),
            'inscricoes' => '<a href="admin.php?page=Eventos&action=inscricoes&id='.$item['id'].'">Inscrições</a>',
            'comunicacao' => '<a href="admin.php?page=Eventos&action=comunicacao&id='.$item['id'].'">Comunicação</a>',
            'configuracoes' => '<a href="admin.php?page=Eventos&action=configuracoes&id='.$item['id'].'">Configurações</a>'
        );

        if ($evento->hasAvaliacao())
           $actions['avaliacao']="<a href='admin.php?page=Eventos&action=avaliacoes&id=$evento->id'>Avaliações</a>";
        if ($evento->pago=='pago')
            $actions['financeiro']="<a href='admin.php?page=Eventos&action=financeiro&id=$evento->id'>Financeiro</a>";

        // Permitir apagar somente com algumas condições
        /** @var $evento Evento */
        $evento = $this->itemsObj[$item['id']];
        if ($evento->qtdInscritos() == 0)
            $actions['delete'] = "<a href='" . wp_nonce_url(get_admin_url() . "admin.php?page=Eventos&action=delete&id=$item[id]") . "'>Apagar</a>";

        // Actions temporáis - Concluir inscrições GBG/GDG
//        if ($item['data_fimInscricoes'] == '' || strtotime($item['data_fimInscricoes']) > time()) {
//            $actions['encerrar'] = '<a href="admin.php?page=Inscricoes&action=encerrarInscricoes&id_evento=' . $item['id'] . '">Encerrar</a>';
//        }

        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/
            '<a href="admin.php?page=Eventos&action=view&id=' . $item['id'] . '">' . $item['titulo'] . '</a>'. ($item['id_evento_pai']?' (filho)':''),
            /*$2%s*/
            $this->row_actions($actions)
        );
    }

    function column_cb($item)
    {
        return null;
    }

    function get_columns()
    {
        $columns = array(
            'title' => 'Evento',
            'data' => 'Data',
            'visitas' => 'Visitantes Únicos /<br>Page-Views',
            'inscritos' => 'Inscritos /<br>Confirmados',
            'algomais' => 'Algo Mais',
        );
        return $columns;
    }


    function get_sortable_columns()
    {
        return null;
        $sortable_columns = array(
            'title' => array('nome', false), //true means it's already sorted
            'email' => array('email', false)
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array();
        return $actions;
    }

    function process_bulk_action()
    {
        //Detect when a bulk action is being triggered...

    }

    function prepare_items($items = null)
    {
        $per_page = 100;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $data = array();
        $dataObj = array();
        foreach ($items as $k => $item) {
            $dataObj[$item->id] = $item;
            if (get_class($item) == 'Evento')
                $item = $item->toArray();
            $data[$k] = (array)$item;
        }
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items = $data;
        $this->itemsObj = $dataObj;
//        var_dump($this->itemsObj);
        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
        ));
    }


}


function ListTableEventos($itens, $titulo)
{
    if ($itens == null || count($itens) == 0) return;
//
//    echo "<pre>";
//    var_dump($itens);
//    echo "</pre>";
//    die();


    $testListTable = new ListTableEventos();
    $testListTable->prepare_items($itens);
    ?>
    <div class="wrap">

        <div id="icon-users" class="icon32"><br/></div>
        <h2><?php echo $titulo; ?></h2>

        <a href="admin.php?page=Eventos&action=add-new" class="add-new-h2">Novo Evento</a>

        <form id="movies-filter" method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
            <?php $testListTable->display() ?>
        </form>

    </div>
<?php
}

function ButtonNovoEvento()
{
    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Eventos</h2>

        <p>Este é um momento histórico, e após incluir o primeiro evento você nunca mais verá esta frase!</p>
        <a href="admin.php?page=Eventos&action=add-new" class="add-new-h2">Novo Evento</a>
    </div>
<?php
}