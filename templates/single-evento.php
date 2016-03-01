<?php
get_header();
?>

<div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">
        <?php
        $args = array('post_type' => 'tgo_evento');
        //$loop = new WP_Query($args);
        //while ($loop->have_posts()) : $loop->the_post();
        the_title();
        echo '<div class="entry-content">';
        the_content();
        echo '</div>';
        echo "<br>Data: " . get_post_meta(get_the_ID(), 'data', 1);
        // Se não iniciou, mostrar data
        echo "<br>Inicio Inscrições: " . get_post_meta(get_the_ID(), 'dataInicioInscricoes', 1);
        // Se não terminou mostrar data
        echo "<br>Fim Inscrições: " . get_post_meta(get_the_ID(), 'data_fimInscricoes', 1);
        // Vagas / Vagas restantes
        echo "<br>Vagas: " . get_post_meta(get_the_ID(), 'vagas', 1);
        echo "<br>Tipo Inscricao: " . get_post_meta(get_the_ID(), 'pago', 1);
        echo "<br>Confirmação: " . get_post_meta(get_the_ID(), 'confirmacao', 1);
        echo "<br>Validacao Pessoa: " . get_post_meta(get_the_ID(), 'validacaoPessoa', 1);

        // Se no período de inscrição, botão para inscrever
        $dataInicio = get_post_meta(get_the_ID(), 'dataInicioInscricoes', 1);
        $data_fim = get_post_meta(get_the_ID(), 'data_fimInscricoes', 1);

        $dataInicio=strtotime($dataInicio);
        $data_fim=strtotime($data_fim) + (24 * 60 * 60);
        
        $inscricaoAberta = $dataInicio < time() && $data_fim > time();
        
        // Inscrição aberta?
        if ($inscricaoAberta){
            echo "<br><br><a href=".get_permalink()."/?inscricao=1>Inscrever</a>";
            //echo "<br><br><a href='".site_url()."/inscricao/?evento=".get_the_ID()."'>Inscrever</a>";
        }

        //endwhile;
        ?>
    </div>
</div>

<?php
get_footer();


