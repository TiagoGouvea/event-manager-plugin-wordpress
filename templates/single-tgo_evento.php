<?php
/**
 * Template para exibição de todos os detalhes de um evento
 */
use TiagoGouvea\PLib;

get_header();

if(!is_user_logged_in()){
    // Registrar visita
    $visitante = Referer::getVisitante();
    // Se não for Bot, registrar no banco de visualizações de evento
    if (!$visitante->bot)
        Referer::registrarVisitaEvento($visitante,$post->ID);
}


// Precisando saber quais dados existem?
/* @var $evento Evento */
$evento = get_the_evento();
//echo "<pre>";var_dump($evento);echo "</pre>";
if ($evento==null) die('Variável $evento está null.... :(');

?>


    <div class="center pull-left" style="margin-bottom:20px;">
        <div id="content" class="clearfix">
            <div class="content_left">

                <div class="">
                    <h1>
                        <a itemprop="url" href="<?php echo get_permalink($evento->id) ?>">
                            <span itemprop="name"><?php echo $evento->titulo; ?></span>
                        </a>
                    </h1>
                    <?php echo get_the_post_thumbnail($evento->id,array(700,200)); ?>
                </div>

                <?php if ($evento->release!=""): ?>
                    Este evento foi realizado em <?php echo PLib::dataRelativa($evento->data." ".$evento->hora,false,false); ?>
                    <?php if ($evento->local()) echo " no ".$evento->local->titulo; ?>

                    <h3>Release do evento</h3>
                    <p><?php
                        $content = apply_filters( 'the_content', $evento->release );
                        $content = str_replace( ']]>', ']]&gt;', $content );
                        echo $content;
                        ?></p>
                <?php else: ?>
                    <p><?php
                        $content = apply_filters( 'the_content', $evento->descricao_3 );
                        $content = str_replace( ']]>', ']]&gt;', $content );
                        echo $content;
                        ?></p>

                    <?php if ($evento->noFuturo): ?>
                        <div class="">
                            <h3>Data</h3>
                            <p><?php echo PLib::date_relative($evento->data." ".$evento->hora,false,false); ?></p>
                        </div>
                    <?php elseif ($evento->acontecendo): ?>
                        <div class="">
                            <h3>Data e hora</h3>
                            <p>Evento em andamento agora! Término <?php echo strtolower(PLib::date_relative($evento->dataFim." ".$evento->horaFim)); ?>.</p>
                        </div>
                    <?php else: ?>
                        <div class="">
                            <h3>Data</h3>
                            <p>Evento realizado <?php echo PLib::date_relative($evento->data." ".$evento->hora,false,false); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!$evento->preInscricao): ?>
                        <?php if ($evento->id_instrutor): ?>
                            <div class="">
                                <h3>Coordenador</h3>
                                <p><?php
                                    $instrutor = Instrutores::getInstance()->getById($evento->id_instrutor);
                                    echo $instrutor->display_name;
                                    ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>



                    <?php if ($evento->noFuturo || $evento->acontecendo()): ?>
                        <div class="">
                            <h3>Inscrição</h3>
                            <?php if ($evento->inscricaoAberta || $evento->acontecendo()): ?>
                                <?php if ($evento->pago=='gratuito'): ?>
                                    <p>Evento gratuito e aberto ao público, necessário apenas realizar a inscrição!</p>
                                <?php else: ?>
                                    <p><?php
                                        $precos = PrecoEvento::obterPorEvento($evento->id);
                                        /* @var $preco Preco */
                                        foreach ($precos as $preco){
                                            if ($preco->encerrado==1){
                                                echo "<del>";
                                                echo $preco->titulo.' - encerrado!<Br>';
                                                echo "</del>";
                                            } else {
                                                echo $preco->titulo.' - '.PLib::format_cash($preco->valor).'<Br>';
                                                break;
                                            }
                                        }
                                        ?></p>
                                <?php endif; ?>
                                <br>
                                <p><a  href="<?php echo the_permalink()."?inscricao=1"; ?>" class="button_blue">Realizar Inscrição!</a></p>
                            <?php else: ?>
                                <p>Inscrições encerradas</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="clearfix"></div>

                    <?php if (!$evento->preInscricao && $evento->local!=null): ?>
                        <div class="clear"></div>
                        <div class="widget">
                            <div class="widget-title">
                                <h3 class="nomargin">Local do Evento</h3>
                            </div>
                            <div class="widget-content">
                                <p><strong><?php echo $evento->local->titulo; ?></strong><br>
                                    <?php echo nl2br($evento->local->endereco); ?><Br>
                                    <?php echo $evento->local->telefone; ?><Br>
                                    <?php echo $evento->local->site; ?>
                                </p>

                                <?php
                                $pos=array();
                                $pos['latitude']=$evento->local->latitude;
                                $pos['place']=$evento->local->titulo;
                                $pos['cidade']=$evento->local->cidade;
                                $pos['longitude']=$evento->local->longitude;
                                $pos['zoom']=16;
                                $pos['height']=200;
                                $pos['description']=$evento->local->post_title;
                                //echo shortcode_mapa($pos);
                                ?>

                                <?php echo get_the_post_thumbnail($evento->id_local); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <h3>Local do Evento</h3>
                        <p>Este evento será realizado em Juiz de Fora. O local exato ainda será definido.</p>
                    <?php endif; ?>


                    <?php
                    if ($evento->inscricaoAberta): ?>
                        <br>
                        <a class="add-calendar"
                           href="http://www.google.com/calendar/event?action=TEMPLATE&amp;text=<?php echo urlencode($evento->titulo); ?>&amp;dates=<?php echo date('Ymd\\THi00\\Z',$evento->inicioTimeStamp); ?>/<?php echo date('Ymd\\THi00\\Z',$evento->fimTimeStamp); ?>&amp;details=<?php echo urlencode(get_the_excerpt()); ?>&amp;location=<?php echo urlencode($evento->local->post_title); ?>" target="_blank" data-event="<?php echo $evento->titulo; ?>">
                            <img class="small-cal-icon" src="https://developers.google.com/_static/decaeda4dd/images/calendar-42.png" alt="Adicionar à agenda" style="margin-right: 10px;">Adicionar evento à minha agenda</a>
                    <?php endif; ?>

                <?php endif; ?>



            </div>

            <div class="clear"></div>
        </div><!--### ende content ###-->
    </div>

<?php get_footer(); ?>