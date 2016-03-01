<?php
/**
 * First, we need pray, then, read this code
 */


// First, create a function that includes the path to your favicon
//function add_favicon() {
//    if file_exists()
//    $favicon_url = get_stylesheet_directory_uri() . '/images/icons/admin-favicon.ico';
//    echo '<link rel="shortcut icon" href="' . $favicon_url . '" />';
//}

//if (is_admin()) {
// Now, just make sure that function runs when you're on the login page and admin pages
//    add_action('login_head', 'add_favicon');
//    add_action('admin_head', 'add_favicon');
//}
use lib\WizardInscricao;
use TiagoGouvea\WPUtil;

/**
 * @return wpdb
 */
function wpdb()
{
    global $wpdb;
    return $wpdb;
}

function decode_json_utf8($str)
{
    return "aa";
//    return preg_replace_callback("/\\\u([[:xdigit:]]{4})/i", "ewchar_to_utf8", $str);
}

function wp_db_null_value($query)
{
//    \TiagoGouvea\PLib::var_dump($query);
    return str_ireplace("'NULL'", "NULL", $query);
}


function is_custom_admin()
{
    return strpos($_SERVER["REQUEST_URI"], '/admin/') !== false;
}

/**
 * Diz se existe uma pessoa autenticada
 * @return bool
 */
function is_autenticado(){
    if (session_id()==null)
        session_start();
    return $_SESSION!=null && $_SESSION['id_pessoa']!=null && $_SESSION['autenticado']==true;
}

function is_avaliacao(){
    return get_query_var('avaliacao',null);
}

function is_certificado(){
    return get_query_var('certificado',null);
}

function is_area_restrita(){
    return get_query_var('area_restrita',null)!=null;
}

function getVariaveis()
{
    $variaveis = "Pessoa: %pessoa_nome% %pessoa_primeiro_nome% %pessoa_password% %pessoa_email% %pessoa_celular% %pessoa_password%
                  Inscrição: %link_pagamento% %link_inscrito% %link_avaliacao% %link_certificado%
                  Evento: %evento_titulo% %evento_data_hora% %evento_local% %evento_local_endereco% %evento_local_telefone%";

    return $variaveis;
}

