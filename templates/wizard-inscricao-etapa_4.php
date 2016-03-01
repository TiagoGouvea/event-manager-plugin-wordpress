<?php


use TiagoGouvea\PLib;

function etapaTitulo()
{
    echo "Pagamento";
}

function etapaIntroducao(Evento $evento, Pessoa $pessoa, Inscricao $inscricao)
{
    echo "Sua inscrição foi realizada com sucesso!<br><br>
          O valor da sua inscrição para \"$evento->titulo\" é <b>" . PLib::format_cash($inscricao->valor_inscricao) . "</b>.<br>";
    ?>

    <?php if ($evento->descontoSessao()):
        ?><p>Você recebeu um total de <b><?php echo ((($evento->valor()-$evento->getDescontoSessao())/$evento->valor())*100) ?>% de desconto</b>, com isso ser investimento é de <b><?php echo PLib::format_cash($evento->getValorAtual()); ?></b></p>
    <?php endif; ?>

    <?php if ($evento->getValorAtual()==0): ?>
        <p>Portanto, sua inscrição <b>já está confirmada</b>!</p>
    <?php else: ?>
        <p>Escolha a forma de pagamento que melhor lhe atende.</p>
    <?php endif; ?>

    <?php
}

/**
 * @param $evento Evento
 */
function etapaConteudo(Evento $evento, Pessoa $pessoa, Inscricao $inscricao)
{
    // Obter meios de pagamento
    $meios = getMeiosPagamento($evento, $pessoa, $inscricao);
    ?>

    <?php //if ($textoTicket!=null) echo "<h4>$textoTicket</h4><br>";
    ?>

    <?php if ($evento->descontoSessao()): ?>
    <p>Aplicado desconto de <?php echo $evento->getDescontoSessao(); ?>%. Valor de seu investimento:
        <b><?php echo PLib::format_cash($evento->getValorAtual()); ?></b></p>
<?php endif; ?>

    <?php
    foreach ($meios as $meioIndice => $meio) {
        ?>
        <div class="meio_pagamento">
            <div class="titulo">
                <h2><?php echo $meio['titulo']; ?></h2>
                <?php if ($meio['desconto'] == null) { ?>
                    <h3>Valor: <?php echo PLib::format_cash($evento->getValorAtual()); ?></h3>
                <?php } else {
                    // Calcular desconto
                    $valor = $evento->getValorAtual();
                    $valor = $evento->getValorAtual() - ($evento->getValorAtual() * ($meio['desconto'] / 100));
                    $valor = round($valor);
                    ?><h3>Valor com desconto: <?php echo PLib::format_cash($valor); ?></h3><?
                }
                ?>
            </div>
            <div>
                <div class="instrucoes">
                    <ul>
                        <?php foreach ($meio['instrucoes'] as $instrucao): ?>
                            <li><?php echo $instrucao; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php if ($meio['url']): ?>
                    <a href="<?php echo $meio['url']; ?>" class="button"
                       style="margin-right: 30px;"><?php echo $meio['caption']; ?></a><br>
                <?php elseif ($meio['overlay']): ?>
                    <a href="#" onclick="alert('<?php echo $meio['overlay']; ?>'); return false;" class="button"
                       style="margin-right: 30px;"><?php echo $meio['caption']; ?></a><br>
                <?php elseif ($meio['confirm']): ?>
                    <a href="#" onclick="confirmar_<?php echo $meioIndice; ?>('<?php echo $meio['confirm']; ?>'); return false;" class="button"
                       style="margin-right: 30px;"><?php echo $meio['caption']; ?></a><br>
                    <script>
                        function confirmar_<?php echo $meioIndice; ?>(mensagem){
                            var ok=confirm(mensagem);
                            if (ok==true){
                                // Enviar requisição ajax dizendo que a pessoa escolheu isso
                                var url = "<?php echo get_permalink().'/?set_meio_pagamento='.$meioIndice; ?>";
//                                console.log(url);
                                getAjax(url,function(data){
                                    console.log(data);
                                });
                            }
                        }
                    </script>
                <?php endif; ?>

            </div>
        </div>
        <br>&nbsp;<br>
    <?php }; ?>

    <?php
}

