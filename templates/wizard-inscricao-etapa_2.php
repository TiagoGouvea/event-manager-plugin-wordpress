<?php

use TiagoGouvea\PLib;

function etapaTitulo(){
    echo "Dados pessoais";
}

function etapaIntroducao(){
    ?>
        <p>Por favor, preencha seus dados nos campos abaixo.</p>
    <?php
}

/**
 * @param $evento Evento
 * @param $pessoa Pessoa
 * @param $inscricao Inscricao
 */
function etapaConteudo($evento,$pessoa,$inscricao){
    $estados = array("AC"=>"Acre", "AL"=>"Alagoas", "AM"=>"Amazonas", "AP"=>"Amapá","BA"=>"Bahia","CE"=>"Ceará","DF"=>"Distrito Federal","ES"=>"Espírito Santo","GO"=>"Goiás","MA"=>"Maranhão","MT"=>"Mato Grosso","MS"=>"Mato Grosso do Sul","MG"=>"Minas Gerais","PA"=>"Pará","PB"=>"Paraíba","PR"=>"Paraná","PE"=>"Pernambuco","PI"=>"Piauí","RJ"=>"Rio de Janeiro","RN"=>"Rio Grande do Norte","RO"=>"Rondônia","RS"=>"Rio Grande do Sul","RR"=>"Roraima","SC"=>"Santa Catarina","SE"=>"Sergipe","SP"=>"São Paulo","TO"=>"Tocantins");

    ?>
    <input type="hidden" name="c_utmz" id="c_utmz" value="" />
        <script type="text/javascript">
        function read_cookie(a){
            var b = a + "=";
            var c = document.cookie.split(";");
            for (var d = 0; d < c.length; d++) {
                var e = c[d];
                while (e.charAt(0) == " ")e = e.substring(1, e.length);
                if (e.indexOf(b)==0){
                    return e.substring(b.length,e.length)
                }
            }
            return 'tiago';
        }
        try{
            document.getElementById("c_utmz").value=read_cookie("__utmz");
        }catch(err){}
        </script>

    <h4>Nome</h4>
    <div class="field-wrapper">
        <?php echo input_texto_simples('nome', '', 30, PLib::coalesce($_POST['nome'],$pessoa->nome)); ?>
    </div>

    <h4>Email</h4>
    <div class="field-wrapper">
        <?php echo input_texto_simples('email', '', 30,PLib::coalesce($_POST['email'],$pessoa->email)); ?>
    </div>

    <h4>Celular</h4>
    <div class="field-wrapper">
        <?php echo input_texto_simples('celular', '', 30, PLib::coalesce($_POST['celular'],$pessoa->celular)); ?>
    </div>

    <?php if ($evento->confirmacao!='preinscricao' && $evento->id_organizador==597): ?>
        <h4>Endereço</h4>
        <div class="field-wrapper">
            <?php echo input_texto_simples('end_cep', 'CEP', 30,$pessoa->end_cep,'onkeypress="javascript:MascaraCep(this);"' ); ?>
        </div>
        <div class="field-wrapper">
            <?php echo input_texto_simples('end_logradouro', 'Logradouro', 30,$pessoa->end_logradouro ); ?>
        </div>
        <div class="fourcol column">
            <div class="field-wrapper">
                <?php echo input_texto_simples('end_numero', 'Numero', 30 ,$pessoa->end_numero); ?>
            </div>
        </div>
        <div class="">
            <div class="field-wrapper">
                <?php echo input_texto_simples('end_complemento', 'Complemento', 30,$pessoa->end_complemento ); ?>
            </div>
        </div>
        <div class="">
            <div class="field-wrapper">
                <?php echo input_texto_simples('end_bairro', 'Bairro', 30,$pessoa->end_bairro ); ?>
            </div>
        </div>
        <div class="">
            <div class="field-wrapper">
                <?php echo input_texto_simples('end_cidade', 'Cidade', 30,$pessoa->end_cidade); ?>
            </div>
        </div>
        <div class="">
            <div class="field-wrapper">
                <?php echo input_select_simples('end_estado', 'Estado', $estados, $pessoa->end_estado); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // Existem campos extras?
    if ($evento->campos_extras!=null){
//        echo "<br><h3>Informações extra</h3><p>Por favor, preencha adequadamente os campos abaixo, pois em determinados eventos aprovaremos a inscrição de acordo com estes dados.</p>";

        $camposExtra='';
        // Obter extras da pessoa

        // Obter extras para exibição

        $pessoaExtras = $pessoa->extras;
        if ($pessoaExtras!=null) $pessoaExtras=json_decode ($pessoaExtras);

        $campos_extra=$evento->getCamposExtras();
        foreach ($campos_extra as $extraIndice => $extraTitulo):?>
            <?php
            // Este dado já existe nesta pessoa?
            $pessoaExtra = $pessoaExtras->$extraIndice;
            if ($_POST[$extraIndice]!=null)
                $pessoaExtra = $_POST[$extraIndice];
            else if ($pessoaExtra!=null)
                $pessoaExtra = PLib::unicode_to_utf8($pessoaExtra->valor);
            ?>

            <div class="">
                <?php
                if (strpos($extraTitulo,'[ ]')!==false){
                    $extraTitulo = str_replace('[ ]','',$extraTitulo);
                    echo input_checkbox_padrao($extraIndice,$extraTitulo,$pessoaExtra);
                } elseif (strpos($extraTitulo,'[file]')!==false){
                    $extraTitulo = str_replace('[file]','',$extraTitulo);

                    echo "<h4>$extraTitulo</h4>";
                    echo "<div class=field-wrapper>";
                    echo "<input type='file' name='$extraIndice' id='$extraIndice'>";
                    echo "</div>";

                } elseif (strpos($extraTitulo,'[')!==false && strpos($extraTitulo,']')!==false){
                    // Criar select
                    $opcoes = substr($extraTitulo,strpos($extraTitulo,'['));
                    $extraTitulo = str_replace($opcoes,"",$extraTitulo);
                    $opcoes = substr($opcoes,1,strlen($opcoes)-2);
                    $opcoes = explode(",",$opcoes);
                    $select=array();
                    foreach ($opcoes as $opcao)
                        $select[$opcao]=$opcao;
                    echo "<h4>$extraTitulo</h4>";
                    $return = "<select name=$extraIndice id=$extraIndice placeholder='$extraTitulo'>";
                    foreach ($select as $chave => $valor) {
                        $selecionado = $_POST[$extraIndice];
                        $selected = ($selecionado!=null && $chave == $selecionado ? "selected" : "");
                        $return .= "<option value='$chave' $selected>$valor</option>";
                    }
                    $return .= "</select>";

                    echo "<div class=field-wrapper>$return</div>";
                } elseif (strpos($extraTitulo,'( )')!==false){
                    // Criar Radios
                    $opcoes = substr($extraTitulo,strpos($extraTitulo,'('));
                    $extraTitulo = str_replace($opcoes,"",$extraTitulo);
                    $opcoes = substr($opcoes,0,strlen($opcoes));
                    $opcoes = explode(",",$opcoes);
                    $select=array();
                    $i=0;
                    foreach($opcoes as $opcao){
                        $i++;
                        $id="option_".$i;
                        $opcao=str_replace("( )","",trim($opcao));
                        $return.=label($id,$opcao,input_radio_padrao($extraIndice,$opcao,null,null,null,$id));
                    }

                    echo "<h4>$extraTitulo</h4>";
                    echo "<div class=field-wrapper>$return</div>";
                } else {
                    echo "<h4>$extraTitulo</h4>";
                    echo "<div class=field-wrapper>";
                    echo input_texto_simples($extraIndice, '', 30, $pessoaExtra);
                    echo "</div>";
                }
                ?>
            </div>
        <?php
        endforeach;
    }
    ?>

    <?php
}