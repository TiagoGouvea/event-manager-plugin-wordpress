<?php

function textAreaMensagem($name,$rows=4){
    $mensagem = Mensagens::getInstance()->getUnico($name);
//    $mensagem = str_replace("<br>","\r\n",$mensagem);
//    $mensagem = preg_replace('/ +/', ' ',$mensagem);
//    $mensagem = preg_replace('/\t+/', '',$mensagem);
    echo input_textarea_simples($name,$rows,$mensagem);
}

?>

<div class="postbox-container">
    <div class="postbox">
        <h3 class="hndle"><span>Mensagens customizadas</span></h3>
        <div class="inside">
            <h2>Wizar de Inscrição no Evento</h2>

            <input type="hidden" name="chave" value="<?php echo Mensagens::getInstance()->getChave(); ?>">

            <p><b>Etapa Final - Pré-inscrição</b></p>
            <?php
            textAreaMensagem('wizard_fim_pre_inscricao')
            ?>

            <p><b>Etapa Final - Inscrição confirmada</b></p>
            <?php
            textAreaMensagem('wizard_fim_inscricao_confirmada');
            ?>

            <p><b>Etapa Final - Inscrição será confirmada posteriormente</b></p>
            <?php
            textAreaMensagem('wizard_fim_confirmacao_posterior');
            ?>

            <p><b>Etapa Final - Inscrição será confirmada após confirmação do pagamento</b></p>
            <?php
            textAreaMensagem('wizard_fim_pagamento_aguardando_confirmacao');
            ?>

            <p><b>Etapa Final - Inscrição confirmada com pagamento</b></p>
            <?php
            textAreaMensagem('wizard_fim_pagamento_confirmado');
            ?>
        </div>

        <div class="inside">
            <h2>Emails</h2>

            <p><b>Confirmação de inscrição</b></p>
            <?php
            textAreaMensagem('email_confirmacao_inscricao',10);
            ?>

            <p><b>Pagamento Cancelado (pelo gateway)</b></p>
            <?php
            textAreaMensagem('email_cancelamento_inscricao_gateway',10);
            ?>
        </div>

    </div>
</div>