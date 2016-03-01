<?php
if ($inscritosAvaliacao==null)
    $inscritosAvaliacao = Inscricoes::getInstance()->getPresentesComAvaliacao($evento->id);

$avaliacaoInstrutor = $evento->getAvaliacaoMediaPergunta('1');
$avaliacaoTreinamento = $evento->getAvaliacaoMediaPergunta('3');
$avaliacaoMaterial = $evento->getAvaliacaoMediaPergunta('2');
?>
<div id="postbox-container-2" class="postbox-container">
    <div id="postexcerpt" class="postbox ">
        <h3 class="hndle"><span>Resumo de Avaliações</span></h3>
        <div class="inside">
            <table width="100%">
                <tr>
                    <td>
                        <div style="margin-bottom: 20px;">
                            <center>
                                <div style="float: left; margin-left:30px;">
                                    <h5>Presentes no evento</h5>
                                    <span style="font-size: 16px; margin-top: 10px;"><?php echo $evento->qtdPresentes(); ?></span>
                                </div>
                                <div style="float: left; margin-left:30px;">
                                    <h5>Avaliações Recebidas</h5>
                                    <span style="font-size: 16px; margin-top: 10px;"><?php echo count($inscritosAvaliacao); ?></span>
                                </div>
                                <div style="float: left; margin-left:30px;">
                                    <h5>Conhecimento do instrutor</h5>
                                    <div id="score_instrutor"></div>
                                </div>
                                <div style="float: left; margin-left:30px;">
                                    <h5>Avaliação geral</h5>
                                    <div id="score_treinamento"></div>
                                </div>
                                <?php if ($avaliacaoMaterial>0):  ?>
                                    <div style="float: left; margin-left:30px;">
                                        <h5>Avaliação Material</h5>
                                        <div id="score_material"></div>
                                    </div>
                                <?php endif; ?>
                            </center>
                            <div class="clearfix"></div>
                        </div>
                        <script>
                            jQuery(document).ready(function () {
                                jQuery('#score_instrutor').raty({
                                    score: <?php echo $avaliacaoInstrutor; ?>,
                                    readOnly: true,
                                    starHalf : 'star-half-big.png',
                                    starOff : 'star-off-big.png',
                                    starOn  : 'star-on-big.png',
                                    path: '<?php echo plugins_url('/Eventos/public/img/'); ?>'
                                });
                                jQuery('#score_treinamento').raty({
                                    score: <?php echo $avaliacaoTreinamento; ?>,
                                    readOnly: true,
                                    starHalf : 'star-half-big.png',
                                    starOff : 'star-off-big.png',
                                    starOn  : 'star-on-big.png',
                                    path: '<?php echo plugins_url('/Eventos/public/img/'); ?>'
                                });
                                <?php if ($avaliacaoMaterial>0): ?>
                                jQuery('#score_material').raty({
                                    score: <?php echo $avaliacaoMaterial; ?>,
                                    readOnly: true,
                                    starHalf : 'star-half-big.png',
                                    starOff : 'star-off-big.png',
                                    starOn  : 'star-on-big.png',
                                    path: '<?php echo plugins_url('/Eventos/public/img/'); ?>'
                                });
                                <?php endif; ?>
                            });
                        </script>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>