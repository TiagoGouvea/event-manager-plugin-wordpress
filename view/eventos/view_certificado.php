<div class="wrap">
    <?php
    use TiagoGouvea\PLib;

    menuEvento($evento, 'evento-view');
    ?>

    <form method="post" enctype="multipart/form-data">
        <div id="poststuff">
            <div id="postbox-container-2" class="postbox-container">
                <div id="postexcerpt" class="postbox " style="margin-bottom: 0px;">
                    <h3 class="hndle"><span>Simulação</span></h3>

                    <?php
                    $inscricoes = Inscricoes::getInstance()->getByEvento($evento->id);
                    /* @var $inscricao Inscricao */
                    $inscricao = $inscricoes[0];
                    ?>
                    <div class="inside">
                        <img src=<?php echo $inscricao->getLinkCertificado(); ?> width=800>
                        <hr>
                        <?php echo label("altura_nome","Altura do Nome",input_texto_simples("altura_nome", "Altura do Nome", 5, $evento->certificado_altura_nome)); ?>
                    </div>
                </div>

                <div id="major-publishing-actions">
                    <div id="publishing-action">
                        <span class="spinner"></span>
                        <input type="submit" name="publish" id="publish"
                               class="button button-primary button-large" value="Atualizar" accesskey="p">
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </form>
</div>