<?php /* @var $pessoa Pessoa */ ?>
<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Pessoa</h2>

    <form method="post" enctype="multipart/form-data">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">

                            <?php if ($pessoa): ?>

                                <h3 class="hndle"><span><?php echo $pessoa->nome; ?></span></h3>
                                <input type="hidden" name="id" value="<?php echo $pessoa->id; ?>">
                                <div class="inside">
                                    <?php

                                    echo label('nome', 'Nome', input_texto_simples('nome', 'Nome', 40, $pessoa->nome));

                                    echo '<img src="'.$pessoa->getPictureUrl().'" style="width: 80px;"/>';
                                    echo input_file("arquivo","Imagem","");

                                    echo label('minibio', 'Mini Bio', input_textarea_simples('minibio', 5, $pessoa->getExtra('minibio')));

                                    echo label('observacoes', 'Observações', input_textarea_simples('observacoes', 3, $pessoa->getExtra('observacoes')));

                                    foreach(Pessoas::$networksGreat as $networkKey=>$networkTitle){
                                        echo label($networkKey, $networkTitle, input_texto_simples($networkKey, $networkTitle, 40, $pessoa->getExtra($networkKey)));
                                    }

                                    $users = Instrutores::getInstance()->getTodosArray();
                                    echo label('id_user', 'Usuário Admin', input_select_simples('id_user', 'Usuário Admin', $users, $pessoa->id_user));
                                    ?>
                                </div>

                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="publish" id="publish"
                                               class="button button-primary button-large" value="Salvar" accesskey="p">
                                    </div>
                                    <div class="clear"></div>
                                </div>


                            <?php else: ?>

                                <h3 class="hndle"><span>Inserir Pessoa</span></h3>
                                <div class="inside">
                                    <?php
                                    echo label('nome', 'Nome', input_texto_simples('nome', 'Nome', 40, $_POST['nome']));
                                    echo label('email', 'Email', input_texto_simples('email', 'Email', 40, $_POST['email']));
                                    echo label('cpf', 'CPF', input_texto_simples('cpf', 'CPF', 40, $_POST['nome']));
                                    echo label('celular', 'Celular', input_texto_simples('celular', 'Celular', 40, $_POST['celular']));
                                    echo label('minibio', 'Mini Bio', input_textarea_simples('minibio', 4, $_POST['minibio']));
                                    echo label('observacoes', 'Observações', input_textarea_simples('observacoes', 5, $_POST['observacoes']));
                                    ?>
                                </div>

                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="publish" id="publish"
                                               class="button button-primary button-large" value="Salvar" accesskey="p">
                                    </div>
                                    <div class="clear"></div>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>