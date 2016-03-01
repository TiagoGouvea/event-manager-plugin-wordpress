<?php

use TiagoGouvea\PLib;
use TiagoGouvea\WPDataMapper\WPSimpleMapper;
use TiagoGouvea\WPUtil;

class Organizador extends WPSimpleMapper {
    public $id;
    public $titulo;
    public $titulo_menor;
    public $email;
    public $descricao;
    public $publico_alvo;
    public $site;
    public $telefone;
    public $periodicidade;
    public $ativo;
    public $senha;

    public $slug;

    public $pagseguro_email;
    public $pagseguro_token;
    public $sms_hoje;
    public $email_semvagas;
    public $email_confirmacao;
    public $email_confirme_agora;
    public $inscricao_dados_conta;
    public $inscricao_locais_pagamento;

    /**
     * Envia um email através deste organizador
     * @param $para
     * @param $assunto
     * @param $mensagem
     * @return bool
     */
    function enviarEmail($para, $assunto, $mensagem){
        $mensagem=$mensagem."<br><br>".$this->titulo;
        if (DB_NAME=='emjf_wpjf' || DB_NAME=='emjf'){
            $enviado=true;
        } else if (WP_DEBUG == true){
            echo "<br>enviarEmail<br><b>Assunto:</b>$assunto<br><b>Mensagem:</b><br>$mensagem<br>";
            $enviado= true;
        } else {
            $headers = array('From: '.$this->titulo.' <'.$this->email.'>');
            WPUtil::mailSetup($headers);
            $enviado = WPUtil::mailSend($para, $assunto, $mensagem);
            // Enviar uma cópia de tudo para endereços
            if (TGO_EVENTO_CCO!=null)
                $enviado = WPUtil::mailSend(TGO_EVENTO_CCO,'[Cópia] '. $assunto, $mensagem);
        }
        return $enviado;
    }

    public function enviarSms($celular,$mensagem)
    {
        $from = "#gdgjf";
        require_once PLUGINPATH . '/vendor/human_gateway_client_api/HumanClientMain.php';
        $humanSimpleSend = new HumanSimpleSend(TGO_EVENTO_HUMAN_ACCOUNT, TGO_EVENTO_HUMAN_TOKEN);
        // Certificar que pessoa e celular estejam ok
        $celular = PLib::only_numbers($celular);
        if ($celular == null || $celular == '' || strlen($celular)<8){
            return false;
        }
        $celular = substr($celular,-10);
        if (strlen($celular)==8) $celular='32'.$celular;
        $celular = '55' . $celular;

//        $celular="553288735683";

        // Criar objeto e enviar
        $message = new HumanSimpleMessage($mensagem, $celular, $from);
        try{
            $status = $humanSimpleSend->sendMessage($message);
        } catch (Exception $e) {
            echo "<br>Erro no envio - Exception<br>";
        }

        if ($status->getCode()=="000"){
            return true;
        } else {
            echo "<br>Erro: $pessoa->nome - $celular - code:". $status->getCode() ." - message:". $status->getMessage() . "<br>";
            return false;
        }
    }
}









