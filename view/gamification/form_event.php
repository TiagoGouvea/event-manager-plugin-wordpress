<?php /* @var $event Event */
use lib\Gamification;
use TiagoGouvea\PHPGamification\Model\Event; ?>
<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Evento</h2>

    <form method="post" enctype="multipart/form-data">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">

                            <?php if ($event): ?>
                                <h3 class="hndle"><span><?php echo $event->getAlias(); ?></span></h3>
                                <input type="hidden" name="id" value="<?php echo $event->getId(); ?>">
                                <div class="inside">
                                    <?php
                                    echo label('description', 'Descrição', input_textarea_simples('description', 5, $event->getDescription()) );
                                    echo label('alias', 'Alias', input_texto_simples('alias', 'Alias', 40, $event->getAlias()));

                                    echo label('allow_repetitions', 'Permite Repetição', input_checkbox_padrao('allow_repetitions', 'Permite Repetição',$event->getAllowRepetitions()));

                                    echo label('reach_required_repetitions', 'Repetições Requeridas', input_texto_simples('reach_required_repetitions', '', 5, $event->getRequiredRepetitions()));

                                    // Badge
                                    $badges = Gamification::getInstance()->getBadgesArray();
                                    echo label('id_each_badge','Badge - para cada',input_select_simples('id_each_badge','Badge - para cada', $badges, $event->getEachCallback()));
                                    echo label('id_reach_badge','Badge - ao alcançar',input_select_simples('id_reach_badge','Badge - ao alcançar', $badges, $event->getReachCallback()));

                                    echo label('each_points', 'Pontos - para cada', input_texto_simples('each_points', '', 5, $event->getEachPoints()));
                                    echo label('reach_points', 'Pontos - ao alcançar', input_texto_simples('reach_points', '', 5, $event->getReachPoints()));

                                    //            'each_callback' => 'Callback - para cada',
                                    //            'reach_callback' => 'Callback - ao alcançar',
                                    ?>
                                </div>

                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="publish" id="publish"
                                               class="button button-primary button-large" value="Salvar" accesskey="p">
                                    </div>
                                    <div class="clear"></div>
                                </div>


                            <?php else: ?>

                                <h3 class="hndle"><span>Inserir Badge</span></h3>
                                <div class="inside">
                                    <?php
                                    echo label('description', 'Descrição', input_textarea_simples('description', 5, $_POST['description']) );
                                    echo label('alias', 'Alias', input_texto_simples('alias', 'Alias', 40, $_POST['alias']));

                                    echo label('allow_repetitions', 'Permite Repetição', input_checkbox_padrao('allow_repetitions', 'Permite Repetição'));

                                    echo label('reach_required_repetitions', 'Repetições Requeridas', input_texto_simples('reach_required_repetitions', '', 5, $_POST['reach_required_repetitions']));

                                    // Badge
                                    $badges = Gamification::getInstance()->getBadgesArray();
                                    echo label('id_each_badge','Badge - para cada',input_select_simples('id_each_badge','Badge - para cada', $badges));
                                    echo label('id_reach_badge','Badge - ao alcançar',input_select_simples('id_reach_badge','Badge - ao alcançar', $badges));

                                    echo label('each_points', 'Pontos - para cada', input_texto_simples('each_points', '', 5, $_POST['each_points']));
                                    echo label('reach_points', 'Pontos - ao alcançar', input_texto_simples('reach_points', '', 5, $_POST['reach_points']));

                                    //            'each_callback' => 'Callback - para cada',
                                    //            'reach_callback' => 'Callback - ao alcançar',

                                    ?>
                                </div>

                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="publish" id="publish"
                                               class="button button-primary button-large" value="Salvar" accesskey="p">
                                    </div>
                                    <div class="clear"></div>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>