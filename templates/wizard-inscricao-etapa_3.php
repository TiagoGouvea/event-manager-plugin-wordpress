<?php

use TiagoGouvea\PLib;

function etapaTitulo(){
    echo "Categoria de Inscrição";
}

function etapaIntroducao($evento,$pessoa){
    ?>
        <p><?php echo $pessoa->nome; ?>, selecione agora sua categoria para inscrição.</p>
    <?php
}

/**
 * @param $evento Evento
 * @param $pessoa Pessoa
 * @param $inscricao Inscricao
 */
function etapaConteudo($evento,$pessoa,$inscricao){
    $categorias = $evento->getCategorias($pessoa);
//    PLib::var_dump($categorias);
    /** @var $categoria Categoria */
    foreach($categorias as $categoria){
        echo input_radio_padrao(
                "categoria",
                $categoria->id,
                "&nbsp;&nbsp;".$categoria->titulo." - ".PLib::format_cash($categoria->getPreco()->valor),
                null,
                ($categoria->_permiteInscricao===false?"disabled":"")
            )."<br><br>";
    }
}