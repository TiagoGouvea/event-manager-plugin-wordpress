<?php

function etapaTitulo(Evento $evento)
{
    if ($evento->fila_espera)
        echo "Vagas esgotadas";
    else
        echo "Confirmação";
}

function etapaEsquerdaTitulo()
{
    return "É assim que se faz!";
}

function etapaIntroducao(Evento $evento,Pessoa $pessoa,Inscricao $inscricao){
}

function etapaEsquerdaIntroducao(Evento $evento, Pessoa $pessoa = null, Inscricao $inscricao = null)
{
    if ($evento->fila_espera == 1) {
        $mensagem = "<p>Agora é esperar...</p>";
    } else if ($evento->confirmacao == 'imediata') {
        $mensagem = "<p>Tudo certo!</p>";
    } else if ($evento->confirmacao == 'posterior') {
        $mensagem = "<p>Agora é só esperar um poquinho...</p>";
    } else if ($evento->confirmacao == 'preinscricao') {
        $mensagem = "<p>Te avisaremos e você será o primeiro!</p>";
    } else if ($evento->confirmacao == 'pagamento') {
        if (!$inscricao->confirmado) {
            $mensagem = "<p>Estamos quase lá! Falta pouco...</p>";
        } else {
            $mensagem = "<p>Excelente! Deu tudo certo!</p>";
        }
    }

    echo $mensagem;
}

// Ver quais meios de pagamentos existem
// Para cada um, seu display
/**
 * @param $evento Evento
 */
function etapaConteudo(Evento $evento, Pessoa $pessoa = null, Inscricao $inscricao = null)
{
//    var_dump($evento);
//    var_dump($pessoa);
//    var_dump($inscricao);
    // Qual a situação da inscrição? Do evento?
    if ($evento->fila_espera == 1)
        $mensagem = "wizard_fim_fila_espera";
    else if ($evento->confirmacao == 'imediata')
        $mensagem = "wizard_fim_inscricao_confirmada";
    else if ($evento->confirmacao == 'posterior')
        $mensagem = "wizard_fim_confirmacao_posterior";
    else if ($evento->confirmacao == 'preinscricao')
        $mensagem = "wizard_fim_pre_inscricao";
    else if ($evento->confirmacao == 'pagamento') {
        if (!$inscricao->confirmado)
            $mensagem = "wizard_fim_pagamento_aguardando_confirmacao";
        else {
            $mensagem = "wizard_fim_pagamento_confirmado";
        }
    }

    echo Mensagens::getInstance()->get($mensagem, $evento, $pessoa, $inscricao, true);
}
