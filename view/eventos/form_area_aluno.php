<?php /* @var $pessoa Pessoa */ ?>
<div class='wrap'>
    <div id='icon-edit' class='icon32'>
        <br/>
    </div>
    <h2><?php echo $evento->titulo; ?></h2>

    <form method="post">
        <div id="poststuff">
            <div class="postbox-container">
                <div class="postbox">
                    <h3 class="hndle"><span>Área do Aluno</span></h3>
                    <input type="hidden" name="id" value="<?php echo $pessoa->id; ?>">

                    <div class="inside">
                        <?php
                        $value = $evento->area_aluno;
                        wp_editor($value, 'area_aluno', array('textarea_rows' => get_option('default_post_edit_rows', 40)));
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
                </div>
            </div>
        </div>
    </form>
</div>