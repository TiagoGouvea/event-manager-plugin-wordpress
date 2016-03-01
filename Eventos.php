<?php
/*
Plugin Name: Eventos
Plugin URI: http://www.tiagogouvea.com.br
Description: Plugin para gestão de eventos
Version: 0.9.1
Author: Tiago Gouvêa
Author URI: http://www.tiagogouvea.com.br

- Quando enviar pela API cpf, pessoa não existir, depois no cadastro vier um email que já existe, terá que mesclar

Café bugs
- Não conseguiu incluir organizador
- Trazer as regras de taxonomias para o plugin, ao invés de depender de outro
- Pensar melhor onde será a pasta de logs e como exigir (não deixa ativar plugin)
- Conferir via código se tabelas existem e tal
- Abas não ficam marcadas quando muda
- Permitir configurar o desconto para pagamento em dinheiro
- Ter cron de duas em duas horas - Pode chamar consulta no pagseguro - Inscrições com cartão no pagseguro podem ser canceladas neste momento
- Criar todos os templates dentro do plugin
- Se inscrição cancelada, ao acessar link de pagamento (do email), pensar em
- Se não tiver um template de eventos, avisar algo, que não tem problema e tal.. e qual a vantagem de ter um template.

@TODO Criar os templates padrão do plugin, salvar em /templates/, validar se ele carrega os da raiz quando existentes
@TODO Adequar autoloader para todas as classes
@TODO Criar interface de configuração para ativar/desativar recursos extras
@TODO Adicionar Gateway de SMS como configuração
@TODO Detectar quando não houver as tabelas e criar no setup do plugin
@TODO Extras, retornar um campo "tipo" ao invés de trazer a string no título
*/
use lib\MonologFormatter;
use lib\WizardInscricao;
use Monolog\ErrorHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\PushoverHandler;
use TiagoGouvea\PLib;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use TiagoGouvea\WPUtil;

// Composer autoloader
require 'autoload.php';
require 'vendor/autoload.php';

// Boostrap
ini_set('session.bug_compat_warn', 0);
ini_set('session.bug_compat_42', 0);
ini_set('session.gc_maxlifetime', 60 * 60);
setlocale(LC_ALL, 'pt_BR');
date_default_timezone_set('America/Sao_Paulo');

// Enviroment
// Enviroment
if (WP_DEBUG == true) {
//    ini_set('xdebug.halt_level', E_STRICT);
    ini_set("display_errors", 0);
//    error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_STRICT);
} else {
    ini_set("display_errors", 0);
    error_reporting(0);
}
$nataraja = strpos($_SERVER['REQUEST_URI'], '/wordpress/wordpress_') !== false;
// Error log
if (WP_DEBUG == false || !$nataraja) {

    $titulo = 'Falha em Eventos';
    if (WP_DEBUG && !$nataraja)
        $titulo = 'DEV - Falha em Eventos';

    require_once 'lib/MonologFormatter.class.php';

    if (!file_exists(ABSPATH . '/log/'))
        die('É necessário existir a pasta ' . ABSPATH . '/log/');

    $log = new Logger('name');
    $mailer = new NativeMailerHandler(TGO_EVENTO_ADMIN, $titulo, TGO_EVENTO_ADMIN);
    $streamHandler = new StreamHandler(ABSPATH . '/log/monolog.log');
    $errHandler = new ErrorHandler($log);
    $errHandler->registerErrorHandler();
    $errHandler->registerExceptionHandler();
    $mailer->setFormatter(new MonologFormatter());
    $log->pushHandler($streamHandler, Logger::WARNING);
    $log->pushHandler($mailer, Logger::WARNING);
}

add_filter('wpseo_locale', 'override_og_locale');
function override_og_locale($locale)
{
    return "pt_BR";
}


// Requires iniciais
include_once 'lib/Cpp.php';
include_once 'functions.php';
include_once 'Shortcodes.php';
include_once 'vendor/TiagoGouvea/DataMapper/DomainObjectAbstract.class.php';
include_once 'vendor/TiagoGouvea/DataMapper/MapperAbstract.class.php';
include_once 'vendor/TiagoGouvea/WPDataMapper/WPSimpleMapper.class.php';
include_once 'vendor/TiagoGouvea/WPDataMapper/WPSimpleDAO.class.php';

if (is_admin()) {
    include_once 'classes/Template.class.php';
    include_once 'classes/Dashboard.class.php';
}

