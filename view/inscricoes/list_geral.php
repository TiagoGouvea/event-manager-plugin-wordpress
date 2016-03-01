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

class ListTableInscritosGeral extends WP_List_Table2
{
    function __construct()
    {
        parent::__construct(array(
            'singular' => 'inscrito',
            'plural' => 'Inscritos',
            'ajax' => false        //does this table support ajax?
        ));
    }

    function column_default($item, $column_name)
    {
        /** @var $inscricao Inscricao */
        $inscricao = $this->itemsObj[$item['id']];
        if ($inscricao == null || $inscricao->pessoa() == null) return;
        switch ($column_name) {
            case 'inscricao':
                $situacao = $inscricao->getSituacaoString();
                $acoes = $inscricao->getSituacaoAcoes(true);
                return "<span id='inscricao_$inscricao->id'>$situacao $acoes</span>";
                break;
            case 'extras':
                return $inscricao->pessoa()->getExtrasExibicao();
//                return "<pre>".print_r($inscricao->pessoa()->getExtrasArray(),true)."</pre>";// $inscricao->pessoa()->getExtrasExibicao();
            case 'evento':
                return $inscricao->evento()->titulo;
            default:
                return $item[$column_name];
            //return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item)
    {
        /* @var $inscricao Inscricao */
        $inscricao = $this->itemsObj[$item['id']];
        $link = $inscricao->getLinkPagamento();

        if ($inscricao->pessoa()==null)
            return "Inscrição sem pessoa?";

        // Começar retorno
        $return='<a href="admin.php?page=Pessoas&action=view&id=' . $item['id_pessoa'] . '">' . PLib::capitalize_name($inscricao->pessoa->nome,35). '</a><br>
        <img src="'.$inscricao->pessoa()->getPictureUrl(80).'" style="width:80px; margin-right: 8px; float:left;">';
        $return.='</b> ';

        // Ações
        $actions = array(
            'link' => '<a href="' . $link . '" target=_blank>Link Wizard</a>',
            'edit' => '<a href="admin.php?page=Inscricoes&action=edit&id=' . $item['id'] . '">Editar</a>'
        );
        if (PLib::coalesce($inscricao->confirmado,0) == 0 )
            $actions['delete'] = '<a href="admin.php?page=Inscricoes&action=delete&id=' . $item['id'] . '&id_evento=' . $item['id_evento'] . '">Excluir</a>';

        // Contar inscrições
        $inscricoes = count(Inscricoes::getInstance()->getByPessoa($item['id_pessoa']));
        if ($inscricoes == 1)
            $inscricoes = "*";
        else {
            $inscricoes = "($inscricoes)";
        }
        $return .= $inscricoes . '<br>Ticket: ' . $item['id'];

        $return .= '<br>Inscrição: ' . Plib::date_relative($item['data_inscricao'],true) . " (" . PLib::days_between_dates($item['data_inscricao']) . " dias)";

        if ($item['id_preco'] != "")
            $return .= '<br>Categoria: ' . $inscricao->categoria()->titulo;

        if ($item['id_preco'] != "")
            $return .= '<br>Lote: ' . $inscricao->preco()->titulo;

        if ($inscricao->valor_inscricao)
            $return .= '<br>Valor Inscrição: ' . PLib::format_cash($inscricao->valor_inscricao);

//        if ($item['codigo_gateway']){
        //$return.= '<br>'.$item['codigo_gateway'];
//        }
        $return='<div class=list_inscricoes_title>'.$return.'</div>';
        $return .= ' ' . $this->row_actions($actions);

        return $return;
    }

    function column_cb($item)
    {
        return null;
    }

    function get_columns()
    {
        $columns = array(
            'title' => 'Inscrito',
            'evento' => 'Evento',
            'extras' => 'Extras Inscrito',
            'inscricao' => 'Situação Inscrição',
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

    function prepare_items($items = null)
    {
        $per_page = 500;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data = array();
        $dataObj = array();
        if ($items)
            foreach ($items as $k => $item) {
                $dataObj[$item->id] = $item;
                $data[$k] = (array)$item;
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


function ListTableInscritosGeral($items)
{
    $testListTable = new ListTableInscritosGeral();
    $testListTable->prepare_items($items);

    add_action('admin_head', 'my_column_width');

    function my_column_width()
    {
        echo '<style type="text/css">';
        echo '.column-data_inscricao { text-align: center; width:60px !important; overflow:hidden }';
        echo '.column-situacao { text-align: center; width:60px !important; overflow:hidden }';
        echo '</style>';
    }

    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <?php
        echo "<h2>Inscrições</h2>";
        $nonce = wp_create_nonce("my_user_vote_nonce");
        ?>

        <?php $testListTable->display() ?>

        <script>

            function formatMoney(valor, c, d, t) {
                var n = valor,
                    c = isNaN(c = Math.abs(c)) ? 2 : c,
                    d = d == undefined ? "." : d,
                    t = t == undefined ? "," : t,
                    s = n < 0 ? "-" : "",
                    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
                    j = (j = i.length) > 3 ? j % 3 : 0;
                return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
            }
            ;

            function confirmarInscricao(id, valor, formaPagamento) {
                var valorPago = '';
                if (valor) {
                    // Obter valor
                    var valorInformado = prompt("Confirmação de Pagamento", formatMoney(valor, 2, ',', '.'));
                    if (valorInformado == '')
                        return;
                    valorInformado = valorInformado.replace('.', '').replace(',', '.');
                    valorInformado = parseFloat(valorInformado);
                    valorPago = '&valor_pago=' + valorInformado + '&forma_pagamento=' + formaPagamento;
                }
                var nonce = '<?php echo wp_create_nonce("my_user_vote_nonce"); ?>';
                var url = '<?php echo admin_url(); ?>/admin-ajax.php?action=inscricao-confirmar&id=' + id + valorPago + '&nonce=' + nonce;
                // Ajax para atualizar registro
                getAjax(url, function (data) {
                    try {
                        console.log(data);
                        data = JSON.parse(data);
                        console.log(data);
                        console.log(data.html);
                        if (data.html != undefined)
                            jQuery('#inscricao_' + id).html(data.html);
                    } catch (error) {
                        alert('Houve uma falha ao exibir as informações. Entre em contato com o Suporte. Mensage: ' + error.message);
                    }
                });
            }

            function cancelarInscricao(id, confirmar) {
                var nonce = '<?php echo wp_create_nonce("my_user_vote_nonce"); ?>';
                var url = '<?php echo admin_url(); ?>/admin-ajax.php?action=inscricao-cancelar&id=' + id + '&nonce=' + nonce;
                // Ajax para atualizar registro
                getAjax(url, function (data) {
                    data = JSON.parse(data);
                    console.log(data.html);
                    if (data.html != undefined)
                        jQuery('#inscricao_' + id).html(data.html);
                });
            }

            function informarValorPagoInscricao(id, valor_pago, forma_pagamento) {
                var nonce = '<?php echo wp_create_nonce("my_user_vote_nonce"); ?>';
                var url = '<?php echo admin_url(); ?>/admin-ajax.php?action=inscricao-informar-valor&id=' + id + '&valor=' + valor_pago + '&forma_pagamento=' + forma_pagamento + '&nonce=' + nonce;
                // Ajax para atualizar registro
                getAjax(url, function (data) {
                    data = JSON.parse(data);
                    console.log(data.html);
                    if (data.html != undefined)
                        jQuery('#inscricao_' + id).html(data.html);
                });
            }

            function marcarPresenca(id) {
                var nonce = '<?php echo wp_create_nonce("my_user_vote_nonce"); ?>';
                var url = '<?php echo admin_url(); ?>/admin-ajax.php?action=inscricao-presenca&id=' + id + '&nonce=' + nonce;
                // Ajax para atualizar registro
                getAjax(url, function (data) {
                    data = JSON.parse(data);
                    console.log(data.html);
                    if (data.html != undefined)
                        jQuery('#inscricao_' + id).html(data.html);
                });
            }

        </script>
    </div>
    <?php
}