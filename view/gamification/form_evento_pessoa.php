 <?php

 /** @var $preco Preco */
 use lib\Gamification;

 $id_evento = ($preco->id_evento ? $preco->id_evento : $evento->id);

 ?>
 <div class='wrap'>
        <div id='icon-edit' class='icon32'>
            <br/>
        </div>
        <h2>Adicionar Evento a Pessoa</h2>

        <form method="post">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="postbox-container">
                            <div class="postbox">
                                <h3 class="hndle"><span>Evento</span></h3>
                                <div class="inside">

                                    <?php

                                    // Evento
                                    $eventos = Gamification::getInstance()->getEventosArray();
                                    echo label('alias','Evento',input_select_simples('alias','Evento', $eventos));

                                    // Pessoas
                                    $pessoas = Pessoas::getInstance()->getTodosArray();
                                    echo label('id_pessoa','Pessoa',input_select_simples('id_pessoa','Pessoa', $pessoas, $_GET['id_pessoa']));
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