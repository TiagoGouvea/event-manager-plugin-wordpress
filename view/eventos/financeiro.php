<?php
use lib\PagSeguroUtil;
use TiagoGouvea\PLib;

?>
<div class="wrap">
    <?php
    menuEvento($evento, 'evento-inscricoes', null, 'Financeiro');
    ?>
    <div id="poststuff">
        <div id="postbox-container-2" class="postbox-container">
            <div id="postexcerpt" class="postbox ">
                <h3 class="hndle"><span>Resumo Financeiro do Evento</span></h3>

                <div class="inside">
                    <table width="100%">
                        <tr>
                            <th>Inscritos</th>
                            <th>Preços/Lotes</th>

                        </tr>
                        <tr>
                            <td>
                                <?php
                                $inscritos = $evento->qtdInscritos;
                                $confirmados = $evento->qtdConfirmados;
                                ?>

                                <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>">Inscritos</a>: <b><?php echo $inscritos; ?></b><br>
                                <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>&filter=confirmados">Confirmados</a>: <b><?php echo $confirmados; ?></b><br>

                                <?php if ($naoConfirmados): ?>
                                    <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>&filter=naoConfirmados">Pendentes</a>: <b><?php echo $naoConfirmados; ?></b><br>
                                <?php endif; ?>
                                Valor Inscrição Média:<br>
                                Pespectiva de vendas:
                            </td>
                            <td>
                                <?php
                                $precos = Precos::getInstance()->getByEvento($evento->id);
                                if ($precos) {
                                    /* @var $preco Preco */
                                    foreach ($precos as $preco) {
                                        if ($preco->encerrado == 1) {
                                            echo $preco->titulo . ' - confirmados: ' . $preco->getQtdConfirmados() . '<Br>';
                                        } else {
                                            echo '<b>Atual:</b><br>' . $preco->titulo . ' - vagas: ' . $preco->vagas . ' - restantes: ' . $preco->getVagasRestantes() . ' - ' . PLib::format_cash($preco->valor) . '<Br>';
                                            if (count($precos) > 1)
                                                echo "<br><b>Encerrados:</b><br>";
                                        }
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>


        <div id="postbox-container-2" class="postbox-container">
            <div id="postexcerpt" class="postbox ">
                <h3 class="hndle"><span>Recebimentos</span></h3>

                <div class="inside">
                    <table width="100%">
                        <tr>
                            <td><h3>Por Preço:</h3><br>
                                <table width="100%">
                                    <tr>
                                        <th>Lote</th>
                                        <th>Confirmados</th>
                                        <th>Valor Recebido</th>
                                        <th>Valor Líquido</th>
                                    </tr>
                                    <?php
                                    // Obter inscrições pagas, por preço
                                    global $wpdb;
                                    $sql = "select ep.titulo, count(ev_inscricoes.id) as qtd, coalesce(sum(ev_inscricoes.valor_pago),0) as valor_pago, coalesce(sum(coalesce(ev_inscricoes.valor_liquido,ev_inscricoes.valor_pago)),0) as valor_liquido
                                    from ev_inscricoes
                                    left join ev_precos_eventos ep on ep.id=ev_inscricoes.id_preco
                                    where ev_inscricoes.id_evento=$evento->id and ev_inscricoes.confirmado=1
                                    group by 1
                                    order by 2 desc";
                                    $data = $wpdb->get_results($sql);
                                    if (count($data) > 0) {
                                        foreach ($data as $dado) {
                                            echo "<tr><td>" . $dado->titulo . '</td><td>' . $dado->qtd . '</td><td>' . PLib::format_cash($dado->valor_pago) . '</td><td>' . PLib::format_cash($dado->valor_liquido) . '</td></tr>';
                                        }
                                    }
                                    ?>
                                </table>

                                <br><br>

                                <h3>Por Forma de Pagamento</h3><br>
                                <table width="100%">
                                    <tr>
                                        <th>Forma Pagamento</th>
                                        <th>Confirmados</th>
                                        <th>Valor Recebido</th>
                                        <th>Valor Líquido</th>
                                    </tr>
                                    <?php
                                    // Obter inscrições pagas, por preço
                                    global $wpdb;
                                    $sql = "select forma_pagamento, count(ev_inscricoes.id) as qtd, coalesce(sum(ev_inscricoes.valor_pago),0) as valor_pago, coalesce(sum(coalesce(ev_inscricoes.valor_liquido,ev_inscricoes.valor_pago)),0) as valor_liquido
                                    from ev_inscricoes
                                    left join ev_precos_eventos ep on ep.id=ev_inscricoes.id_preco
                                    where ev_inscricoes.id_evento=$evento->id and ev_inscricoes.confirmado=1
                                    group by 1
                                    order by 2 desc";
                                    $data = $wpdb->get_results($sql);
                                    if (count($data) > 0) {
                                        foreach ($data as $dado) {
                                            echo "<tr><td>" . $dado->forma_pagamento . '</td><td>' . $dado->qtd . '</td><td>' . PLib::format_cash($dado->valor_pago) . '</td><td>' . PLib::format_cash($dado->valor_liquido) . '</td></tr>';
                                        }
                                    }

                                    // Obter total
                                    global $wpdb;
                                    $sql = "select count(ev_inscricoes.id) as qtd, coalesce(sum(ev_inscricoes.valor_pago),0) as valor_pago, coalesce(sum(coalesce(ev_inscricoes.valor_liquido,ev_inscricoes.valor_pago)),0) as valor_liquido
                                    from ev_inscricoes
                                    where ev_inscricoes.id_evento=$evento->id and ev_inscricoes.confirmado=1";
                                    $data = $wpdb->get_results($sql);
                                    echo "<tr><td></td><td><b>" . $data[0]->qtd . "</b></td><td><b>" . PLib::format_cash($data[0]->valor_pago) . "</b></td><td><b>" . PLib::format_cash($data[0]->valor_liquido) . "</b></td></tr>";
                                    ?>
                                </table>


                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>


        <div id="postbox-container-2" class="postbox-container">
            <div id="postexcerpt" class="postbox ">
                <h3 class="hndle"><span>Inscrições</span></h3>

                <div class="inside">
                    <table width="100%">
                        <tr>
                            <th>Nome</th>
                            <th>Forma Pagamento</th>
                            <th>Valor Pago</th>
                            <th>Valor líquido</th>
                            <th>Situação</th>
                        </tr>
                        <?php
                        $inscritos = Inscricoes::getInstance()->getByEvento($evento->id, 'confirmado=1', 'forma_pagamento, nome');
                        // Totalizadores
                        $valorDisponivel=0;
                        $valorAReceber=0;
                        /* @var $inscrito Inscricao */
                        foreach ($inscritos as $inscrito) {
                            if ($inscrito->status_gateway==4)
                                $valorDisponivel=$valorDisponivel+PLib::coalesce($inscrito->valor_liquido, $inscrito->valor_pago);
                            else if ($inscrito->status_gateway==3)
                                $valorAReceber=$valorAReceber+PLib::coalesce($inscrito->valor_liquido, $inscrito->valor_pago);

                            echo "<tr>
                                    <td>" . $inscrito->pessoa()->nome . '</td>
                                    <td>' . $inscrito->titulo_forma_pagamento() . '</td>
                                    <td>' . PLib::format_cash($inscrito->valor_pago) . '</td>
                                    <td>' . PLib::format_cash(PLib::coalesce($inscrito->valor_liquido, $inscrito->valor_pago)) . '</td>
                                    <td>' . $inscrito->titulo_status_gateway() . '</td>
                                    </tr>';
                        }
                        ?>
                    </table>

                    <br><br>
                    <h3>Gateway PagSeguro</h3>
                    Valor Disponível: <?php echo PLib::format_cash($valorDisponivel); ?><br>
                    Valor a Receber: <?php echo PLib::format_cash($valorAReceber); ?><br>
                    Despesas Gateway: <?php echo PLib::format_cash($data[0]->valor_pago - $data[0]->valor_liquido); ?><br>
                </div>
            </div>
        </div>
    </div>
</div>