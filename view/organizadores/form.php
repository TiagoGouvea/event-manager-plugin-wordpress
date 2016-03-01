<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Organizadores</h2>

    <form method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">
                            <h3 class="hndle"><span>Organizador de Eventos</span></h3>
                            <input type="hidden" name="id" value="<?php echo $organizador->id; ?>">

                            <div class="inside">
                                <?php

                                //var_dump($organizador);
                                // Titulo
                                echo input_text($organizador->titulo, 'titulo', 'Título:', 50);
                                echo input_text($organizador->titulo_menor, 'titulo_menor', 'Título menor:', 10, 'Para utilização em SMS');
                                echo input_text($organizador->slug, 'slug', 'Slug (url):', 20);
                                echo input_text($organizador->email, 'email', 'Email:', 50);
                                echo input_text($organizador->telefone, 'telefone', 'Telefone:', 50);
                                echo input_text($organizador->site, 'site', 'Site:', 50);


                                echo label('descricao', 'Descrição do Organizador:',
                                    input_textarea_simples('descricao', 5, $organizador->descricao)
                                );

                                echo label('publico_alvo', 'Público alvo:',
                                    input_textarea_simples('publico_alvo', 5, $organizador->publico_alvo)
                                );

                                echo label('periodicidade', 'Periodicidade de eventos:',
                                    input_textarea_simples('periodicidade', 5, $organizador->periodicidade)
                                );

                                $variaveis = getVariaveis();

                                echo "Variáveis disponíveis: " . $variaveis . '<Br><br>';


                                // Dados para depósito em conta
                                echo label('inscricao_dados_conta', 'Dados para depósito em conta:',
                                    input_textarea_simples('inscricao_dados_conta', 10, $organizador->inscricao_dados_conta),
                                    "Deixar em branco para não existir"
                                );

                                // Locais para pagamento em dinheiro
                                echo label('inscricao_locais_pagamento', 'Locais para pagamento em dinheiro:',
                                    input_textarea_simples('inscricao_locais_pagamento', 10, $organizador->inscricao_locais_pagamento),
                                    "Deixar em branco para não existir"
                                );

                                echo input_checkbox_padrao('ativo',"Ativo",$organizador->ativo);

                                ?>
                            </div>



                        </div>
                    </div>

                    <?php
                    // Incluir formulário parcial de mensagens
                    if ($organizador->id!=null){
                        Mensagens::getInstance()->setOrganizador($organizador->id);
                        require_once PLUGINPATH . '/view/mensagens/partial_mensagens.php';
                    }
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
    </form>
</div>