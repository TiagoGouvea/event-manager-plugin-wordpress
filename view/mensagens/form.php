 <?php

 /** @var $categoria Categoria */
 $id_evento = $categoria->id_evento?$categoria->id_evento : $evento->id;

 ?>
 <div class='wrap'>
        <div id='icon-edit' class='icon32'>
            <br/>
        </div>
        <h2>Mensagens</h2>

        <form method="post">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">

                        <?php
                        Mensagens::getInstance()->setConfig();
                        require_once 'partial_mensagens.php'; ?>

                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <span class="spinner"></span>
                                <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Salvar" accesskey="p"></div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
 </div>