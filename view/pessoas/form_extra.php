<?php /* @var $pessoa Pessoa */ ?>
<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Adicionar Extra</h2>

    <form method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">
                            <h3 class="hndle"><span><?php echo $pessoa->nome; ?></span></h3>
                            <input type="hidden" name="id" value="<?php echo $pessoa->id; ?>">
                            <div class="inside">
                                <?php echo input_texto_padrao('chave',"Chave:",20,$_POST['chave']); ?>
                                <?php echo input_texto_padrao('titulo',"Título:",20,$_POST['titulo']); ?>
                                <?php echo label('valor','Valor do Extra:',input_textarea_simples('valor',5,$_POST['valor'])); ?>
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