<?php
// Input de inscrição

//[input_inscricao chave=cpf]


add_shortcode('input_inscricao', 'shortcode_input_inscricao');
function shortcode_input_inscricao($atts)
{
    if (get_the_ID()==null) return;

    $chave=null;
    extract(shortcode_atts(array(
        'chave' => 'email',
        'id_evento' => null
    ), $atts));

    $evento = Eventos::getInstance()->getById(get_the_ID());

    $out='<div class="field-wrapper">
            CPF:<Br><input id="input_'.$chave.'" type="text" /><br>
            <input onclick="javascript:inscrever(\''.$chave.'\');" type="button" value="Inscrever" class="button" />
           </div>';

    return $out;
}



// Lista os eventos
add_shortcode('eventos', 'shortcode_eventos');

function shortcode_eventos($atts)
{
    $ignorar=null;
    $passado=null;
    $futuro=null;
    $limite=null;
    extract(shortcode_atts(array(
        'limite' => null,
        'colunas' => '4',
        'ordenar' => true,
        'futuro' => true,
        'passado' => false
    ), $atts));

    if ($ignorar != null) $where = 'ID<>' . $ignorar;

    // linhas, futuro, passado
    $evento = null;
    if ($passado == true) {
        $eventos = Eventos::getInstance()->getPassados();
        //var_dump($eventos);
    } else if ($futuro == true) {
        $eventosAtuais = Eventos::getInstance()->getAcontecendo(true,null,$where);
        $eventosFuturos = Eventos::getInstance()->getFuturos(true,null,$where);
        $eventos = array_merge($eventosAtuais,$eventosFuturos);
    }

    $out=null;
    $counter = 0;
    //$out = '<div class="courses-listing clearfix">';
    if ($eventos != null && count($eventos) > 0) {
        foreach ($eventos as $evento) {
            $counter++;

            // Obter template
            $template = 'content-tgo_evento-row.php';
            ob_start();
            include(templateLocate($template));
            $out .= ob_get_contents();
            ob_end_clean();

            // Limite de eventos
            if ($limite!=null && $counter==$limite)
                break;
        }
        $out.='<div class="clear"></div>';
    }

    return $out;
}


// Conta os eventos realizados
add_shortcode('quantidade_eventos_passado', 'shortcode_quantidade_eventos_passado');
function shortcode_quantidade_eventos_passado($atts, $content = null)
{
    $eventos = Eventos::getInstance()->getPassados();
    return count($eventos);
}

// Conta os inscritos em eventos
add_shortcode('quantidade_inscritos', 'shortcode_quantidade_inscritos');
function shortcode_quantidade_inscritos($atts, $content = null)
{
    $qtd = Organizadores::getInstance()->getCountTodosInscritos();
    return $qtd;
}


//Google Map
add_shortcode('mapa', 'shortcode_mapa');
function shortcode_mapa($atts, $content = null)
{
    extract(shortcode_atts(array(
        'latitude' => '40.714',
        'longitude' => '-74',
        'place' => '',
        'cidade' => '',
        'zoom' => '16',
        'height' => '165',
        'description' => '',
    ), $atts));

    $place = urlencode($place);
    $cidade = urlencode($cidade);

    //wp_enqueue_script('google-map', 'http://maps.google.com/maps/api/js?sensor=false');


    $out = "<iframe width='100%' height='$height' frameborder='0' style='border:0'
                   src='https://www.google.com/maps/embed/v1/place?key=AIzaSyBtwOok4GbrxDyI-Au8JrPbz1eFWyJOj6k&q=$place,$cidade'>
                </iframe>";
    return $out;

    //center=$latitude,$longitude&zoom=16&maptype=roadmap

    //$out='<div class="google-map-container"><div class="google-map" id="google-map" style="height:'..'px"></div><input type="hidden" class="map-latitude" value="'.$latitude.'" />';
    //$out.='<input type="hidden" class="map-longitude" value="'.$longitude.'" /><input type="hidden" class="map-zoom" value="'.$zoom.'" /><input type="hidden" class="map-description" value="'.$description.'" /></div>';

    return $out;
}




// Avatar
add_shortcode('avatar', 'shortcode_avatar');
function shortcode_avatar($atts)
{
    $id=$atts['id'];
    if ($id==null) $id=$atts['pessoa'];
    if ($id==null) return null;
    /* @var $pessoa Pessoa */
    if (\TiagoGouvea\PLib::only_numbers($id)==$id)
        $pessoa = Pessoas::getInstance()->getById($id);
    else
        $pessoa = Pessoas::getInstance()->getByMd5($id);

    ob_start();
    ?>
    <div class="avatar">
        <div class="avatar-meta">
            <div class="avatar-image">
                <a href='<?php echo home_url('/perfil/?pessoa='.$pessoa->id*131313); ?>'>
                    <img src="<?php echo $pessoa->getPictureUrl(110); ?>" width="110">
                </a>
            </div>
            <div class="avatar-links">
                <?php
                $icones = array('facebook', 'twitter', 'instagram', 'gplus', 'linkedin', 'site');
                foreach ($icones as $icone) {
                    if ($pessoa->hasExtra($icone)): ?>
                        <a href="<?php echo $pessoa->getExtra($icone); ?>" class="<?php echo $icone; ?>_gray" target="_blank"></a>
                    <?php endif;
                }
                ?>
            </div>
        </div>
        <div class="avatar-text">
            <h4 class="nomargin">
                <a href='<?php echo home_url('/perfil/?pessoa='.$pessoa->id*131313); ?>'>
                    <?php echo $pessoa->nome; ?>
                </a>
            </h4>
            <p style="padding-top:10px;"><?php echo $pessoa->getExtra('minibio'); ?></p>
        </div>
    </div>

    <?php $pessoa->getStructuredDataJs(); ?>



    <?php
    $out = ob_get_contents();
    ob_end_clean();
    return $out;
}
