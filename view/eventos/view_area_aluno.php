<div class="wrap">
    <?php
    use TiagoGouvea\PLib;
    menuEvento($evento,'evento-view');
    ?>

    <div id="poststuff">
        <div id="postbox-container-2" class="postbox-container">
            <div id="postexcerpt" class="postbox " style="margin-bottom: 0px;">
                <h3 class="hndle"><span>Área do Aluno</span></h3>
                <div class="inside">
                    <?php echo $evento->area_aluno; ?>
                </div>
            </div>

            <div id="major-publishing-actions">
                <div id="publishing-action">
                    <span class="spinner"></span>
                    <input type="submit" name="publish" id="publish"
                           class="button button-primary button-large" value="Editar" accesskey="p">
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>