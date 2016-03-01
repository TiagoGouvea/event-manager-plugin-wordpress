 <?php

 /** @var $desconto Categoria */
 $id_evento = ($desconto->id_evento ? $desconto->id_evento : $evento->id);

 ?>
 <div class='wrap'>
        <div id='icon-edit' class='icon32'>
            <br/>
        </div>
        <h2>Descontos</h2>

        <form method="post">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="postbox-container">
                            <div class="postbox">
                                <h3 class="hndle"><span>Desconto do Evento</span></h3>
                                <input type="hidden" name="id" value="<?php echo $desconto->id; ?>">
                                <input type="hidden" name="id_evento" value="<?php echo ($id_evento); ?>">
                                <div class="inside">
                                    <div class="inside">
                                        <?php  echo input_texto_padrao('ticket',"Ticket:",30,$desconto->ticket); ?>
                                        <?php
                                        $tipos=array('percentual'=>'Percentual','valor'=>'Valor');
                                        echo label('desconto_por','Tipo de Desconto',input_select_simples('desconto_por','Tipo de Desconto',$tipos,$desconto->desconto_por));
                                        ?>
                                        <?php  echo input_texto_padrao('desconto',"Desconto:",10,$desconto->desconto); ?>
                                        <?php  echo input_texto_padrao('quantidade',"Quantidade:",10,$desconto->quantidade); ?>
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