// Iniciar e finalizar objetos
add_action('init', 'tgo_evento_init', 1);
add_action('wp_logout', 'tgo_evento_end');
add_action('wp_login', 'tgo_evento_lgoin');
function tgo_evento_init()
{
    if (!session_id())
        session_start();
    Tgo_Evento::register_post_type();
    Tgo_Template::register_post_type();
}

function tgo_evento_end()
{
    session_destroy();
}

function tgo_evento_lgoin()
{
    session_destroy();
}

define('PLUGINPATH', ABSPATH . 'wp-content/plugins/Eventos');

// Rascunho de configurações
if (!defined('TGO_EVENTO_GAMIFICATION'))
    define('TGO_EVENTO_GAMIFICATION', false);
if (!defined('TGO_EVENTO_URL_MODE'))
    define('TGO_EVENTO_URL_MODE', 0);

/**
 * Instanciar classes
 */
function call_Evento()
{
    new Tgo_Evento();
    if (is_admin()) {
        new Tgo_Template();
    }
}

if (is_admin()) {
    add_action('load-post.php', 'call_Evento');
    add_action('load-post-new.php', 'call_Evento');
}

// Registrar menus do Plugin
add_action('admin_menu', 'add_menus');
// Dependendo do nivel do usuário e da quantidade de eventos, o menu pode ser APENAS do evento em questão
function add_menus()
{

    // Se for author, deixo apenas confirmar inscrições
    if (get_user_role() == 'author'){
        add_menu_page('Inscrições', 'Inscrições', 'read', 'Inscricoes', array('ControllerEventos', 'inscricoes'), 'dashicons-index-card', 6);
    } else {
        add_menu_page('Eventos', 'Eventos', 'edit_pages', 'Eventos', array('ControllerEventos', 'dispatcher'), 'dashicons-calendar-alt', 5);
        // Incluir já eventos no menu
        $eventos = Eventos::getInstance()->getAtuaisERecentes();
        if ($eventos)
            foreach ($eventos as $evento)
                add_submenu_page('Eventos', PLib::date_relative($evento->data) . ' - ' . $evento->titulo, PLib::date_relative($evento->data) . ' - ' . $evento->titulo, 'edit_pages', 'Eventos&action=view&id=' . $evento->id, 'AdminPresenca');

        add_menu_page('Inscrições', 'Inscrições', 'read', 'Inscricoes', array('ControllerEventos', 'inscricoes'), 'dashicons-index-card', 6);

        add_menu_page('Pessoas', 'Pessoas', 'edit_pages', 'Pessoas', array('ControllerPessoas', 'dispatcher'), 'dashicons-groups', 7);

        if (TGO_EVENTO_GAMIFICATION === true) {
            add_menu_page('Gamification', 'Gamification', 'edit_pages', 'Gamification', array('ControllerGamification', 'dispatcher'), 'dashicons-smiley', 8);
            add_submenu_page('Gamification', 'Importar Eventos', 'Importar Eventos', 'edit_pages', 'GamificationGDGJF', array('ControllerGamification', 'import'));
            add_submenu_page('Gamification', 'Badges', 'Badges', 'edit_pages', 'badges', array('ControllerGamification', 'dispatcher'));
            add_submenu_page('Gamification', 'Eventos', 'Eventos', 'edit_pages', 'eventos', array('ControllerGamification', 'dispatcher'));
        }

        add_menu_page('Apoio', 'Apoio', 'edit_pages', 'AdminApoio', 'AdminApoio', 'dashicons-nametag', 9);
        add_submenu_page('AdminApoio', 'Tickets de Desconto', 'Tickets de Desconto', 'edit_pages', 'TicketsDesconto', array('ControllerDescontos', 'dispatcher'));
        add_submenu_page('AdminApoio', 'Locais', 'Locais', 'edit_pages', 'Locais', array('ControllerLocais', 'dispatcher'));
        add_submenu_page('AdminApoio', 'Organizadores', 'Organizadores', 'manage_options', 'Organizadores', array('ControllerOrganizadores', 'dispatcher'));
        add_submenu_page('AdminApoio', 'Templates', 'Templates', 'manage_options', 'edit.php?post_type=tgo_template');
        add_submenu_page('AdminApoio', 'Mensagens', 'Mensagens', 'manage_options', 'Mensagens', array('ControllerMensagens', 'dispatcher'));
        add_submenu_page('AdminApoio', 'Integrações', 'Integrações', 'manage_options', 'Integracoes', array('ControllerIntegracoes', 'dispatcher'));
        //    add_submenu_page('AdminApoio', 'Migrar Dados', 'Migrar Dados', 'manage_options', 'MenuConfig', array('AdminConfig', 'adminConfigHome'));
        add_submenu_page('AdminApoio', 'Sincronizar Gateways', 'Sincronizar Gateways', 'manage_options', 'Sincronizar', array('ControllerInscricoes', 'sincronizarGateways'));
    }

    // Actions sem menu
    add_submenu_page(null, 'Categorias', 'Categorias', 'read', 'Categorias', array('ControllerCategorias', 'dispatcher'));
    add_submenu_page(null, 'Inscrições', 'Inscrições', 'read', 'Inscricoes', array('ControllerInscricoes', 'dispatcher'));
    add_submenu_page(null, 'Comunicação', 'Comunicação', 'read', 'Comunicacao', array('ControllerComunicacao', 'dispatcher'));
    add_submenu_page(null, 'Preços', 'Preços', 'read', 'Precos', array('ControllerPrecos', 'dispatcher'));
    add_submenu_page(null, 'Descontos', 'Descontos', 'read', 'Descontos', array('ControllerDescontos', 'dispatcher'));
    add_submenu_page(null, 'Pessoas', 'Pessoas', 'read', 'Pessoas', array('ControllerPessoas', 'dispatcher'));
}


