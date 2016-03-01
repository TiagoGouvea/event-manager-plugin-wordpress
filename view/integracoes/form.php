 <?php /** @var $integracao integracao */  ?>
 <div class='wrap'>
        <div id='icon-edit' class='icon32'>
            <br/>
        </div>
        <h2>Integração</h2>

        <form method="post">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="postbox-container">
                            <div class="postbox">
                                <h3 class="hndle"><span>Integração de Sistemas</span></h3>
                                <input type="hidden" name="id" value="<?php echo $integracao->id; ?>">
                                <div class="inside">
                                    <?php echo input_texto_padrao('titulo',"Titulo:",30,$integracao->titulo); ?>

                                    <?php
                                    $tipos=array(
                                        'PagSeguro'=>'PagSeguro - Gateway de Pagamento',
                                        'Cielo'=>'Cielo Ecommerce - Integração para Pagamento',
                                        'PhormarPessoa'=>'Phormar - Consulta de pessoa',
                                        'RdStation'=>'RD Station - Nutrição de Leads',
                                        'AgileCRM'=>'AgileCRM - CRM Online'
                                    );
                                    echo label('servico','Serviço de Integração',input_select_simples('servico','Serviço de Integração',$tipos,$integracao->servico,'class=ajustes_visuais'));
                                    ?>

                                    <?php echo input_texto_padrao(
                                        'url',
                                        "URL:",
                                        90,
                                        $integracao->url,
                                        "Consulte o desenvolvedor para a inclusão correta dos dados.
                                        Paga \"Phormar - Consulta de pessoa\" utilize algo como http://online.phormar.com.br/server/getPessoa.php?cpf={identificacao}
                                        Utilizar {identificacao} no trecho a ser substituido.
                                        "); ?>

                                    <?php echo input_texto_padrao(
                                        'client',
                                        "Login/Email/ClientId/MerchantId:",
                                        90,
                                        $integracao->client,
                                        "Identificação para integração no destino. Pode ser um login, email ou client id, dependendo do serviço."); ?>

                                    <?php echo input_texto_padrao(
                                        'token',
                                        "Token de acesso:",
                                        90,
                                        $integracao->token); ?>
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

            <script>
                function ajustesVisuais(){
                    console.log(this);
                    console.log();
                    var servico = jQuery("#servico").val();
                    if (servico == "PhormarPessoa") {
                        jQuery("#label_url").css("display","block");
                    } else {
                        jQuery("#label_url").css("display","none");
                    }

                    if (servico == "RdStation") {
                        jQuery("#label_client").css("display","none");
                    } else {
                        jQuery("#label_client").css("display","block");
                    }
                };

                jQuery(function () {
                    jQuery(".ajustes_visuais").on("change", function () {
                        ajustesVisuais();
                    });
                    ajustesVisuais();
                });
            </script>

        </form>
 </div>