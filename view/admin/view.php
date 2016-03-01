<div class="wrap">
    <?php
    use TiagoGouvea\PLib;


    //menuEvento($evento,'evento-view');
    ?>

    <div id="poststuff">
        <?php
        // Inscritos
        $arquivo=get_stylesheet_directory() . '/list_inscritos_'.$evento->id.'.php';
        if (file_exists($arquivo))
            require_once $arquivo;
        else {
            if ($inscritos==null)
                $inscritos = Inscricoes::getInstance()->getByEvento($evento->id);
            $arquivo=PLUGINPATH . '/view/admin/list_inscritos.php';
            require_once $arquivo;
        }

        ?>
    </div>

</div>