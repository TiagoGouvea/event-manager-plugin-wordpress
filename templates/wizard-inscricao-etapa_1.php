<?php

function etapaTitulo(){
    return "Identificação";
}

function etapaIntroducao(Evento $evento){
    if (!$evento->inscricaoAberta() || $evento->acontecendo())
        echo "<p>As inscrições para este evento já se encerraram.</P>";
}


function etapaConteudo($evento,$pessoa,$inscricao){
    $validacao = $evento->validacao_pessoa;
    if ($validacao=='cpf') $validacao='CPF';
    if ($validacao=='email') $validacao='endereço de email';
    ?>
    <?php if ($evento->inscricaoAberta() || $evento->acontecendo): ?>
        <div class="field-wrapper">

        <?php if ($evento->isPago()): ?>
            <p>Se você tem um <b>ticket de desconto</b>,informe-o</p>
            <div class="field-wrapper">
                <?php echo input_texto_simples('ticket', 'Ticket de desconto', 20); ?>
            </div><br>
        <?php endif; ?>

        <p>Para iniciar informe seu <?php echo $validacao; ?> no campo abaixo</p>
            <?php if ($evento->validacao_pessoa=='cpf'): ?>
                <?php echo input_texto_simples('cpf', 'CPF', 20); ?>
            <?php else: ?>
                <?php echo input_texto_simples('email', 'Email', 30); ?>
            <?php endif; ?>
        </div>
    <?php endif ?>
    <?php
}