function getMeiosPagamento(Evento $evento, $pessoa, $inscricao)
{
    $meios = array();
    if ($evento->pago_pagseguro) {
        if ($meios['phormar'])
            $meios["pagseguro"]['titulo'] = "Cartão de Crédito";
        else
            $meios["pagseguro"]['titulo'] = "PagSeguro";
        $meios["pagseguro"]['instrucoes'] = array(
            "Você será direcionado ao site do PagSeguro para realização da transação;",
            "A inscrição é confirmada no momento em que o PagSeguro recebe o \"ok\" de sua operadora e avisa ao nosso sistema."
        );
        $meios["pagseguro"]["caption"] = "Pagar agora";
        $meios["pagseguro"]["url"] = get_permalink() . "?servico=pagseguro&ticket=" . $inscricao->id;
    }
    if ($evento->pago_cielo) {
        $meios["cielo"]['titulo'] = "Cartão de Crédito / Débito";
        $meios["cielo"]['instrucoes'] = array(
            "Você será direcionado ao site da Cielo para realização da transação;",
            "A inscrição é confirmada no momento em que o PagSeguro recebe o \"ok\" de sua operadora e avisa ao nosso sistema."
        );
        $meios["cielo"]["caption"] = "Pagar agora";
        $meios["cielo"]["url"] = get_permalink() . "?servico=cielo&ticket=" . $inscricao->id;

        $meios["cielo_boleto"]['titulo'] = "Boleto (a vista)";
        $meios["cielo_boleto"]['instrucoes'] = array(
            "Você será direcionado ao site da Cielo para realização da transação;",
            "A inscrição é confirmada no momento em que o PagSeguro recebe o \"ok\" de sua operadora e avisa ao nosso sistema."
        );
        $meios["cielo_boleto"]["caption"] = "Pagar agora";
        $meios["cielo_boleto"]["desconto"] = 5;
//        $meios["cielo_boleto"]["overlay"] = 'Sua inscrição está registrada, entraremos em contato por telefone ou email lhe encaminhando o boleto para pagamento.';
        $meios["cielo_boleto"]["confirm"] = 'Sua inscrição já está registrada. Optando por esta forma de pagamento, lhe encaminharemos em breve o boleto por email para pagamento. \r\n Confirma pagamento em boleto?';
        //$meios["cielo_boleto"]["url"] = get_permalink() . "?servico=cielo&ticket=" . $inscricao->id;
    }
    if ($evento->pago_dinheiro) {
        $meios["dinheiro"]['titulo'] = "Dinheiro (a vista)";
//        $meios["dinheiro"]["desconto"]=5;
        $meios["dinheiro"]['instrucoes'] = array(
            "Vá até um de nossos parceiros para realizar o pagamento;",
            "Assim que o parceiro notificar seu pagamento confirmaremos sua inscrição."
        );
        $meios["dinheiro"]["caption"] = "Onde pagar";
//        $meios["dinheiro"]["overlay"] = 'Local para pagamento em dinheiro:\r\n\r\n' . $evento->localPagamento()->titulo . '\r\n' . str_replace("\r\n", '\r\n', $evento->localPagamento()->endereco) . '\r\n' . $evento->localPagamento()->telefone . '\r\nSua inscrição está registrada, dirija-se ao local de pagamento para confirmar sua participação.';
        $meios["dinheiro"]["confirm"] = 'Sua inscrição já está registrada. Optando por esta forma de pagamento, sua inscrição só será confirmada após o pagamento no local abaixo. \r\n\r\n' . $evento->localPagamento()->titulo . '\r\n' . str_replace("\r\n", '\r\n', $evento->localPagamento()->endereco) . '\r\n' . $evento->localPagamento()->telefone . '\r\n Confirma pagamento em boleto?';
    }
    if ($evento->pago_deposito) {
        $meios["deposito"]['titulo'] = "Depósito (a vista)";
        $meios["deposito"]['instrucoes'] = array(
            "Se preferir, transfira o valor de sua inscrição diretamente para nossa conta;",
            "Após a transferência, nos envie o comprovante para que possamos identificar seu pagamento e confirmar a inscrição o quanto antes."
        );
        $meios["deposito"]["caption"] = "Dados bancários";
        $meios["deposito"]["confirm"] = 'Sua inscrição já está registrada. Optando por esta forma de pagamento, sua inscrição só será confirmada após a confirmação do depósito na conta abaixo. \r\n\r\n' . str_replace("\r\n", '\r\n', $evento->organizador()->inscricao_dados_conta) . '\r\n\r\n Confirma opção de pagamento por depósito?';
    }

    return $meios;
}

global $erro;
