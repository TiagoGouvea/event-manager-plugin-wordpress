 <?php

 /** @var $preco Preco */
 $id_evento = ($preco->id_evento ? $preco->id_evento : $evento->id);

 ?>
 <div class='wrap'>
        <div id='icon-edit' class='icon32'>
            <br/>
        </div>
        <h2>Preços</h2>

        <form method="post">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="postbox-container">
                            <div class="postbox">
                                <h3 class="hndle"><span>Preços do Evento</span></h3>
                                <input type="hidden" name="id" value="<?php echo $preco->id; ?>">
                                <input type="hidden" name="id_evento" value="<?php echo ($id_evento); ?>">
                                <div class="inside">
                                    <?php echo input_texto_padrao('titulo',"Titulo:",50,$preco->titulo); ?>
                                    <?php echo input_texto_padrao('valor',"Valor:",10,$preco->valor,"Apenas pessoas com esta condição poderão se inscrever. Exemplo: cidade=\"juiz de fora\""); ?>
                                    <?php  echo input_texto_padrao('vagas',"Vagas:",10,$preco->vagas); ?>
                                    <?php echo input_checkbox_padrao('encerrado','Encerrado:',$preco->encerrado); ?>
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