<div id="postbox-container-2" class="postbox-container">
    <div id="postexcerpt" class="postbox ">
        <h3 class="hndle"><span>Inscrições</span></h3>

        <div class="inside">

            <table width="100%">
                <tr>
                    <th>Inscritos</th>
                    <th>Vagas</th>
                    <th>Preços/Lotes</th>
                    <?php
                    use TiagoGouvea\PLib;

                    if ($evento->pago == 'pago'): ?>
                        <th>Recebimentos</th>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td>
                        <?php
                        $pageViews = getPageViewsEvento($id_evento);
                        $visitantes = getVisitantesEvento($id_evento);

                        $preInscritos = $evento->qtdPreInscritos();
                        $inscritos = $evento->qtdInscritos();
                        $inscritosNovos = $evento->qtdInscritosNovos();

                        $confirmados = $evento->qtdConfirmados();
                        $naoConfirmados = $evento->qtdNaoConfirmados();
                        $cancelados = $evento->qtdCancelados();
                        $filaEspera = $evento->qtdFilaEspera();
                        $qtdPresentes = $evento->qtdPresentes();

                        ?>

                        <?php if ($preInscritos): ?>
                            <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>&filter=preInscritos">Pré-Inscritos</a>: <b><?php echo $preInscritos; ?></b><br>
                        <?php endif; ?>

                        <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>">Inscritos</a>: <b><?php echo $inscritos; ?></b><br>
                        <?php if ($inscritosNovos): ?>
                            <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>">Inscritos Novos</a>: <b><?php echo $inscritosNovos; ?></b><br>
                        <?php endif; ?>
                        <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>&filter=confirmados">Confirmados</a>: <b><?php echo $confirmados; ?></b><br>

                        <?php if ($naoConfirmados): ?>
                            <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>&filter=naoConfirmados">Pendentes</a>: <b><?php echo $naoConfirmados; ?></b><br>
                        <?php endif; ?>

                        <?php if ($qtdPresentes): ?>
                            <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>&filter=presentes">Presentes</a>: <b><?php echo $qtdPresentes; ?></b><br>
                        <?php endif; ?>

                        <?php if ($filaEspera): ?>
                            <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>&filter=filaEspera">Fila de Espera</a>: <b><?php echo $filaEspera; ?></b><br>
                        <?php endif; ?>

                        <?php if ($cancelados): ?>
                            <a href="admin.php?page=Inscricoes&id_evento=<?php echo $evento->id; ?>&filter=rejeitados">Cancelados</a>: <b><?php echo $cancelados; ?></b><br>
                        <?php endif; ?>

                        <?php if (eventoPago == 1): ?>
                            Lote Atual: <?php echo $evento->aa; ?><br>
                        <?php endif; ?>
                    </td>
                    <td>
                        Vagas para evento: <b><?php echo $evento->vagas; ?></b><br>
                        Vagas disponíveis: <b><?php echo $evento->vagasDisponiveis(); ?></b>
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
                                    echo '<b>Atual:</b><br>' . $preco->titulo . ' - vagas: ' . $preco->vagas . ' - restantes: ' . $preco->getVagasRestantes() . ' - '.PLib::format_cash($preco->valor).'<Br>';
                                    if (count($precos) > 1)
                                        echo "<br><b>Encerrados:</b><br>";
                                }
                            }
                        }
                        ?>
                    </td>
                    <?php if ($evento->pago == 'pago'): ?>
                        <td><b>Por Preço:</b><br>
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
                                    echo $dado->titulo . ' - Confirmados: ' . $dado->qtd . ' - Recebido: ' . \TiagoGouvea\PLib::format_cash($dado->valor_pago) . '<br>';
                                }
                            }
                            ?>
                            <br>
                            <b>Por Forma de Pagamento:</b><br>
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
                                    echo $dado->forma_pagamento . ' - Confirmados: ' . $dado->qtd . ' - Recebido: ' . PLib::format_cash($dado->valor_pago) . ' - Valor Líquido: ' . PLib::format_cash($dado->valor_liquido) . '<br>';
                                }
                            }
                            ?><br>
                            <b>Recebido:</b><br>
                            <?php
                            // Obter total
                            global $wpdb;
                            $sql = "select coalesce(sum(ev_inscricoes.valor_pago),0) as valor_pago, coalesce(sum(coalesce(ev_inscricoes.valor_liquido,ev_inscricoes.valor_pago)),0) as valor_liquido
                                    from ev_inscricoes
                                    where ev_inscricoes.id_evento=$evento->id and ev_inscricoes.confirmado=1";
                            $data = $wpdb->get_results($sql);
                            if ($data) {
                                echo "Total: " . PLib::format_cash($data[0]->valor_pago) . "<br>";
                                echo "Liquido: " . PLib::format_cash($data[0]->valor_liquido) . "<br>";
                            }
                            ?>
                        </td>
                    <?php endif; ?>
                </tr>
            </table>

            <!--            <div class="inside">-->
            <!--                <table width="100%">-->
            <!--                    <thead>-->
            <!--                    <th width="300">Ação</th>-->
            <!--                    <th>Detalhes</th>-->
            <!--                    </thead>-->
            <!--                    <tr>-->
            <!--                        <Td>-->
            <!--                            <a href="admin.php?page=Inscricoes&id_evento=--><?php //echo $evento->id; ?><!--">Inscritos</a>-->
            <!--                        </td>-->
            <!--                        <td>Acessar relação de inscritos no evento</td>-->
            <!--                    </tr>-->
            <!--                    <tr>-->
            <!--                        <Td>-->
            <!--                            <a href="admin-ajax.php?page=Inscricoes&action=presencaImprimir&id_evento=--><?php //echo $evento->id; ?><!--&nonce='.$nonce.'">Impressão de Presentes</a>-->
            <!--                        </td>-->
            <!--                        <td>Imprimir relação para marcação de presença</td>-->
            <!--                    </tr>-->
            <!--                </table>-->
            <!--            </div>-->

        </div>
    </div>
</div>