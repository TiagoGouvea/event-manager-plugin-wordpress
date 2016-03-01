<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Envio de SMS</h2>

    <form method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">
                            <h3 class="hndle"><span><?php echo $titulo; ?></span></h3>
                            <div class="inside">
                                <form method="post" action="">
                                <?php
                                echo label('mensagem','Mensagem', input_textarea_simples('mensagem',2,''),
                                    "Variáveis disponíveis para utilização:<br>".getVariaveis($event));
                                ?>
                            </div>

                            <div id="major-publishing-actions">
                                <div id="publishing-action">
                                    <span class="spinner"></span>
                                    <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Enviar" accesskey="p"></div>
                                <div class="clear"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>