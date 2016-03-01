<?php
use lib\Gamification;

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 25/03/15
 * Time: 22:15
 */

class ControllerQuestionarios
{
    public static function dispatcher($action=null)
    {
        if ($action==null) $action = $_GET['action'];
//        $evento=null;
//        if (isset($_GET['id_evento']))
//            $id_evento = $_GET['id_evento'];
//        if (isset($_POST['id_evento']))
//            $id_evento = $_POST['id_evento'];

        if ($action == 'responder')
            return self::showForm($action);
    }

    private static function showForm($action)
    {

        // Postando?
        if (count($_POST) > 0) {
            /* @var $inscricao Inscricao */
            $inscricao = Inscricoes::getInstance()->getById(get_query_var('avaliacao')/13);
            /* @var $evento Evento */
            $questionario = $inscricao->evento()->getQuestionarioAvaliacao();
            $perguntas = $questionario->getPerguntas();
            $jaRespondeu = $inscricao->hasAvaliacaoResposta(1);

            $mensagem=null;

            foreach($perguntas as $pergunta){
                $resp = trim($_POST['input_'.$pergunta->id]);
//                var_dump($resp);
                if ($pergunta->obrigatoria && $resp==''){
                    setFlashError("Por favor, responda as perguntas obrigatórias (marcadas com *)");
                    break;
                }
                if ($resp!=''){
                    $resposta = Respostas::getInstance()->createResposta($pergunta,$inscricao,$resp);
                    $mensagem.=$pergunta->titulo.': '.$resp.'<br>';
                }
            }

            if (!hasFlashError()){
                setFlash("sucesso");
                // Enviar email com respostas
//                $mensagem="Respostas:<br><br>".$mensagem;
//                $inscricao->evento()->organizador()->enviarEmail(
//                    $inscricao->evento()->organizador()->email,
//                    "Resposta - ".$questionario->titulo." - ".$inscricao->evento()->titulo." - ". $inscricao->pessoa()->nome,
//                    $mensagem
//                );
                // Creditar o gamification
                if (TGO_EVENTO_GAMIFICATION===true && !$jaRespondeu) {
                    Gamification::getInstance()->broadcast('event_feedback', $inscricao->id_pessoa,$inscricao);
                }
            }

        } else {
            // Obter inscrição
            $inscricao = Inscricoes::getInstance()->getById(get_query_var('avaliacao')/13);
            if ($inscricao==null)
                die("Inscrição não localizada");
            // Validar inscrição
            if ($inscricao->confirmado!='1')
                die("Inscrição não confirmada");
        }

        return 'avaliacao.php';
    }
}