<?php
///**
// * Created by PhpStorm.
// * User: TiagoGouvea
// * Date: 01/12/14
// * Time: 09:05
// */
///** @var $evento Evento */
//// Requiser Wordpress config and objects
//define('WP_USE_THEMES', false);
//global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
//
//if ($_SERVER[HTTP_HOST]=="localhost" || $_SERVER[ HTTP_HOST]=="192.168.25.53")
//    require('/Users/TiagoGouvea/www/wordpress/wordpress_inspirar/wp-load.php');
//else
//    require(dirname(__FILE__).'/wp-load.php');
//
//require plugin_dir_path(__FILE__).'/wp-content/plugins/Eventos/vendor/autoload.php'; //Slim/Slim.php';
////require plugin_dir_path(__FILE__).'/wp-content/plugins/Eventos/Eventos.php';
//
//header('Content-Type: text/html; charset=utf-8');
//
//// Obter evento de hoje
//
//$evento = Eventos::obterPorId(1027);
//
//echo "<head>
//    <title>#GDGJF</title>
//    <style>body { font-family: 'tahoma' }</style>
//    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0'>
//</head>";
//
//
//echo "<h2>".$evento->post_title."</h2>";
//
//if ($_GET['inscricao']){
//
//    echo "<a href=mobile.php>Ver Inscritos</a>";
//
//    if ($_POST[email]){
//        $email = strtolower( $_POST['email']);
//        $nome = ucwords(strtolower($_POST['nome']));
//
//        // Certificar pessoa
//        $pessoa = Pessoa::obterPorEmail($email);
//        if ($pessoa == null || $pessoa==false) {
//            $pessoaArray=array('nome'=>$nome);
//            $pessoa = new stdClass();
//            $pessoa->newsletter = true;
//            $pessoa = Pessoa::certificarPessoa($email,null,$pessoaArray);
//        }
//
//        // Certificar inscrição
//        $inscricao = Inscricoes::getInstance()->certificarInscricao($evento,$pessoa,false,null);
//        Inscricao::confirmarInscricao($inscricao);
//
//        echo "<br><br>Inscrição de <b>".$nome."</b> confirmada!<br><BR>";
//    }
//
//    echo "<h3>Nova Inscrição</h3>";
//
//    ?>
<!---->
<!---->
<!---->
<!--    <form method="post" action="">-->
<!--        Nome:<br>-->
<!--        <input type="text" name="nome" size="40"><br><br>-->
<!---->
<!--        Email:<br>-->
<!--        <input type="text" name="email" size="40"><br><br>-->
<!---->
<!--        <input type="submit" value="Inscrever">-->
<!--    </form>-->
<!---->
<!--    <br>-->
<!---->
<!--    --><?//
//
//} else {
//
//        echo "<a href=mobile.php?inscricao=1>Nova inscrição</a>";
//        echo "<h3>Inscritos</h3>";
//        inscritos($evento->ID);
//
//        echo "<br><br><a href=mobile.php?inscricao=1>Nova inscrição</a>";
//}
//
//
//function inscritos($id){
//    $inscritos = Inscricao::obterPorEvento($id,"ev_pessoas.nome");
//    foreach($inscritos as $inscrito){
//        echo $inscrito->nome."<br>";
//        //break;
//    }
//}