// Adicionar filtro de exibição de template
add_filter('template_include', 'template_include_evento', 1);
function template_include_evento($template_path)
{
    // O conteudo é um post type tgo_evento
    if (get_post_type() == 'tgo_evento') {

        if (is_single()) {
            // Obter o evento
            // É uma inscrição?
            if (get_query_var('inscricao') != null) {
                if ($_GET['cancelar'] != null) {
                    // Cancelando inscrição, validar pessoa na url
                    /* @var $inscricao Inscricao */
                    $inscricao = Inscricoes::getInstance()->getById($_GET['ticket']);
                    if ($inscricao->id_pessoa != $_GET['cancelar'])
                        die();
                    $inscricao->cancelar();
                } else {
                    // Chamar wizard da inscrição (outra classe)
                    $etapa = $_POST['etapa'];
                    // Instanciar Wizard
                    $idEvento = get_the_ID();
                    $ticket = $_GET['ticket'];
                    $wizard = new WizardInscricao($idEvento, $ticket);
                    //                var_dump($etapa);
                    if ($etapa != '' && $etapa >= 1) {
                        $evento = get_the_evento();
                        if ($evento->fb_conversion_track)
                            add_action('wp_head', 'getEventoFacebookPixel');
                        if ($evento->tw_conversion_track)
                            add_action('wp_head', 'getEventoTwConversionTrack');
                    }

                    return PLUGINPATH . '/templates/wizard-inscricao.php';
                }
            } // Aplicando um Desconto?
            else if (get_query_var('servico') && get_query_var('ticket') != null) {
                // Serviço de pagamento externo
                ControllerInscricoes::direcionarServicoPagamento(get_query_var('servico'), get_query_var('ticket'));
                return;
            } // Avaliando um evento?
            else if (is_avaliacao()) {
                $template = ControllerQuestionarios::dispatcher('responder');
            } else if (is_certificado()) {
                ControllerInscricoes::certificado(get_query_var('certificado') / 13);
                return;
            } else if (get_query_var('mobile') != null) {
                $template = 'mobile.php';
            } // Aplicando um Desconto?
            else if (get_query_var('aplicarDesconto') != null) {
                // Chamar form para aplicar desconto
                $template = 'wizard-desconto.php';
            } else if (get_query_var('set_meio_pagamento') != null) {
                // Setar meio de pagamento
                ControllerInscricoes::setMeioPagamento($_SESSION['id_inscricao'], $_GET['set_meio_pagamento']);
                return;
            } else {
                $template = 'single-tgo_evento.php';
            }

            if ($template)
                $template_path = templateLocate($template);

            if ($template_path == null)
                die("Sem template ($template) para inclusão.");
        }
    }
    return $template_path;
}

add_action("wp_ajax_exportarInscritosOrganizadorCsv", "exportarInscritosOrganizadorCsv");
function exportarInscritosOrganizadorCsv()
{
    //$id_organizador = $_GET['id'];
    //$organizador = Organizador::obterPorId($id_organizador);
    $inscritos = Organizadores::getInstance()->getTodosInscritos();
    $nInscritos = array();
    foreach ($inscritos as $inscrito)
        if ($inscrito->email != null)
            $nInscritos[$inscrito->email] = $inscrito;

    $cabecalho = array();
    foreach ($nInscritos[$inscrito->email] as $campo => $valor) {
        $cabecalho[0][$campo] = $campo;
    }

    $nInscritos = array_merge($cabecalho, $nInscritos);
    //var_dump($inscritos);die();
    Plib::array_to_csv_download($nInscritos, 'Inscritos.csv');
    die();
}

