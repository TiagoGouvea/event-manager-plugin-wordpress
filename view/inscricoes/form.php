<?php /* @var $inscricao Inscricao */
use TiagoGouvea\PLib; ?>
<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Inscrição</h2>

    <form method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">

                            <?php if ($inscricao): ?>

                              Editar ainda não implementado


                            <?php else: ?>

                                <h3 class="hndle"><span>Inscrever Pessoa</span></h3>
                                <input type="hidden" name="id_evento" value="<?php echo $evento->id; ?>">
                                <div class="inside">
                                    <?php


                                    $pessoas = Plib::object_to_array(Pessoas::getInstance()->getAll());
                                    $pessoas = Plib::array_to_key_value($pessoas,'id','nome');
                                    echo input_select_simples('id_pessoa','Pessoa:',$pessoas, $_POST['id_pessoa']);

//                                    echo label('nome', 'Nome', input_texto_simples('nome', 'Nome', 40, $_POST['nome']));
//                                    echo label('email', 'Email', input_texto_simples('email', 'Email', 40, $_POST['email']));
//                                    echo label('cpf', 'CPF', input_texto_simples('cpf', 'CPF', 40, $_POST['nome']));
//                                    echo label('celular', 'Celular', input_texto_simples('celular', 'Celular', 40, $_POST['celular']));
//                                    echo label('minibio', 'Mini Bio', input_textarea_simples('minibio', 4, $_POST['minibio']));
//                                    echo label('observacoes', 'Observações', input_textarea_simples('observacoes', 5, $_POST['observacoes']));
                                    ?>
                                </div>

                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="publish" id="publish"
                                               class="button button-primary button-large" value="Inscrever" accesskey="p">
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