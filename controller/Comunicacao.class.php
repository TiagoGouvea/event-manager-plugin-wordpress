<?php

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 25/03/15
 * Time: 22:15
 */
class ControllerComunicacao
{
    public static function dispatcher()
    {
        $action = $_GET['action'];
        $filter = $_GET['filter'];

        $evento = null;
        if (isset($_GET['id_evento']))
            $id_evento = $_GET['id_evento'];
        if (isset($_POST['id_evento']))
            $id_evento = $_POST['id_evento'];

        if ($id_evento != null)
            $evento = Eventos::getInstance()->getById($id_evento);

        if ($action == 'email')
            self::showForm($action, $filter, $evento);

        if ($action == 'sms')
            self::showFormSms($filter, $evento);
    }


    private static function showForm($action, $filter, Evento $evento)
    {
        // Postando?

        if (count($_POST) > 0 && $_POST['preview'] != 1) {
            $assunto = $_POST['assunto'];
            $mensagem = nl2br($_POST['mensagem']);

//            var_dump($filter);
            // Enviar emails
            $inscritos = Inscricoes::getInstance()->getByFilterString($evento, $filter);
            if ($filter == 'naoConfirmados' || $filter == 'rejeitados' || $filter == 'preInscritos' || $filter=='confirmadosFalse') {
                // Obter apenas inscritos que não tenham OUTRA(s) inscrições confirmadas
                if ($inscritos) {
                    $nInscritos = array();
                    /* @var $inscrito Inscricao */
                    foreach ($inscritos as $inscrito) {
                        if (Inscricoes::getInstance()->getByEventoPessoaConfirmado($inscrito->id_evento, $inscrito->id_pessoa) != null)
                            continue;
                        $nInscritos[] = $inscrito;
                    }
                    $inscritos = $nInscritos;
                }
            }

//            var_dump($inscritos);die();

            $subTitulo = $filter;

            echo "<h2>Envio de emails para $subTitulo</h2><br>";
            $enviados = 0;
            foreach ($inscritos as $inscrito) {
                /* @var $inscrito Inscricao */
                $mensagemEnviar = Mensagens::getInstance()->substituirVariaveis($mensagem, $evento, $inscrito->pessoa(), $inscrito);
                $mensagemEnviar = stripslashes($mensagemEnviar);

                $assuntoEnviar = Mensagens::getInstance()->substituirVariaveis($_POST['assunto'], $evento, $inscrito->pessoa(), $inscrito);
                $assuntoEnviar = stripslashes($assuntoEnviar);

//              $evento->organizador()->enviarEmail($evento->organizador()->email,$assunto,$mensagemEnviar);
//              die();

                $enviado = $evento->organizador()->enviarEmail($inscrito->pessoa()->email, $assuntoEnviar, $mensagemEnviar);

                if ($enviado) {
                    echo "Email enviado para " . $inscrito->pessoa()->nome . ' (' . $inscrito->pessoa()->email . ')<Br>';
                    $enviados++;
                } else {
                    echo "Erro ao enviar email para " . $inscrito->pessoa()->nome . ' (' . $inscrito->pessoa()->email . ')<Br>';
                }
            }
            echo "<br><br>Email enviado para $enviados pessoas.<br><br>";

            // Enviar uma cópia para organizador
            $evento->organizador()->enviarEmail($evento->organizador()->email, 'Cópia - ' . $assuntoEnviar, $mensagemEnviar);

            echo "Uma <b>cópia do email</b> enviado para \"" . $inscrito->pessoa()->nome . "\" foi enviado para " . $evento->organizador()->email . " com a finalidade de registro.";
        } else {
            $subTitulo = $filter;
            $titulo = "Email para $subTitulo no " . $evento->titulo;
            require_once PLUGINPATH . '/view/comunicacao/form_email.php';
        }
    }

    private static function showFormSms($filter, Evento $evento)
    {
        // Postando?
        if (count($_POST) > 0) {
            $mensagem = nl2br($_POST['mensagem']);

            // Enviar emails
            if ($filter == 'confirmados') {
                $inscritos = Inscricoes::getInstance()->getConfirmados($evento->id);
            } else if ($filter == 'naoConfirmados') {
                //$inscritos = Inscricao::obterPorEventoNaoConfirmados($evento->id);
            } else if ($filter == 'rejeitados') {
                //$inscritos = Inscricao::obterPorEventoRejeitados($evento->id);
            }

            $subTitulo = $filter;

            echo "<h2>Envio de SMS para $subTitulo</h2><br>";
            $enviados = 0;
            foreach ($inscritos as $inscrito) {
                /* @var $inscrito Inscricao */
                if ($inscrito->pessoa()->celular == null) continue;
                $mensagemEnviar = Mensagens::getInstance()->substituirVariaveis($mensagem, $evento, $inscrito->pessoa(), $inscrito);
                $mensagemEnviar = stripslashes($mensagemEnviar);

                $enviado = $evento->organizador()->enviarSms($inscrito->pessoa()->celular, $mensagemEnviar);

                if ($enviado) {
                    echo "SMS enviado para " . $inscrito->pessoa()->nome . ' (' . $inscrito->pessoa()->celular . ')<Br>';
                    $enviados++;
                } else {
                    echo "Erro ao enviar email SMS " . $inscrito->pessoa()->nome . ' (' . $inscrito->pessoa()->celular . ')<Br>';
                }
            }
            echo "<br><br>SMS enviado para $enviados pessoas.<br><br>";
        } else {
            $subTitulo = $filter;
            $titulo = "SMS para $subTitulo no " . $evento->titulo;
            require_once PLUGINPATH . '/view/comunicacao/form_sms.php';
        }
    }
}