add_action("wp_ajax_exportarCsv", "inscricoesExportarCsv");
function inscricoesExportarCsv()
{
    ControllerInscricoes::dispatcher();
}

add_action("wp_ajax_exportarCsvFullContacts", "inscricoesExportarCsvFullContacts");
function inscricoesExportarCsvFullContacts()
{
    ControllerInscricoes::dispatcher();
}

//add_action("wp_ajax_presencaImprimir", "presencaImprimir");
//function presencaImprimir()
//{
//    $id_evento = $_GET['id_evento'];
//    if ($id_evento)
//        $eventos = array(Eventos::getInstance()->obterPorId($id_evento));
//    else
//        $eventos = Eventos::getInstance()->getTodos();
//
//    foreach ($eventos as $evento) {
//        echo "<font face=Tahoma><center><h3>$evento->post_title<h3>";
//        echo "<h4>Presentes no evento</h4>";
//        $inscritos = Inscricao::obterPorEventoConfirmados($evento->id);
//        echo "<table width='100%' border=1 cellspacing=0 >";
//        echo "<tr style='font-size:15px;'><th>Nome</th><th>Presente</th></tr>";
//        foreach ($inscritos as $inscrito) {
//            echo "<tr style='font-size:12px;line-height:20px; padding:5px;'><td>" . $inscrito->nome . "</td><td>&nbsp</td></tr>";
//        }
//        echo "</table>";
//        echo "Confirmados: " . count($inscritos);
//    }
//    die();
//}

add_action("wp_ajax_inscricao-cancelar", 'inscricaoCancelar');
function inscricaoCancelar()
{
    ControllerInscricoes::dispatcher();
}

add_action("wp_ajax_inscricao-confirmar", 'inscricaoConfirmar');
function inscricaoConfirmar()
{
    ControllerInscricoes::dispatcher();
}

add_action("wp_ajax_inscricao-informar-valor", 'inscricaoInformarValor');
function inscricaoInformarValor()
{
    ControllerInscricoes::dispatcher();
}

add_action("wp_ajax_inscricao-presenca", 'inscricaoPresenca');
function inscricaoPresenca()
{
    ControllerInscricoes::dispatcher();
}

function InscricoesPagamento()
{
    require_once 'admin/PagSeguro-consulta.php';
}

add_filter('query_vars', 'query_vars_evento');

function query_vars_evento($vars)
{
    return array('certificado', 'area_restrita', 'inscricao', 'avaliacao', 'servico', 'ticket', 'pre', 'aplicarDesconto', 'transaction_id', 'set_meio_pagamento') + $vars;
}


// Operações por url
// URL chamada
$request = $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//var_dump($request);
//die();
// Carregar API?
$api = site_url() . '/api/';
if (stripos($request, $api) !== false) {
    ControllerApi::dispatcher();
    die();
}

// Executar o CRON?
if (strpos($request, site_url() . '/crontab/') !== false) {
    if (strpos($request, site_url() . '/crontab/dia/') !== false)
        ControllerCrontab::dia();
    if (strpos($request, site_url() . '/crontab/hora/') !== false)
        ControllerCrontab::hora();
    die();
}

// Aunteticar?
if ($_POST && $_POST['controller'] == 'pessoa') {
//    var_dump($_POST);
    if ($_POST['action'] == 'autenticar')
        ControllerPessoas::autenticar();
    if ($_POST['action'] == 'recuperar-senha')
        ControllerPessoas::recuperarSenha();
    if ($_POST['action'] == 'atualizar-perfil')
        ControllerPessoas::atualizarPerfil();

}

if (isset($_GET['logout'])) {
    ControllerPessoas::logout();
}

// Recebendo uma notificação de um gateway?
if (isset($_GET['notificacao_gateway']) && isset($_GET['id_integracao'])) {
    ControllerInscricoes::processarNotificacao($_GET['id_integracao'], PLib::coalesce($_POST['notificationCode'], $_GET['notificationCode']));
    exit();
}

if (($_GET['page'] == 'Eventos' && $_GET['id'] != null)) {
    set_the_evento(Eventos::getInstance()->getById($_GET['id']));
}

validarPlugin();