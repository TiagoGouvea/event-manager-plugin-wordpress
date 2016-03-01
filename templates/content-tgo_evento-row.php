<?php
/**
 * Template para exibição de eventos em lista
 * Se for alterar, melhor copia-lo para a raiz da pasta de seu template
 */
use TiagoGouvea\PLib;

/* @var $evento Evento */
// var_dump($evento);
?>
    <div itemscope itemtype="http://schema.org/Event" class="evento">
        <h3>
            <a itemprop="url" href="<?php echo get_permalink($evento->id) ?>">
                <span itemprop="name"><?php echo $evento->titulo; ?></span>
            </a>
            <div class="g-plusone"  data-href="<?php echo get_permalink($evento->id) ?>"></div>
        </h3>

        <a href="<?php echo get_permalink($evento->id) ?>" title="<?php echo $evento->titulo; ?>">
            <?php echo get_the_post_thumbnail($evento->id,array(700,200)); ?>
        </a>

        <?php if ($evento->noFuturo()): ?>
            <h4>
                <?php
                    echo PLib::date_relative($evento->data." ".$evento->hora, true, false);
                    if ($evento->local()) echo " no " . $evento->local()->titulo;
                ?>
            </h4>
            <p><?php if (get_the_excerpt()!=null) the_excerpt(); else echo $evento->descricao_2; ?></p>
        <?php else: ?>
            <h4>
                <?php
                    echo "Evento realizado ".PLib::date_relative($evento->data, false, false);
                    if ($evento->local()) echo " no " . $evento->local()->titulo;
                ?>
            </h4>

            <p>
                <?php
                $release = strip_tags($evento->release);
                $limit = 230;
                if (strlen($release) > $limit){
                    $release = substr($release, 0, strrpos(substr($release, 0, $limit), ' ')) . '...';
                    $release.=" <a href='".get_permalink($evento->id)."'>continuar lendo</a>";
                }

                echo $release;
                ?>
            </p>
        <?php endif; ?>
    </div>

<?php

?>