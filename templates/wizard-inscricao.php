<?php
use lib\WizardInscricao;
use TiagoGouvea\PLib;


get_header(); ?>

<?php
/* @var $pessoa Pessoa */
/* @var $inscricao Inscricao */
/* @var $evento Evento */
/* @var $wizard WizardInscricao */

$wizard = WizardInscricao::getInstance();
$evento = $wizard->getEvento();


//PLib::var_dump($etapa,"Etapa");
//PLib::var_dump($evento,"Evento");
//PLib::var_dump($pessoa,"Pessoa");
//PLib::var_dump($inscricao,"Inscricao");

// Carregar etapa
templateInclude("wizard-inscricao-etapa_" . $wizard->etapa . ".php");

//PLib::var_dump($wizard);

// Carregar layout
templateInclude("wizard-inscricao-layout.php",
    array(
        'urlForm'=>$urlForm,
        'wizard'=>$wizard,
        'evento'=>$wizard->evento,
        'pessoa'=>$wizard->pessoa,
        'inscricao'=>$wizard->inscricao
    )
);

function wizardTitulo(){
    global $wizard;
    etapaTitulo($wizard->evento,$wizard->pessoa,$wizard->inscricao);
}
function wizardIntroducao(){
    global $wizard;
    etapaIntroducao($wizard->evento,$wizard->pessoa,$wizard->inscricao);
}
function wizardConteudo(){
    global $wizard;
    etapaConteudo($wizard->evento,$wizard->pessoa,$wizard->inscricao);
}
?>


