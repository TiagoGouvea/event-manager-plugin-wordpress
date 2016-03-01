<?php
use lib\PagSeguroUtil;
use TiagoGouvea\PLib;
$perguntas = Perguntas::getInstance()->getByQuestionario(1);
$inscritosAvaliacao = Inscricoes::getInstance()->getPresentesComAvaliacao($evento->id);



?>
<div class="wrap">
    <?php
    menuEvento($evento, 'evento-inscricoes', null, 'Avaliações');
    ?>
    <div id="poststuff">

        <?php require_once 'avaliacoes_resumo.php'; ?>

        <div id="postbox-container-2" class="postbox-container">
            <div id="postexcerpt" class="postbox ">
                <h3 class="hndle"><span>Avaliações</span></h3>
                <div class="inside">
                    <table width="100%">
                        <tr>
                            <th>Pessoa</th>
                            <?php foreach ($perguntas as $pergunta): ?>
                                <th><?php echo $pergunta->titulo; ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <?php
                            /* @var $inscrito Inscricao */
                            foreach ($inscritosAvaliacao as $inscrito):
                            ?>
                            <tr>
                                <td style="width: 120px;">
                                    <a href="admin.php?page=Pessoas&action=view&id=<?php echo $pessoa->id; ?>">
                                        <?php echo $inscrito->pessoa()->primeiro_segundo_nome(); ?>
                                    </a>
                                </td>
                                <?php foreach ($perguntas as $pergunta): ?>
                                    <td <?php echo ($pergunta->tip_pergunta=='star'? "width='140'":""); ?>><?php
                                        $resposta =$inscrito->getAvaliacaoResposta($pergunta->id);
                                        if ($resposta==null) continue;
                                        if ($pergunta->tip_pergunta=='star'){
                                            ?><center>
                                            <div class="course-rating rating-form">
                                                <div id="score_<?php echo $resposta->id; ?>"></div>
                                            </div>
                                            </center>
                                            <script>
                                                jQuery(document).ready(function () {
                                                    jQuery('#score_<?php echo $resposta->id; ?>').raty({
                                                        score: <?php echo $resposta->resposta; ?>,
                                                        readOnly: true,
                                                        path: '<?php echo plugins_url('/Eventos/public/img/'); ?>'
                                                    });
                                                });
                                            </script>
                                            <?php
                                        } else {
                                            echo stripslashes($resposta->resposta);
                                        }
                                        ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>