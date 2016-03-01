<?php

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 29/04/15
 * Time: 11:03
 *
 * Executa as tarefas necessárias para o momento
 *
 * Como incluir no crontab
 *
 */
class ControllerCrontab
{
    public static function dispatcher()
    {

    }

    /**
     *  http://url/crontab/dia/
     */
    public static function dia()
    {
        require 'wp-includes/pluggable.php';
        // Eventos futuros
        $eventos = Eventos::getInstance()->getFuturos();
        /* @var $evento Evento */
        foreach ($eventos as $evento) {

            // Apenas eventos pagos
            if ($evento->pago != 'pago' || $evento->preInscricao()) continue;

            echo "<h1>$evento->titulo</h1>";

            // Notificar "confirme agora!" - Boleto emitido a 3 dias corridos, não pago
//            $inscricoes = Inscricoes::getInstance()->getConfirmeAgora($evento->id);
//            if (count($inscricoes) > 0) {
//                /* @var $inscricao Inscricao */
//                foreach ($inscricoes as $inscricao) {
//                    $inscricao->confirmeAgora();
//                    // !!! REGISTRAR QUE ENVIEI ESTE EMAIL na inscrição
//                }
//            }

            // Notificar inscrições vencidas por dia
            $inscricoesVencer = Inscricoes::getInstance()->getVencerDia($evento->id);
//            \TiagoGouvea\PLib::var_dump($inscricoesVencer);
            if (count($inscricoesVencer) > 0) {
                /* @var $inscricao Inscricao */
                foreach ($inscricoesVencer as $inscricao) {
                    if ($inscricao->pre_inscricao) continue;
                    echo "Vencer " . $inscricao->id . "<br>";
                    $inscricao->vencer();
                }
            }
        }
        // Verificar se é preciso desativar o pagamento por boleto (x dias antes do evento)

        // Se chegou o momento do evento, as inscrições que não se confirmaram....... devem?
    }


    /**
     *  http://url/crontab/hora/
     */
    public static function hora()
    {
        require 'wp-includes/pluggable.php';
        // Eventos futuros
        $eventos = Eventos::getInstance()->getFuturos();
        /* @var $evento Evento */
        foreach ($eventos as $evento) {

            // Apenas eventos pagos
            if ($evento->pago != 'pago' || $evento->preInscricao()) continue;

            echo "<h1>$evento->titulo</h1>";

            // Notificar inscrições ainda não pagas
            $inscricoesVencer = Inscricoes::getInstance()->getPagueAgora($evento->id);
//            var_dump($inscricoesVencer);
//            \TiagoGouvea\PLib::var_dump($inscricoesVencer);
            if (count($inscricoesVencer) > 0) {
                /* @var $inscricao Inscricao */
                foreach ($inscricoesVencer as $inscricao) {
                    if ($inscricao->pre_inscricao) continue;
                    echo "Pedir que confirme agora " . $inscricao->id . "<br>";
                    $inscricao->confirmeAgora();
                }
            }

            // Notificar inscrições vencidas por hora
            $inscricoesVencer = Inscricoes::getInstance()->getVencerHora($evento->id);
//            var_dump($inscricoesVencer);
//            \TiagoGouvea\PLib::var_dump($inscricoesVencer);
            if (count($inscricoesVencer) > 0) {
                /* @var $inscricao Inscricao */
                foreach ($inscricoesVencer as $inscricao) {
                    if ($inscricao->pre_inscricao) continue;
                    echo "Vencer " . $inscricao->id . "<br>";
                    $inscricao->vencer();
                }
            }
        }
    }
} 