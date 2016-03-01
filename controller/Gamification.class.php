<?php
use lib\Controller;
use lib\Gamification;
use TiagoGouvea\PHPGamification\Model\Event;

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 13/06/15
 * Time: 11:02
 */

//require_once PLUGINPATH . '/vendor/TiagoGouvea/PHPGamification/PHPGamification.class.php';
//require_once PLUGINPATH.'/vendor/TiagoGouvea/PHPGamification/DAO.class.php';

class ControllerGamification extends Controller
{
    public static function dispatcher()
    {
        $action = $_GET['action'];
        $page = $_GET['page'];


        if ($page=='Gamification' && $action==null)
            self::showLevelRanking();

        if ($page=='badges') {
            if (isset($_GET['id']))
                $badge = Gamification::getInstance()->getBadge($_GET['id']);
            if ($action == null)
                self::badgeList();
            if (($action == 'add-new' || $action == 'edit'))
                self::addBadge($badge);
        }
        if ($page=='eventos') {
            if (isset($_GET['id']))
                $event = Gamification::getInstance()->getEvent($_GET['id']);
            if ($action == null)
                self::eventList();
            if (($action == 'add-new' || $action == 'edit'))
                self::addEvent($event);
        }

        if ($action=='import')
            self::import();
        if ($action=='addEventoPessoa')
            self::addEventoPessoa();
        if ($action=='addBadgePessoa')
            self::addBadgePessoa();



        // Listar usuários
        // Acessar log, eventos
        // Listar por badges e tal

        self::showActionsMigration();
    }

    // Get users ranking
    private static function showLevelRanking(){
        $ranking = Gamification::getInstance()->getUsersPointsRanking(300);
        require_once PLUGINPATH . '/view/gamification/list_points_ranking.php';
        ListTablePointsRanking($ranking);
    }

    private static function showActionsMigration()
    {
//        ?>
<!--            <a href="admin.php?page=Gamification&action=import" class="add-new-h2">Importar Pontos</a>-->
<!--        --><?php
    }

    public static function import()
    {
        die("Ops! Apagar tudo?");
        try{
            $gamification = Gamification::getInstance();

            echo "<H1>Importando dados...</H1>";
            // Resetar Gamification
            $gamification->clearAllData();

            // Modelo GDG
            // Obter todos usuários

            // Obter eventos de 1 de agosto em diante
            $eventos = Eventos::getInstance()->getPassados();
            foreach ($eventos as $evento) {
                if (strtotime($evento->data) <strtotime('2015-08-01')) continue;

                // Obter todas presenças - pontuar
                echo "Evento $evento->id - $evento->titulo - $evento->data - ";

                $inscricoes = Inscricoes::getInstance()->getPresentes($evento->id);
                echo count($inscricoes)." presentes <br>";
                foreach($inscricoes as $inscricao){
//                    if ($inscricao->id_pessoa!=23) continue;
                    /* @var @inscricao Inscricao */
                    $gamification->setUserId($inscricao->id_pessoa);
                    $dino=false;

                    $inicio = $evento->data . ($evento->hora? " " . $evento->hora:'');

                    // Clean Code
                    if ($evento->id==1065){
//                        var_dump("dino?");
                        // Dino?
                        if ($inscricao->pessoa()->getCountConfirmadoAntesCleanCode()>0){
                            $gamification->executeEvent('before_gamification',$inscricao,$inicio);
                            $dino=true;
                        }
                    }

                    // Primeira presença?
                    if (!$dino && $inscricao->pessoa()->getCountPresentes()==1 && $inscricao->pessoa()->getCountConfirmados()<=2)
                        $gamification->executeEvent('first_presence',$inscricao,$inicio);
                    else
                        // Só esteve presente, pontuar
                        $gamification->executeEvent('event_presence',$inscricao,$inicio);

                    // Se respondeu pesquisa, pontuar
                    if ($inscricao->hasAvaliacao())
                        $gamification->executeEvent('event_feedback',$inscricao,$inscricao->getAvaliacaoResposta(1)->dat_resposta);
                }
            }

            // The End
            echo "<br>Gamification points fully updated!<br>";
            self::showLevelRanking();

        } catch (Exception $e){
            echo "Exception: ".$e->getMessage();
        }
    }

    private static function addEventoPessoa()
    {
        // Postando?
        if (count($_POST) > 0) {
            $pessoa = Pessoas::getInstance()->getById($_POST['id_pessoa']);
            $gamification = Gamification::getInstance();
            $gamification->setUserId($_POST['id_pessoa']);
            $gamification->executeEvent($_POST['alias'],$pessoa);

            ControllerPessoas::view($pessoa);
        } else {
            require_once PLUGINPATH . '/view/gamification/form_evento_pessoa.php';
        }
    }


    public static function badgeList(){
        $badges = Gamification::getInstance()->getBadges();
        require_once PLUGINPATH . '/view/gamification/list_badges.php';
        ListTableBadges($badges,'Badges');
    }

    private static function addBadge($badge=null)
    {
        // Postando?
        if (count($_POST) > 0) {
            if ($_POST['id']) {
                // Update??
                die("Update não implementado");
            } else
                $badge = Gamification::getInstance()->addBadge($_POST['alias'], $_POST['title'],$_POST['description'], '');
            if ($badge && $_FILES['arquivo']) {
                $name = strtolower($_FILES['arquivo']['name']);
                $name = str_replace(' ', '_', $name);
                if (strpos($name, '.png') === false)
                    echo ("O arquivo deve ser .PNG");
                $file = get_template_directory().'/img/gamification/' . $badge->getAlias() . '.png';
                if (file_exists($file))
                    unlink($file);
                move_uploaded_file($_FILES['arquivo']['tmp_name'], $file);
            }

            ControllerGamification::badgeList();
        } else {
            require_once PLUGINPATH . '/view/gamification/form_badge.php';
        }
    }



    public static function eventList(){
        $events = Gamification::getInstance()->getEvents();
        require_once PLUGINPATH . '/view/gamification/list_events.php';
        ListTableEvents($events,'Eventos');
    }

    private static function addEvent($event=null)
    {
        // Postando?
        if (count($_POST) > 0) {
            if ($_POST['id']) {
                // Update??
                die("Update não implementado");
            } else {
                $event = new Event();
                $event->setAlias($_POST['alias']);
                $event->setDescription($_POST['description']);
                $event->setAllowRepetitions($_POST['allow_repetitions']==1);
                if ($_POST['reach_required_repetitions'])
                    $event->setReachRequiredRepetitions($_POST['reach_required_repetitions']);
                if ($_POST['id_each_badge']){
                    $badge = Gamification::getInstance()->getBadge($_POST['id_each_badge']);
                    $event->setEachBadgeGranted($badge);
                }
                if ($_POST['id_reach_badge']){
                    $badge = Gamification::getInstance()->getBadge($_POST['id_reach_badge']);
                    $event->setReachBadgeGranted($badge);
                }
                if ($_POST['each_points'])
                    $event->setEachPointsGranted($_POST['each_points']);
                if ($_POST['reach_points'])
                    $event->setReachPointsGranted($_POST['reach_points']);
                $event = Gamification::getInstance()->addEvent($event);
            }
            ControllerGamification::eventList();
        } else {
            require_once PLUGINPATH . '/view/gamification/form_event.php';
        }
    }
}