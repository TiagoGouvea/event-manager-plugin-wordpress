<?php /* @var $pessoa Pessoa */ ?>
<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2><?php echo $evento->titulo; ?></h2>

    <form method="post" enctype="multipart/form-data">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">

                                <h3 class="hndle"><span>Importar Inscritos</span></h3>
                                <input type="hidden" name="id" value="<?php echo $pessoa->id; ?>">
                                <div class="inside">
                                    <?php
                                    // Arquivo
                                    echo input_file("arquivo","Arquivo CSV","O formato dos dados devem ser: nome;email;cpf;celular;");
                                    // Importar como confirmados
                                    echo input_checkbox_padrao("confirmar","Importar Confirmados",1,"Diz se os inscritos importados agora serão incluídos como Confirmados no evento");
                                    // Importar como presentes
                                    echo input_checkbox_padrao("presente","Importar Presentes",null,"Diz se os inscritos importados agora serão incluídos como Confirmados e Presentes no evento");
                                    ?>
                                </div>

                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="publish" id="publish"
                                               class="button button-primary button-large" value="Importar" accesskey="p">
                                    </div>
                                    <div class="clear"></div>
                                </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>