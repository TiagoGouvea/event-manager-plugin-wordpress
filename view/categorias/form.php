 <?php

 /** @var $categoria Categoria */
 use TiagoGouvea\PLib;

 $id_evento = $categoria->id_evento?$categoria->id_evento : $evento->id;

 ?>
 <div class='wrap'>
        <div id='icon-edit' class='icon32'>
            <br/>
        </div>
        <h2>Categorias</h2>

        <form method="post">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="postbox-container">
                            <div class="postbox">
                                <h3 class="hndle"><span>Categoria de Evento</span></h3>
                                <input type="hidden" name="id" value="<?php echo $categoria->id; ?>">
                                <input type="hidden" name="id_evento" value="<?php echo ($id_evento); ?>">
                                <div class="inside">
                                    <?php echo input_texto_padrao('titulo',"Titulo:",50,$categoria->titulo); ?>
                                    <?php echo input_texto_padrao('condicao',"Exclusiva:",50,$categoria->condicao,"Apenas pessoas com esta condição poderão se inscrever. Exemplo: cidade=\"juiz de fora\""); ?>
                                    <?php

                                    $precos = Plib::object_to_array(Precos::getInstance()->getByEvento($id_evento));
                                    $precos = Plib::array_to_key_value($precos,'id','titulo');
                                    echo input_select_simples('id_preco','Preço:',$precos, $categoria->id_preco);
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