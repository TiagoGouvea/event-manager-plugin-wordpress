<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 30/10/15
 * Time: 12:14
 */

namespace lib;

use Inscricao;
use TiagoGouvea\PHPRdStation\RDStationAPI;

class RdStation
{
    public static function update($subject, $action)
    {
        ///
        /* @var $inscricao Inscricao */
        $inscricao = $subject;
        if (!($subject instanceof Inscricao)) {
            //throw new Exception("tipo não suportado em RdStation::update: "+get_class($subject));
        }
        $pessoa = $inscricao->pessoa();
//        var_dump($pessoa);

        $rd = new RDStationAPI(TGO_EVENTO_RDSTATION_PRIVATE_TOKEN,TGO_EVENTO_RDSTATION_TOKEN);

        $sucesso=false;

        // Criar identificador
        if ($action==\Enum::INSCRICAO_REALIZADA) {
            $data = array(
                'nome' => $pessoa->nome,
                'celular' => $pessoa->celular,
                'identificador'=> "Inscrição realizada em: " . $inscricao->evento()->titulo,
            );

            $tags = $inscricao->evento()->getTags(true);
            if ($tags)
                $data['tags']=join(',',$tags);

            // Demais dados
            if ($inscricao->c_utmz)
                $data['c_utmz']=$inscricao->c_utmz;
            if ($pessoa->end_cidade!=null){
                $data['end_cidade']=$pessoa->end_cidade;
                $data['end_estado']=$pessoa->end_estado;
            }
            $sucesso = $rd->sendNewLead($pessoa->email, $data);
            if ($sucesso)
                $sucesso = $rd->updateLeadStageAndOpportunity($pessoa->email, 1, true);
        }
        else if ($action==\Enum::INSCRICAO_CONFIRMADA) {
            $sucesso = $rd->updateLeadStatus($pessoa->email, 'won', $inscricao->valor_pago);
        }
        else if ($action==\Enum::INSCRICAO_CANCELADA) {
            $sucesso = $rd->updateLeadStatus($pessoa->email, 'lost', $inscricao->valor_pago, 'Inscrição cancelada');
        }

        if (!$sucesso){
            echo "Falha em RD - action: ".$action;
            //die("Falha em RD");
            throw new \Exception("Falha em RD com action: ".$action);
        } else {
            //echo "Sucesso em RD";
        }
    }

    public static function InsertLead($nome,$email,$celular=null,$identificador=null,$mensagem=null,$tags=null){
        $rd = new RDStationAPI('d657348413ac7c9bfe0126456480ad7a', 'fb22ee9387e2b3c6068cc3522d52b6f0');
        $sucesso=false;

        // Dados básicos
        $data = array(
            'nome' => $nome,
            'celular' => $celular,
            'identificador'=> $identificador,
            'mensagem'=>$mensagem,
            'tags'=>$tags
        );
        // Demais dados
        $sucesso = $rd->sendNewLead($email, $data);

        if (!$sucesso){
            echo "Falha em RD - action: ".$identificador;
            die("Falha em RD");
            throw new \Exception("Falha em RD com action: ".$identificador);
        } else {
            //echo "Sucesso em RD";
        }
    }
}