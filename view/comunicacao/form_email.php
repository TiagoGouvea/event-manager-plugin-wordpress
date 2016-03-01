<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Envio de Email</h2>

    <form method="post" action="" id="form_email">

    <?php
    use TiagoGouvea\PLib;

    if (count($_POST) > 0 && $_POST['preview']==1){
        require_once 'preview.php';
    }
    ?>


        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">
                            <h3 class="hndle"><span><?php echo $titulo; ?></span></h3>
                            <input type="hidden" name="id" value="<?php echo $evento->id; ?>">
                            <div class="inside">
                                <?php
                                $assunto=stripslashes(PLib::coalesce($_POST['assunto'],$evento->post_title));
                                if ($subAction=='confirmados'){
                                    $mensagem = PLib::coalesce($_POST['mensagem'],$evento->organizador()->email_confirmacao);
                                } else if ($subAction=='naoConfirmados'){
                                    $mensagem = PLib::coalesce($_POST['mensagem'],$evento->organizador()->email_semvagas);
                                }

                                $mensagem = stripslashes(str_replace("<br />","",$mensagem));

                                echo label('assunto','Assunto', input_texto_simples('assunto','Assunto',80, $assunto));
                                echo label('mensagem','Mensagem', input_textarea_simples('mensagem',20,$mensagem),
                                    "Variáveis disponíveis para utilização:<br>".getVariaveis($event).'<br><br>Ao final do email irá uma linha simples de assinatura contendo: '.$evento->organizador()->titulo);
                                ?>
                            </div>

                            <div id="major-publishing-actions">
                                <div id="publishing-action">
                                    <input type="hidden" name="preview" id="preview" value="">
                                    <input type="button" class="button button-primary button-large" value="Pré-visualizar" onclick="javascript:document.getElementById('preview').value='1';document.getElementById('form_email').submit();">
                                    <input type="submit" class="button button-primary button-large" value="Enviar"  onclick="javascript:document.getElementById('preview').value='';document.getElementById('form_email').submit();"></div>
                                <div class="clear"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>