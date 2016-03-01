<?php /* @var $badge Badge */
use TiagoGouvea\PHPGamification\Model\Badge; ?>
<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2>Badge</h2>

    <form method="post" enctype="multipart/form-data">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox-container">
                        <div class="postbox">

                            <?php if ($badge): ?>
                                <h3 class="hndle"><span><?php echo $badge->getTitle(); ?></span></h3>
                                <input type="hidden" name="id" value="<?php echo $badge->getId(); ?>">
                                <div class="inside">
                                    <?php
                                    echo label('title', 'Título', input_texto_simples('title', 'Título', 40, $badge->getTitle()));
                                    echo label('alias', 'Alias', input_texto_simples('alias', 'Alias', 40, $badge->getAlias()));
                                    echo label('description', 'Descrição', input_textarea_simples('description', 5, $badge->getDescription()) );
                                    echo '<img src="'.get_template_directory_uri() . '/img/gamification/' . $badge->getAlias() . '.png'.'" style="width: 80px;"/>';
                                    echo input_file("arquivo","Imagem","");
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
                                    echo label('title', 'Título', input_texto_simples('title', 'Título', 40, $_POST['title']));
                                    echo label('alias', 'Alias', input_texto_simples('alias', 'Alias', 40, $_POST['alias']));
                                    echo label('description', 'Descrição', input_textarea_simples('description', 5, $_POST['description']) );
                                    echo input_file("arquivo","Imagem","");
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