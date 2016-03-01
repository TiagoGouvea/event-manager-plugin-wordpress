<div id="postbox-container-2" class="postbox-container">
    <div id="postexcerpt" class="postbox ">
        <h3 class="hndle"><span>Inscrições</span></h3>

        <div class="inside">

            <table width="100%">
                <tr>
                    <th>Inscrições</th>
                </tr>
                <tr>
                    <td>
                        <?php
                        $pageViews = getPageViewsEvento($id_evento);
                        $visitantes = getVisitantesEvento($id_evento);

                        $inscritos = $evento->qtdInscritos;
                        $confirmados = $evento->qtdConfirmados;
                        $naoConfirmados = $evento->qtdNaoConfirmados();
                        $cancelados = $evento->qtdCancelados();
                        ?>

                        <?php if ($evento->preInscricao): ?>
                            Pré-inscritos: <b><?php echo $evento->qtdPreInscritos; ?></b><br>
                        <?php endif; ?>

                        <a href="">Inscritos</a>: <b><?php echo $inscritos; ?></b><br>
                        <a href="?filtro=confirmados">Confirmados</a>: <b><?php echo $confirmados; ?></b><br>

                        <?php if ($naoConfirmados): ?>
                        <a href="?filtro=naoConfirmados">Pendentes</a>: <b><?php echo $naoConfirmados; ?></b><br>
                        <?php endif; ?>

                        <?php if ($cancelados): ?>
                            <a href="?filtro=cancelados">Cancelados</a>: <b><?php echo $cancelados; ?></b><br>
                        <?php endif; ?>

                        <?php if (eventoPago==1): ?>
                            Lote Atual: <?php echo $evento->aa; ?><br>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>