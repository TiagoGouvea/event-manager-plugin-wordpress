<?php
/**
 * Template para exibição do wizard de inscrição para um evento
 */
/* @var $wizard WizardInscricao */
use lib\WizardInscricao;

$wizard = WizardInscricao::getInstance();
$evento = $wizard->getEvento();
$inscricao = $wizard->getInscricao();
$pessoa = $wizard->getPessoa();

?>
    <div class="wrapper content tgo_evento_inscricao">
        <div class="tgo_evento_inscricao_header">
            <h1>
                <a href="<?php echo get_permalink($evento->id) ?>">
                    <?php echo $evento->titulo; ?>
                </a>
            </h1>

            <?php if (hasFlash()): ?>
                <div class="message" style="margin: 15px 0 15px 0;">
                    <span style="color: #317b3b; font-size: 16px;"><?php echo getFlashString(); ?></span>
                </div>
            <?php endif; ?>

            <h2><?php etapaTitulo($evento); ?></h2>
            <?php etapaIntroducao($evento, $pessoa, $inscricao); ?>
        </div>
        <?php if ($wizard->erro): ?>
            <div class="error" style="margin: 15px 0 15px 0;">
                <br>
                <span style="color: #b40000; font-size: 16px;"><b>Erro: <?php echo $wizard->erro; ?></b></span>
                <br>
            </div>
        <?php endif; ?>


        <div class="tgo_evento_inscricao_form">
            <form method="post" class="formatted-form" action="<?php echo $wizard->getUrlForm(); ?>"
                  enctype="multipart/form-data">
                <input type="hidden" name="etapa" value="<?php echo $wizard->etapa; ?>">

                <?php
                etapaConteudo($evento, $pessoa, $inscricao); ?>

                <?php if (!$wizard->etapaFinal): ?>
                    <br><input type="submit" value="<?php echo $wizard->avancarTexto; ?>">
                <?php endif; ?>
            </form>
        </div>
        <br><br>
        <div class="clear"></div>
    </div> <!-- end .wrapper -->

<?php get_footer(); ?>