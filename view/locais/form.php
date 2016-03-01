<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Local</h2>

    <form method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">
                            <h3 class="hndle"><span>Local para eventos</span></h3>
                            <input type="hidden" name="id" value="<?php echo $local->id; ?>">
                            <div class="inside">
                            <?php
                            echo input_text($local->titulo, 'titulo', 'Título do local:',40);
                            echo label('endereco','Endereço Completo',input_textarea_simples('endereco', 3, $local->endereco),'Inclua a cidade no endereço');
                            echo input_text($local->cidade, 'cidade', 'Cidade:',20);
                            echo input_text($local->telefone, 'telefone', 'Telefone:',16);
                            echo input_text($local->site, 'site', 'Site:',60);
                            echo input_text($local->latitude, 'latitude', 'Latitude:',20);
                            echo input_text($local->longitude, 'longitude', 'Longitude:',20);
                            ?>
                            </div>

                            <div id="major-publishing-actions">
                                <div id="publishing-action">
                                    <span class="spinner"></span>
                                    <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Salvar" accesskey="p"></div>
                                <div class="clear"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>