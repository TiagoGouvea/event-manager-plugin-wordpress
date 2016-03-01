<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 13/06/15
 * Time: 11:26
 */

namespace lib;

use Exception;
use Inscricao;
use Organizador;
use Organizadores;
use Pessoa;
use TiagoGouvea\PHPGamification;
use TiagoGouvea\PHPGamification\DAO;
use TiagoGouvea\PHPGamification\Model\Badge;
use TiagoGouvea\PHPGamification\Model\Event;
use TiagoGouvea\PHPGamification\Model\UserScore;
use TiagoGouvea\PLib;

class Gamification
{

    private static $instance;
    private static $gamification;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new Gamification();
        return self::$instance;
    }

    public function __construct()
    {
        if (self::$gamification!=null) return;

        // Creation of gamification engine
        $gamification = new PHPGamification();
        $gamification->setDAO(new DAO(GM_HOST, GM_DBNAME, GM_USERNAME, GM_PASSWORD));
        self::$gamification = $gamification;
    }

    /**
     * @param $limit
     * @return UserScore[]
     */
    public function getUsersPointsRanking($limit)
    {
        return self::$gamification->getUsersPointsRanking($limit);
    }

    public function getLevel($id)
    {
        return self::$gamification->getLevel($id);
    }

    public function clearAllData()
    {
        $gamification = self::$gamification;

        // Reset all data
        $gamification->truncateDatabase(true);

        /**
         * Configurações do GDG como no Drive:
         */

        // Badges definitions
        $noogler = $gamification->addBadge('noogler', 'GDG Noogler', '');
        $friend = $gamification->addBadge('friend', 'GDG Friend', '');
        $pequenoGafanhoto = $gamification->addBadge('pequeno_gafanhoto', 'Pequeno Gafanhoto', '');
        $dino = $gamification->addBadge('dino', 'Dinossauro do GDGJF', '');

        $gamification->addBadge('king_of_chat', 'King of the Chat', 'You posted 10 messages to the chat (500 points)', 'img/badge2.png');
        $gamification->addBadge('spreader', 'Blog Spreader', 'You wrote 5 post to your blog (1000 points)', 'img/badge3.png');
        $gamification->addBadge('five_stars_badge', 'Five Stars', 'You get the Five Stars level', 'img/badge4.png');

        // Niveis
        $gamification->addLevel(0, 'Hello World');
//        $gamification->addLevel(1000, 'Five stars', 'grant_five_stars_badge'); // Execute event: grant_five_stars_badge


        // Primeira vez no GDG - 10 pontos - badge noogler
        $event = new Event();
        $event->setAlias('first_presence')
            ->setDescription('Primeira presença no GDGJF')
            ->setEachPointsGranted(10)
            ->setAllowRepetitions(false)
            ->setEachCallback('\lib\Gamification::CallbackFirstPresence')
            ->setEachBadgeGranted($noogler);
        $gamification->addEvent($event);

        // Já tinha ido no GDG antes vez no GDG - 15 pontos - badge noogler
        $event = new Event();
        $event->setAlias('before_gamification')
            ->setDescription('Já ia ao GDG antes da Era do Gamification, é um dinosauro praticamente!')
            ->setEachPointsGranted(15)
            ->setAllowRepetitions(false)
            ->setEachCallback('\lib\Gamification::CallbackBeforeGamification')
            ->setEachBadgeGranted($dino);
        $gamification->addEvent($event);

        // Estar presente em um evento do GDG - 10 pontos - (5x) badge friend
        $event = new Event();
        $event->setAlias('event_presence')
            ->setDescription('Esteve presente ao evento')
            ->setEachPointsGranted(10)
            ->setEachCallback('\lib\Gamification::CallbackEventPresence')
            ->setAllowRepetitions(true);
//            ->setReachRequiredRepetitions(5)
//            ->setEachBadgeGranted($friend);
        $gamification->addEvent($event);

        // Dar Feedback de evento - 5 points
        $event = new Event();
        $event
            ->setAlias('event_feedback')
            ->setDescription('Respondeu o formulário de feedback do evento')
            ->setEachPointsGranted(5)
            ->setAllowRepetitions(true)
            ->setEachCallback('\lib\Gamification::CallbackEventFeedback');
        $gamification->addEvent($event);

        // Ir ao hamburger após o evento - 5 points
        $event = new Event();
        $event->setAlias('lets_hamburger')
            ->setDescription('Foi ao hamburger com a galera do GDG')
            ->setEachPointsGranted(5)
            ->setAllowRepetitions(true);
        $gamification->addEvent($event);

        // Escrever post pro blog - 20 pontos - (4x) badge writer
        $event = new Event();
        $event->setAlias('blog_post')
            ->setDescription('Escreveu um post para o Blog')
            ->setEachPointsGranted(20)
            ->setAllowRepetitions(true);
//            ->setEachCallback('\lib\Gamification::CallbackBlogPost')
//            ->setReachRequiredRepetitions(5)
//            ->setEachBadgeGranted($friend);
        $gamification->addEvent($event);

        // Apresentar conteúdo - 10 pontos - (1x) badge writer - 4x badge
        $event = new Event();
        $event->setAlias('event_present')
            ->setDescription('Apresentou conteúdo no evento')
            ->setEachPointsGranted(10)
            ->setAllowRepetitions(true)
            ->setEachCallback('\lib\Gamification::CallbackEventPresent')
            ->setReachRequiredRepetitions(1)
            ->setEachBadgeGranted($pequenoGafanhoto);
        $gamification->addEvent($event);

        // Auxiliar na organização - 10 pontos - (1x) badge  - 4x badge
        $event = new Event();
        $event->setAlias('event_support')
            ->setDescription('Auxiliou na organização de evento')
            ->setEachPointsGranted(10)
            ->setAllowRepetitions(true)
            ->setEachCallback('\lib\Gamification::CallbackEventSupport');
        $gamification->addEvent($event);





        echo "<br><B>Dados apagados!!!</B><Br>";
    }

    public function executeEvent($string, $additional = null,$eventDate=null)
    {
        return self::$gamification->executeEvent($string, $additional,$eventDate);
    }

    public function setUserId($userId)
    {
        return self::$gamification->setUserId($userId);
    }

    public function getEventosArray()
    {
        $eventos = self::$gamification->getEvents();
        $array = array();
        foreach ($eventos as $evento)
            $array[$evento->getAlias()] = $evento->getAlias() . '- ' . $evento->getDescription();
        return $array;

    }

    public function addUserEvent(PHPGamification\UserEvent $userEvent)
    {
        self::$gamification->addUserEvent($userEvent);
    }


    /**
     * Avisa que algo aconteceu, normalmente para disparar um evento
     * @param $alias
     * @param $id_user
     * @throws Exception
     */
    public function broadcast($alias, $id_user, $param = null)
    {
//        var_dump($alias);
//        var_dump($id_user);
        self::$gamification->setUserId($id_user);
        self::$gamification->executeEvent($alias,$param);
    }

    public static function CallbackFirstPresence(Inscricao $inscricao)
    {
        // Evento terminou no passado?
        if ($inscricao->evento()->fim()<time()) return true;
        var_dump($inscricao->evento()->fim());
        var_dump(time());
        die("aaa216");

        // Enviar email agradecendo e tal
//        var_dump($inscricao->evento()); return true;

        $assunto = "Bem vindo ao GDGJF!";
        $mensagem = "Olá %pessoa_primeiro_nome%!<br><br>

        Que bom que você veio se juntar a nós!<br><br>

        O <a href='http://emjuizdefora.com/gdgjf/'>Google Developers Group Juiz de Fora</a> realiza eventos sempre em Juiz de Fora, além de apoiar eventos de outros grupos e instituições.<br><br>

        Vamos te manter por dentro de tudo. Para todos os próximos eventos que realizamos, você receberá um email com o link para inscrição.<br><br>

        A partir de agora você também está participando de nosso <a href='http://emjuizdefora.com/gdgjf/gamification/'>Gamification</a>, e até já ganhou alguns
        pontos e uma medalha; \"Noogler\"! Se desejar consulte o <a href='http://emjuizdefora.com/gdgjf/gamification/'>ranking</a> do Gamification em nosso site.<br><br>

        Pra finalizar, siga-nos nas no <a href='https://plus.google.com/101567513255448590336/posts'>Google Plus</a> e participe dos nossos grupos no <a href='https://www.facebook.com/GoogleDevelopersGroupJuizDeFora'>Facebook</a>.<br><br>

        Valeu!
        ";
        $mensagem = \Mensagens::getInstance()->substituirVariaveis($mensagem,$inscricao->evento(),$inscricao->pessoa(),$inscricao);

        $inscricao->evento()->organizador()->enviarEmail(
            $inscricao->pessoa()->email,
            $assunto,
            $mensagem);

        return true;
    }

    public static function CallbackEventPresence(Inscricao $inscricao)
    {
        // Evento terminou no passado? (importação)
//        var_dump($inscricao);
        return true;
        if ($inscricao->evento()->fim()<time()) return true;

        // Enviar email agradecendo e tal
//        var_dump($inscricao->evento()); return true;

        // O que acontece quando a pessoa está presente?
        $assunto = "Teste de email de presença";
        $mensagem = "Olá %pessoa_primeiro_nome%!<br><br>

        Presença (de teste) confirmada!<br><br>

        Valeu!
        ";
        $mensagem = \Mensagens::getInstance()->substituirVariaveis($mensagem,$inscricao->evento(),$inscricao->pessoa(),$inscricao);

        $inscricao->evento()->organizador()->enviarEmail(
            $inscricao->pessoa()->email,
            $assunto,
            $mensagem);

        return true;
    }

    public static function CallbackBlogPost(Pessoa $pessoa)
    {
        // ?
        return true;
    }

    public static function CallbackEventPresent(Pessoa $pessoa)
    {
        $assunto = "Muito bom hein!";
        $mensagem = "Olá %pessoa_primeiro_nome%!<br><br>

        Graças a pessoas como você que nossa comunidade continua sempre crescendo!<br><br>

        Ficamos muito agradecidos com sua participação apresentando conteúdos, ok?<br><br>

        A partir de agora, você irá ganhar pontos de acordo com o feedback das pessoas que assistirem sua apresentação. Se votarem notas altas, você ganha mais pontos, caso contrário, não ganhará quase nada.<br><br>

        Você também já ganhou uma badge \"Estreiou no microfone\"! :) <br><br>

        Boa sorte e manda bala!<br><br>

        Valeu!
        ";
        $mensagem = \Mensagens::getInstance()->substituirVariaveis($mensagem,null,$pessoa,null);

        /* @var $organizadores Organizador[] */
        $organizadores = Organizadores::getInstance()->getAll();
        $oganizador=$organizadores[0];
        $oganizador->enviarEmail(
            $pessoa->email,
            $assunto,
            $mensagem);

        return true;
    }

    public static function CallbackEventSupport(Pessoa $pessoa)
    {
        // ?
        return true;
    }

    public static function CallbackEventFeedback(Inscricao $inscricao)
    {
        // ?
//        echo ("CallbackEventFeedback<br>");
        return true;
    }

    public static function CallbackBeforeGamification()
    {
        // ?
//        echo ("CallbackBeforeGamification<br>");
        return true;
    }


    public function getUserScore()
    {
        $score = self::$gamification->getUserScores();
        return $score;
    }

    public function getUserBadges()
    {
        $badges = self::$gamification->getUserBadges();
        return $badges;
    }


    public function showUser()
    {
        echo "<h2>getUserAlerts</h2>";
        $alerts = self::$gamification->getUserAlerts();
        if ($alerts == null) {
            echo "No level or badge alerts to show<br>";
        } else {
            foreach ($alerts as $alert) {
                echo "Id Level: " . $alert->getIdLevel() . " - Id Badge: " . $alert->getIdBadge() . "<br>";
            }
        }

    }

    public function showUserScores()
    {
        echo "<h4>Pontuação, Nível e Progresso</h4>";
        $score = self::$gamification->getUserScores();
//        var_dump($score);die();
        echo "Pontos: " . $score->getPoints() . " - Nível: " . $score->getIdLevel() . " - " . $score->getLevel()->getTitle() . " - Progresso: " . $score->getProgress() . "%<br>";
        $badges = self::$gamification->getUserBadges();
        if (count($badges)) {
            echo "<h4>Badges</h4>";
            foreach ($badges as $badge) {
                $theBadge = self::$gamification->getBadge($badge->getIdBadge());
                //echo "Badge Id: " . $badge->getIdBadge() . " -  Counter: " . $badge->getBadgeCounter() . " - Alias: " . $theBadge->getTitle() . " - Description: ".$theBadge->getDescription()." <br>";
                echo "Alias: " . $theBadge->getTitle() . " - Description: ".$theBadge->getDescription()." <br>";
            }
        }
    }

    public function showUserEvents()
    {
        $events = self::$gamification->getUserEvents();
        if ($events) {
            echo "<h4>Eventos</h4>";
            foreach ($events as $event) {
                //        var_dump($event);
                echo $event['event']->getAlias() . " - " . $event['event']->getDescription() . " - Counter: $event[counter] - Points: " . $event['userEvent']->getPointsCounter() . "<br>";
                //            foreach ($event['triggers'] as $k => $trigger)
                //                echo " &nbsp;  &nbsp; Trigger: $k - Reached: " . ($trigger['reached'] ? "true" : "false") . "<br>";
            }
        }
    }

    public function showUserLog(){

        $logs = self::$gamification->getUserLog();
        if ($logs) {
            echo "<h4>Log</h4>";
            echo "<table><Tr><th>Data</th><th>Alias</th><th>Evento</th><th>Pontuou</th><th>Badge</th><th>Novo Nível</th></Tr>";
            foreach ($logs as $log) {
                $event = self::$gamification->getEventById($log->getIdEvent());
                if ($log->getIdBadge())
                    $badge = self::$gamification->getBadge($log->getIdBadge())->getTitle();
                else
                    $badge = null;
                echo "<tr><td>" . PLib::date_relative($log->getEventDate()) . "</td><td>" . $event->getAlias() . " </td><td>" . $event->getDescription() . "</td><td>" . $log->getPoints() . "</td><td> " . ($badge) . "</td><td>" . $log->getIdLevel() . "</td></tr>";
            }
            echo "</table>";
        }
    }

    /**
     * @param $id
     * @return Badge
     * @throws Exception
     */
    public function getBadge($id)
    {
        self::$gamification->startEngine();
        return self::$gamification->getBadge($id);
    }

    public function getBadges()
    {
        return self::$gamification->getBadges();
    }

    public function getBadgesArray()
    {
        $badges = self::$gamification->getBadges();
        $array = array();
        foreach ($badges as $badge)
            $array[$badge->getId()] = $badge->getAlias(). ' - ' . $badge->getDescription();
        return $array;

    }

    public function addBadge($alias,$title,$description)
    {
        return self::$gamification->addBadge($alias,$title,$description);
    }



    public function getEvents()
    {
        return self::$gamification->getEvents();
    }

    public function getEvent($id)
    {
        self::$gamification->startEngine();
        return self::$gamification->getEventById($id);
    }

    public function addEvent($event)
    {
        return self::$gamification->addEvent($event);
    }


}