function getVisitantesEvento($id_evento)
{
    global $wpdb;
    $qtd = $wpdb->get_results("SELECT ip            FROM ev_visitante_evento
                                                WHERE id_evento = '" . $id_evento . "'
                                                GROUP BY ip, userAgent");
    return count($qtd);
}


function getPageViewsEvento($id_evento)
{
    global $wpdb;
    $qtd = $wpdb->get_row("SELECT count(*) as qtd FROM ev_visitante_evento WHERE id_evento = '" . $id_evento . "'");
    return $qtd->qtd;
}

function menuEvento(Evento $evento, $menu, $subMenu = null, $subTitulo = null, $tituloAtual = null)
{
    if ($evento==null) return;
    echo "<h2>$evento->titulo" . ($evento->confirmacao == "preinscricao" ? " (pré-inscrição)" : "") . ($evento->id_evento_pai?' (evento filho)':''). "</h2>";

    echo "<a href='admin.php?page=Eventos&action=view&id=$evento->id' class='add-new-h2'>Dashboard</a>";
    echo "<a href='admin.php?page=Eventos&action=inscricoes&id=$evento->id' class='add-new-h2'>Inscrições</a>";
    echo "<a href='admin.php?page=Eventos&action=comunicacao&id=$evento->id' class='add-new-h2'>Comunicação</a>";
    if ($evento->hasAvaliacao()){
        echo "<a href='admin.php?page=Eventos&action=avaliacoes&id=$evento->id' class='add-new-h2'>Avaliações</a>";
    }
    if ($evento->pago=='pago'){
        echo "<a href='admin.php?page=Eventos&action=financeiro&id=$evento->id' class='add-new-h2'>Financeiro</a>";
    }
    echo "<a href='admin.php?page=Eventos&action=configuracoes&id=$evento->id' class='add-new-h2'>Configurações</a>";
    echo "<a href='$evento->permalink()' style='margin-left: 20px;' class='add-new-h2' target='_blank'>Visualizar</a>";

    echo "<h2>$subTitulo</h2>";

    if ($menu == "evento-configuracao") {
        echo "<a href='post.php?action=edit&post=$evento->id' class='add-new-h2'>Editar Evento</a>";
        echo "<a href='admin.php?page=Precos&id_evento=$evento->id' class='add-new-h2'>Preços</a>";
        echo "<a href='admin.php?page=Categorias&id_evento=$evento->id' class='add-new-h2'>Categorias</a>";
        echo "<a href='admin.php?page=Descontos&id_evento=$evento->id' class='add-new-h2'>Tickets de Desconto</a>";
    }

    if ($menu == "inscricao-list") {
        echo "<a href='admin.php?page=Inscricoes&id_evento=$evento->id' class='add-new-h2'>Todos inscritos</a>";
        if ($evento->qtdPresentes() > 0)
            echo "<a href='admin.php?page=Inscricoes&id_evento=$evento->id&filter=presentes' class='add-new-h2'>Presentes</a>";
        echo "<a href='admin.php?page=Inscricoes&id_evento=$evento->id&filter=confirmados' class='add-new-h2'>Confirmados</a>";
        echo "<a href='admin.php?page=Inscricoes&id_evento=$evento->id&filter=naoConfirmados' class='add-new-h2'>Pendentes</a>";
        if ($evento->qtdPreInscritos()>0)
            echo "<a href='admin.php?page=Inscricoes&id_evento=$evento->id&filter=preInscritos' class='add-new-h2'>Pré-inscritos</a>";
        echo "<a href='admin.php?page=Inscricoes&id_evento=$evento->id&filter=filaEspera' class='add-new-h2'>Fila de espera</a>";
        echo "<a href='admin.php?page=Inscricoes&id_evento=$evento->id&filter=rejeitados' class='add-new-h2'>Cancelados</a>";
        if ($evento->campos_extras){
            echo "<a style='margin-left: 20px;' href='admin.php?page=Inscricoes&id_evento=$evento->id&action=extras' class='add-new-h2'>Extras</a>";
        }
        echo "<a style='margin-left: 20px;' href='admin.php?page=Inscricoes&id_evento=$evento->id&action=add' class='add-new-h2'>Inscrever Pessoa</a>";
        echo "<a style='margin-left: 20px;' href='admin.php?page=Inscricoes&id_evento=$evento->id&action=importarCsv' class='add-new-h2'>Importar CSV</a>";

    }

    if ($menu == "precoevento-list") {
        echo "<a href='admin.php?page=AdminPrecosEventos&action=add-new&id_evento=$evento->id' class='add-new-h2'>Novo preço</a>";
    }

    if ($menu == "desconto-list") {
        echo "<a href='admin.php?page=Desconto&action=add-new&id_evento=$evento->id' class='add-new-h2'>Novo Ticket</a>";
    }

    if ($tituloAtual != null)
        echo "<h2>$tituloAtual</h2>";

    $erros = $evento->getErros();
    if (count($erros['erros'])>0){
        admin_notice($evento);
    }
}


function templateLocate($arquivo, $argumentos = null)
{
    // Procurar na pasta do tempalte
    if (file_exists(get_stylesheet_directory() . '/' . $arquivo))
        return get_stylesheet_directory() . '/' . $arquivo;

    // Procurar na sub pasta tgo_evento no tempplate
    if (file_exists(get_stylesheet_directory() . '/tgo_evento/') && (file_exists(get_stylesheet_directory() . '/tgo_evento/' . $arquivo)))
        return get_stylesheet_directory() . '/tgo_evento/' . $arquivo;

    // Procurar na pasta do tempalte
    if (file_exists(PLUGINPATH . '/templates/' . $arquivo))
        return PLUGINPATH . '/templates/' . $arquivo;

    throw new Exception("Template necessário não encontrado na pasta do tema nem no plugin: " . $arquivo);
}


function templateInclude($arquivo, $argumentos = null)
{
    $template = templateLocate($arquivo);
    if ($argumentos != null)
        extract($argumentos);

    // Procurar na pasta do tempalte
    if ($template != null) {
        require_once $template;
        return;
    }
}

function timeStampTimeZoneFix($timestamp)
{
    $datetime = new DateTime();
    $datetime->setTimestamp($timestamp);
    //echo $datetime->format(DATE_ATOM)."<br>";
    $gmt0 = new DateTimeZone('UTC');
    $datetime->setTimezone($gmt0);
    //echo $datetime->format(DATE_ATOM);
    return $datetime->getTimestamp();
}

function validarPlugin()
{
    if (CONCATENATE_SCRIPTS == true) {
        die ('CONCATENATE_SCRIPTS deve ser definido como false em wp_config.<br><br>
             define(\'CONCATENATE_SCRIPTS\',false);');
    }
    if (TGO_EVENTO_ADMIN==null){
        die('O email do administrador deve ser definido na constante TGO_EVENTO_ADMIN em wp_config');
    }
}


//ini_set( 'error_reporting', -1 );
//ini_set( 'display_errors', 'On' );
//add_action( 'parse_request', 'debug_404_rewrite_dump' );
//add_action( 'template_redirect', 'debug_404_template_redirect', 99999 );
//add_filter ( 'template_include', 'debug_404_template_dump' );

function debug_404_rewrite_dump(&$wp)
{
    echo '<pre>';
    global $wp_rewrite;

    echo '<h2>rewrite rules</h2>';
    echo var_export($wp_rewrite->wp_rewrite_rules(), true);

    echo '<h2>permalink structure</h2>';
    echo var_export($wp_rewrite->permalink_structure, true);

    echo '<h2>page permastruct</h2>';
    echo var_export($wp_rewrite->get_page_permastruct(), true);

    echo '<h2>matched rule and query</h2>';
    echo var_export($wp->matched_rule, true);

    echo '<h2>matched query</h2>';
    echo var_export($wp->matched_query, true);

    echo '<h2>request</h2>';
    echo var_export($wp->request, true);

    global $wp_the_query;
    echo '<h2>the query</h2>';
    echo var_export($wp_the_query, true);
}

function debug_404_template_redirect()
{
    global $wp_filter;
    echo '<h2>template redirect filters</h2>';
    echo var_export($wp_filter[current_filter()], true);
}

function debug_404_template_dump($template)
{
    echo '<h2>template file selected</h2>';
    echo var_export($template, true);

    echo '</pre>';
    exit();
}

/**
 * Retorna o evento
 * @param bool $trazerPai
 * @return Evento
 */
function get_the_evento($trazerPai=false)
{
    global $theEvento;
    if (get_the_ID()) {
        if ((get_post_type(get_the_ID() ))=='tgo_evento')
            return Eventos::getInstance()->getById(get_the_ID(), $trazerPai);
    } else if ($theEvento)
        return $theEvento;
    return null;
}

global $theEvento;
function set_the_evento($evento){
    global $theEvento;
    $theEvento=$evento;
}


/**
 * Retorna a pessoa autenticada
 * @return Pessoa
 */
function get_the_pessoa(){
    if (!session_id())
        session_start();
    $idPessoa = $_SESSION['id_pessoa'];
    if ($idPessoa)
        return Pessoas::getInstance()->getById($idPessoa);
    return null;
}

function save_evento_meta($post_id, $post, $update)
{
    if ($post->post_type != 'tgo_evento') return;
    Eventos::getInstance()->setRewriteRule($post);
    flush_rewrite_rules_eventos();
}
add_action('save_post', 'save_evento_meta', 10, 3);

add_filter('post_type_link', 'get_post_type_link', 1, 3);
function get_post_type_link($link, $post = 0){
    if ( get_post_type( $post )=='tgo_evento' ) {
        $link = Eventos::getInstance()->getUrl($post);
    }
    return $link;
}

//add_action('init', 'flush_rewrite_rules_eventos',10);
function flush_rewrite_rules_eventos()
{
    global $wpdb;
    $querystr = "SELECT {$wpdb->posts}.post_name, post_title, {$wpdb->posts}.ID
                     FROM {$wpdb->posts}
                     WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'tgo_evento'";
    $posts = $wpdb->get_results($querystr, OBJECT);
    foreach ($posts as $post) {

        if (TGO_EVENTO_URL_MODE==0) {
            //        echo "--- $post->post_title<br>";
            $taxonomies = get_the_terms($post->ID, 'tgo_evento_tipo');
            if ($taxonomies != null && count($taxonomies) > 0) {
                foreach ($taxonomies as $taxonomy) {
                    //var_dump($taxonomy);
                    //die($taxonomy);
                    // "{$post->post_name}\\.{$suffix}\$" : "{$post->post_name}\$";
                    //                var_dump($taxonomy);
                    $regex = "^{$taxonomy->slug}\/{$post->post_name}\$";
                    //                var_dump($regex);
                    $rule = "index.php?tgo_evento={$post->post_name}";
                    //                var_dump($rule);
                    //                echo "<br>";
                    add_rewrite_rule($regex, $rule, 'top');
                }
            } else {
                //            die('no taxonomy');
                $regex = "^{$post->post_name}\$";
                $rule = "index.php?tgo_evento={$post->post_name}";
                add_rewrite_rule($regex, $rule, 'top');
            }
        }

        // Por categoria
        if (TGO_EVENTO_URL_MODE==1) {
            $post_categories = wp_get_post_categories($post->ID);
            if (count($post_categories)>0){
                foreach($post_categories as $c){
                    $slug=null;
                    $cat = get_category( $c );
                    $slug=$cat->slug;
                    if ($cat->category_parent){
                        while ($cat->category_parent){
                            $cat = get_category( $cat->category_parent );
                            $slug=$cat->slug."\/".$slug;
                        }
                    }
                    $regex = "^{$slug}\/{$post->post_name}\$";
                    $rule = "index.php?tgo_evento={$post->post_name}";
                    add_rewrite_rule($regex, $rule, 'top');

                    $terms[] = array( 'name' => $cat->name, 'slug' => $slug );
                }
            } else {
                $regex = "^{$post->post_name}\$";
                $rule = "index.php?tgo_evento={$post->post_name}";
                add_rewrite_rule($regex, $rule, 'top');
            }
        }
    }
    flush_rewrite_rules(false);
}

global $flashError, $flash, $flashWarning;

function setFlashError($message)
{
    global $flashError;
    if (is_array($message))
        $flashError = array_merge((array)$flashError, $message);
    else
        $flashError[] = $message;
}

function setFlashWarning($message)
{
    global $flashWarning;
    if (is_array($message))
        $flashWarning = array_merge((array)$flashWarning, $message);
    else
        $flashWarning[] = $message;
}

function getFlashErrorString()
{
    global $flashError;
    return join('<br>', $flashError);
}
function getFlashErrorArray()
{
    global $flashError;
    return $flashError;
}

function hasFlashError()
{
    global $flashError;
    return count($flashError) > 0;
}

function hasFlashWarning()
{
    global $flashWarning;
    return count($flashWarning) > 0;
}

function getFlashWarningString()
{
    global $flashWarning;
    return join('<br>', $flashWarning);
}

function setFlash($message)
{
    global $flash;
    if (is_array($message))
        $flash = array_merge((array)$flash, $message);
    else
        $flash[] = $message;
}

function hasFlash()
{
    global $flash;
    return count($flash) > 0;
}
function getFlashString()
{
    global $flash;
    return join('<br>', $flash);
}

function getEventoFacebookPixel()
{
    /* @var $evento Evento */
    $evento = get_the_evento();
    $wizard = WizardInscricao::getInstance();
    $trackAction=null;

//    var_dump($wizard->etapa);
    if ($wizard->etapa==DADOS_PESSOAIS){
        $trackAction='AddToCart';
    } else if ($wizard->etapa==CATEGORIAS){
    } else if ($wizard->etapa==PAGAMENTO){
        $trackAction='InitiateCheckout';
    } else if ($wizard->etapa==FIM){
        if ($wizard->inscricao->confirmado){
            if ($wizard->inscricao->valor_inscricao>0){
                $trackAction='Purchase';
            } else {
                $trackAction='AddPaymentInfo';
            }
        } else {
            $trackAction='CompleteRegistration';
        }
    }

    if ($trackAction==null)
        $trackAction="PageView";

//    var_dump($trackAction);

        // ViewContent
    // Track key page views (ex: product page, landing page or article)
    //fbq('track', 'ViewContent');
    // AddToCart
    // Track when items are added to a shopping cart (ex. click/landing page on Add to Cart button)
    //fbq('track', 'AddToCart');
    // InitiateCheckout
    // Track when people enter the checkout flow (ex. click/landing page on checkout button)
    //fbq('track', 'InitiateCheckout');
    // AddPaymentInfo
    // Track when payment information is added in the checkout flow (ex. click/landing page on billing info)
    //fbq('track', 'AddPaymentInfo');
    // Purchase
    // Track purchases or checkout flow completions (ex. landing on "Thank You" or confirmation page)
    //fbq('track', 'Purchase', {value: '1.00', currency: 'USD'});
    // Lead
    // Track when a user expresses interest in your offering (ex. form submission, sign up for trial, landing on pricing page)
    //fbq('track', 'Lead');
    // CompleteRegistration
    // Track when a registration form is completed (ex. complete subscription, sign up for a service)
    //fbq('track', 'CompleteRegistration');

    $output = "<!-- Facebook Conversion Code -->
    <script>
        console.log('track','$trackAction');
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
            document,'script','//connect.facebook.net/en_US/fbevents.js');
            fbq('init', '$evento->fb_conversion_track');
            fbq('track', '$trackAction');
        </script>
        <noscript>
        <img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id=$evento->fb_conversion_track&ev=$trackAction&noscript=1' />
        </noscript>
        <!-- End Facebook Pixel Code -->";
    echo $output;
}

function getEventoTwConversionTrack()
{
    /* @var $evento Evento */
    $evento = get_the_evento();
    $output = '<!-- Facebook Conversion Code -->
        <script src="//platform.twitter.com/oct.js" type="text/javascript"></script>
        <script type="text/javascript">
            twttr.conversion.trackPid("'.$evento->tw_conversion_track.'", { tw_sale_amount: 0, tw_order_quantity: 0 });
        </script>
        <noscript>
            <img height="1" width="1" style="display:none;" alt="" src="https://analytics.twitter.com/i/adsct?txn_id="'.$evento->tw_conversion_track.'"&p_id=Twitter&tw_sale_amount=0&tw_order_quantity=0" />
            <img height="1" width="1" style="display:none;" alt="" src="//t.co/i/adsct?txn_id="'.$evento->tw_conversion_track.'"&p_id=Twitter&tw_sale_amount=0&tw_order_quantity=0" />
        </noscript>
        ';
    echo $output;
}


// Busca nos post types de eventos, quando buscando ou vendo tags
add_filter('pre_get_posts', 'query_post_type');
function query_post_type($query) {
    if(is_category() || is_tag()) {
        $post_type = get_query_var('post_type');
        if($post_type)
            $post_type = $post_type;
        else
            $post_type = array('post','tgo_evento'); // replace cpt to your custom post type
        $query->set('post_type',$post_type);
        return $query;
    }
}


function admin_notice($evento = null)
{
    if ($evento == null) $evento = get_the_evento();
    if ($evento == null) return;
    // Validar evento
    $erros = $evento->getErros();
    if ($erros['error'])
        setFlashError($erros['error']);
    if ($erros['warning'])
        setFlashWarning($erros['warning']);

    if (!hasFlashError() && !hasFlashWarning()) return;
    if (hasFlashError()) {
        $message = getFlashErrorString();
        echo "<div class='error'> <p>$message</p></div>";
    }
    if (hasFlashWarning()) {
        $message = getFlashWarningString();
        echo "<div class='warning'> <p>$message</p></div>";
    }
}

add_action('admin_notices', 'admin_notice');



// Trazer os eventos para a query principal
function tgo_evento_home_loop($query)
{
    if (is_home() && $query->is_main_query())
        $query->set('post_type', array('post', 'tgo_evento'));
    return $query;
}

add_filter('pre_get_posts', 'tgo_evento_home_loop');

/**
 * Adicionar na fila de scripts o javascript do plugin
 */
function enqueueScripts()
{
//    wp_register_script('my-jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.js');
//    wp_register_script('my-jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js');
//    wp_enqueue_script('my-jquery-ui');
    wp_enqueue_script('jquery-ui-tabs');
//    wp_enqueue_script( 'jquery' );
    wp_enqueue_script('eventos', '/wp-content/plugins/Eventos/public/js/eventos.js');
    wp_enqueue_style('eventosCss', '/wp-content/plugins/Eventos/public/css/eventos.css');
    wp_enqueue_script('raty', '/wp-content/plugins/Eventos/public/js/raty-2.7.0/jquery.raty.js');
    wp_enqueue_style('ratyCss', '/wp-content/plugins/Eventos/public/js/raty-2.7.0/jquery.raty.css');
}

add_action('wp_enqueue_scripts', 'enqueueScripts');
add_action('admin_enqueue_scripts', 'enqueueScripts');


function enviarEmail($para, $assunto, $mensagem)
{
    WPUtil::mailSend($para, $assunto, $mensagem);
}

function get_user_role()
{
    global $current_user;
    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);
    return $user_role;
}