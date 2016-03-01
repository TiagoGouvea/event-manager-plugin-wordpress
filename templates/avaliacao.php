<?php
get_header();
/* @var $inscricao Inscricao */
if (round(get_query_var('avaliacao') / 13)!=get_query_var('avaliacao') / 13)
    die();
$inscricao = Inscricoes::getInstance()->getById(get_query_var('avaliacao') / 13);
if ($inscricao == null)
    die();
if ($inscricao->id_evento!=get_the_evento()->id)
    die();
/* @var $evento Evento */
$questionario = $inscricao->evento()->getQuestionarioAvaliacao();
$perguntas = $questionario->getPerguntas();
?>
    <div class="center pull-left" style="margin-bottom:20px;">
        <div id="content" class="clearfix">
            <h1><?php the_title(); ?></h1>

            <p>&nbsp;</p>
            <?php if (hasFlash()): ?>
                <p>Ótimo <?php echo ucfirst(mb_strtolower($inscricao->pessoa()->primeiro_nome())); ?>!
                <p>Recebemos sua avaliação, ela é muito importante para nós!</p>
                <p>Obrigado!</p>
            <?php else: ?>
            <?php if (hasFlashError()): ?>
                <div class="message" style="display: block;">
                    <ul class="error">
                        <li>
                            <?php echo implode("</li><li>", getFlashErrorArray()); ?>
                        </li>
                    </ul>
                </div>
                <p>&nbsp;</p>
            <?php endif; ?>

                <p>Obrigado <?php echo (mb_strtolower($inscricao->pessoa()->primeiro_nome())); ?> por ter participado deste evento conosco! Gostaríamos de receber seu feedback para melhorarmos cada vez mais nossa comunidade.</p>
                <br>
                <h2>Avaliação</h2>

                <div class="widget-content">
                    <form action="" method="POST">
                        <div class="clear"></div>

                        <?php foreach ($perguntas as $pergunta): ?>

                            <h4><?php echo $pergunta->titulo . ($pergunta->obrigatoria ? " *" : ""); ?></h4>
                            <?php echo($pergunta->descricao ? "<span class=descricao>" . $pergunta->descricao . "</span><br>" : ""); ?>
                            <div class="field-wrapper" style="margin-bottom: 10px;     display: inline-block;">
                                <?php if ($pergunta->tip_pergunta == 'star'): ?>
                                    <div id="rate_<?php echo $pergunta->id; ?>" style="float: left;"></div>
                                    <div id="div_<?php echo $pergunta->id; ?>" style="float: left; margin: 10px; color:darkgoldenrod; font-weight: bolder; font-size: 16px;"></div>
                                <?php elseif ($pergunta->tip_pergunta == 'textarea'): ?>
                                    <textarea id="question" name="input_<?php echo $pergunta->id; ?>" rows="7" style="height: 4em; width: 300px;"><?php echo $_POST['input_' . $pergunta->id]; ?></textarea>
                                <?php endif; ?>
                            </div>
                            <div class="clear"></div>

                        <?php endforeach; ?>

                        <p>&nbsp;</p>

                        <div class="clear"></div>

                        <div class="form-loader"></div>
                        <input type="submit" class="button_blue" value="Enviar">
                    </form>
                </div>

                <div class="clear"></div>
                <p>&nbsp;</p>
            <?php endif; ?>


        </div>
        <br><Br>
    </div>

    <script>
        var config = {
            number: 5
        };

        <?php foreach ($perguntas as $pergunta): ?>
        <?php if ($pergunta->tip_pergunta=='star'): ?>
        $('#rate_<?php echo $pergunta->id; ?>').raty(
            {
                number: 5,
                scoreName: 'input_<?php echo $pergunta->id; ?>',
                score: <?php echo \TiagoGouvea\PLib::coalesce($_POST['input_'.$pergunta->id],0); ?>,
                target: '#div_<?php echo $pergunta->id; ?>',
                hints: ['Ruim', 'Regular', 'Bom', 'Ótimo', 'Excelente']
            });
        <?php endif; ?>
        <?php endforeach; ?>
    </script>

<?php
get_footer();
?>