<div class="wrap">

    <?php
    use TiagoGouvea\PLib;
    menuEvento($evento,'evento-view');
    ?>

    <div id="poststuff">
        <div id="postbox-container-2" class="postbox-container">
            <div id="postexcerpt" class="postbox ">
                <h3 class="hndle"><span>Resumo do Evento</span></h3>
                <div class="inside">
                    <table width="100%">
                        <Tr>
                            <th>Acessos</th>
                            <th>Conversões</th>
                            <th>Evento</th>
                        </Tr>
                        <tr>
                            <td>
                                <?php
                                    $pageViews = getPageViewsEvento($id_evento);
                                    $visitantes = getVisitantesEvento($id_evento);
                                ?>

                                Page Views: <b><?php echo $pageViews; ?></b><br>
                                Visitantes Únicos: <b><?php echo $visitantes; ?></b><br>

                                <?php if (eventoPago==1): ?>
                                    Lote Atual: <?php echo $evento->aa; ?><br>
                                <?php endif; ?>
                            </td>
                            <td>
                                Visitantes Inscritos: <b><?php echo $evento->conversaoVisitantesInscritos; ?></b><br>
<!--                                Visitantes Confirmados: <b>--><?php //echo $evento->conversaoVisitantesConfirmados; ?><!--</b><br>-->
                                Inscritos Confirmados: <b><?php echo $evento->conversaoInscritosConfirmados; ?></b><br>
                                <?php if ($evento->qtdPresentes>0): ?>
                                    Confirmados Presentes: <b><?php echo $evento->conversaoConfirmadosPresentes; ?></b>
                                <?php endif; ?>
                            </td>
                            <Td>
                                <?php
                                $momento;
                                if ($evento->noFuturo)
                                    $momento = "No futuro";
                                else if ($evento->acontecendo)
                                    $momento = "Acontencendo agora";
                                else
                                    $momento = "Já realizado";

                                ?>

                                Data Evento: <b><?php echo PLib::date_relative($evento->data." ".$evento->hora,true,false); ?></b><br>
                                Momento Evento: <b><?php echo $momento; ?></b><br>
                                Aceitando Inscrições: <b><?php echo ($evento->inscricaoAberta ? "Sim" : "Não"); ?></b><br>
                            </Td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($evento->realizado() && $evento->hasAvaliacao()) require_once 'avaliacoes_resumo.php'; ?>

        <?php require_once 'view_bloco_inscricoes.php'; ?>

<!--        --><?php //require_once 'view_bloco_inscricoes_comunicacao.php'; ?>

<!--        --><?php //require_once 'view_bloco_inscricoes_configuracoes.php'; ?>
    </div>

</div>