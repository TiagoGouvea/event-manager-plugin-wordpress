
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">
                            <h3 class="hndle"><span>Prévisualização de email</span></h3>
                            <div class="inside">
                                <?php
                                $inscricoes = Inscricoes::getInstance()->getByEvento($evento->id);
                                $inscricao=$inscricoes[0];

                                $assunto = Mensagens::getInstance()->substituirVariaveis($_POST['assunto'],$evento,$inscricao->pessoa(),$inscricao);
                                $assunto = stripslashes($assunto);
                                ?>

                                Inscrição obtida para teste: <?php echo $inscricao->id." - ".$inscricao->pessoa()->nome;?><br><br>
                                <h3><?php echo $assunto; ?></h3>

                                <span style="background-color: white; border: 1px solid grey; width: 97%; display:block; padding: 10px;">
                                <?php
                                $mensagem = nl2br($_POST['mensagem']);
                                $mensagemEnviar = Mensagens::getInstance()->substituirVariaveis($mensagem,$evento,$inscricao->pessoa(),$inscricao);
                                $mensagemEnviar = stripslashes($mensagemEnviar);
                                $mensagemEnviar= $mensagemEnviar."<br><br>".$inscricao->evento()->organizador()->titulo;
                                echo  $mensagemEnviar;
                                ?>
                                </span>
                                <br>
                            </div>

                            <div id="major-publishing-actions">
                                <div id="publishing-action">
                                    <input type="submit" class="button button-primary button-large" value="Enviar Agora" onclick="javascript:document.getElementById('preview').value='';document.getElementById('form_email').submit();"></div>
                                <div class="clear"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
