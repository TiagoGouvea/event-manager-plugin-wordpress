<?php
use lib\Gamification;
use lib\PagSeguroUtil;
use TiagoGouvea\PLib;
use TiagoGouvea\WPDataMapper\WPSimpleMapper;

/**
 * Plugin Name: Inscricaos
 * Plugin URI: http://www.tiagogouvea.com.br
 * Description: Gerenciador de Inscricaos
 * Version: 00.00.01
 * Author: Tiago Gouvêa
 * Author URI: http://www.tiagogouvea.com.br
 * License: GPL2
 */
// Post types de Inscrições

class Inscricao extends WPSimpleMapper
{

    public $id;
    public $id_evento;
    public $id_pessoa;
    public $id_preco;
    public $id_categoria;
    public $data_inscricao;
    public $valor_inscricao;
    public $pre_inscricao;
    public $meio_pagamento;

    public $confirmado;
    public $data_confirmacao;
    public $valor_pago;
    public $taxa_cobranca;
    public $valor_liquido;
    public $forma_pagamento;
    public $data_pagamento;
    public $status_gateway;
    public $forma_pagamento_gateway;
    public $codigo_gateway;
    public $data_atualizacao_gateway;
    public $id_pessoa_confirmacao;

    public $vencido;
    public $data_vencido;
    public $data_cancelamento;

    public $id_situacao;
    public $_observacao;
    public $_status_gateway;
    public $presente;
    public $c_utmz;



    /**
     * @return Pessoa
     */
    public function pessoa()
    {
        return Pessoas::getInstance()->getById($this->id_pessoa);
    }

    /**
     * @return Pessoa
     */
    public function pessoa_confirmacao()
    {
        return Pessoas::getInstance()->getById($this->id_pessoa_confirmacao);
    }


    /**
     * @return Evento
     */
    public function evento()
    {
        return Eventos::getInstance()->getById($this->id_evento);
    }

    /**
     * @return Categoria
     */
    public function categoria()
    {
        return Categorias::getInstance()->getById($this->id_categoria);
    }

    /**
     * @return Preco
     */
    public function preco()
    {
        return Precos::getInstance()->getById($this->id_preco);
    }


    /**
     * Retorna a situação da inscrição em string para exibição amigável
     */
    public function getSituacaoString()
    {
        $return = null;

        // Fila de espera?
        if ($this->id_situacao == 10) {
            $return .= '<b>Fila Espera</b><br>';
        }
        // Presente? Confirmado?
        if ($this->presente == 1)
            $return .= '<b>Confirmada e Presente</b>';
        if ($this->presente != 1 && $this->confirmado == '1')
            $return .= '<b>Confirmada</b>';
        if ($this->confirmado == '1' && $this->data_confirmacao!=null) {
            $return .= '<br>' . PLib::date_relative($this->data_confirmacao, true) . '<br>';
        } else if ($this->confirmado === '0') {
            $return .= '<b>Cancelada</b><br>';
            if ($this->data_cancelamento != null)
                $return .= 'Cancelamento: ' . PLib::date_relative($this->data_cancelamento) . '<br>';
        } else if ($this->pre_inscricao == 1) {
            $return .= '<b>Pré-inscrição</b><br>';
        }
        if ($this->confirmado==null) {
            $return .= '<b>Não confirmada</b><br>';
            if ($this->meio_pagamento!=null)
                $return.='Meio de Pagamento: '.$this->meio_pagamento.'<br>';
        }


        // Algo de pagamento?
        if ($this->forma_pagamento)
            $return .= "Forma Pagamento: " . $this->forma_pagamento . '<br>';
        if ($this->id_pessoa_confirmacao && $this->valor_pago)
            $return .= "Pessoa Recebimento: " . $this->pessoa_confirmacao()->nome . '<br>';
        else if ($this->id_pessoa_confirmacao)
            $return .= "Pessoa Confirmação: " . $this->pessoa_confirmacao()->nome . '<br>';
        if ($this->forma_pagamento_gateway)
            $return .= "Forma Pgto Gateway: " . PagSeguroUtil::getFormaPagamentoTituloString($this->forma_pagamento_gateway) . '<br>';
        if ($this->status_gateway)
            $return .= "Status PagSeguro: " . PagSeguroUtil::getStatusTituloString($this->status_gateway) . '<br>';
        if (PLib::coalesce($this->valor_pago, 0) > 0)
            $return .= "Valor pago: " . PLib::format_cash($this->valor_pago) . '<br>';
        if ($this->valor_liquido !== $this->valor_pago)
            $return .= "Valor líquido: " . PLib::format_cash(PLib::coalesce($this->valor_liquido, $this->valor_inscricao)) . '<br>';
        if ($this->data_atualizacao_gateway)
            $return .= "Atualização: " . PLib::date_relative($this->data_atualizacao_gateway, true) . " (" . PLib::days_between_dates($this->data_atualizacao_gateway) . " dias)<br>";

        return $return;
    }

    /**
     * Retorna as ações para a inscrição
     * @param $ajax
     * @return null|string
     */
    public function getSituacaoAcoes($ajax)
    {
        $return = null;
        $actions = null;

        $evento = $this->evento();

        if ($ajax) {
            if (!$evento->realizado() && PLib::coalesce($this->confirmado, 0) == 0){
                if (PLib::coalesce($this->valor_inscricao,0)>0)
                    $actions = "
                    <a href='#' onclick='javascript:confirmarInscricao(" . $this->id . ", \"" . $this->valor_inscricao . "\",\"Dinheiro\");return false;'>Confirmar (dinheiro)</a><br>
                    <a href='#' onclick='javascript:confirmarInscricao(" . $this->id . ", \"" . $this->valor_inscricao . "\",\"Depósito\");return false;'>Confirmar (depósito)</a><br>";
                else
                    $actions = "
                    <a href='#' onclick='javascript:confirmarInscricao(" . $this->id . ");return false;'>Confirmar Inscrição</a><br>";
            }
            if ((!$evento->realizado() || $evento->acontecendo())) {
                $actions.="<a href='#' onclick='javascript:cancelarInscricao(" . $this->id . ");return false;'>Cancelar</a>";
            }
            if (!$evento->realizado() && $this->confirmado == 1 && $this->evento()->pago == 'pago' && PLib::coalesce($this->valor_pago, 0) == 0) {
                $actions .=
                    "<a href='#' onclick='javascript:informarValorPagoInscricao(" . $this->id . ",'" . $this->valor_inscricao . "','Dinheiro');return false;'>Informar Valor Pago</a>";
            };
            if (($evento->acontecendo() || $evento->realizado()) && $this->presente == null) {
                $actions .=
                    "<a href='#' onclick='javascript:marcarPresenca(" . $this->id . ");return false;'>Marca Presença</a>";
            }
            if ($evento->realizado() && $this->presente==1 && $evento->hasCertificadoArquivo()){
                $actions .=
                    "<a href='".$this->getLinkCertificado()."' target=_blank>Certificado</a>";
            }
        } else {
            if (!$evento->realizado() && PLib::coalesce($this->confirmado, 0) == 0) {
                $actions =
                    "<a href='?confirmarInscricao=$this->id'>Confirmar</a>";
            }
        }

        $return = "<div class='row-actions'>
                <span class='view'>" .
            $actions .
            "</span>
            </div>";

        return $return;
    }


    function confirmarPagamento($status, $data, $valorBruto, $valorLiquido, $taxa, $codigo, $dataGateway=null, $formaPagamento)
    {
        // Se confirmando pela primeira vez, enviar emails
        $confirmarInscricao = ($this->confirmado != '1');

        // Registrar status do gateway
        $this->registrarStatusGateway($dataGateway, $codigo, $status, $formaPagamento);

        $data = substr($data, 0, 10);
        $data = strtotime($data);
        $data = date('Y-m-d', $data);

        $this->data_pagamento = $data;
        $this->valor_pago = $valorBruto;
        $this->valor_liquido = $valorLiquido;
        $this->taxa_cobranca = $taxa;
        $this->forma_pagamento = 'Gateway';

        // Confirmar inscrição
        if ($confirmarInscricao)
            $this->confirmar($this->forma_pagamento);

        return $this;
    }

    /**
     * Registra na inscrição o status atual no gateway
     * @param $dataGateway
     * @param $codigo
     * @param $status
     * @param $formaPagamento
     * @return Inscricao
     */
    function registrarStatusGateway($dataGateway=null, $codigo, $status, $formaPagamento)
    {
        if ($dataGateway) {
            $dataGateway = substr($dataGateway, 0, 10);
            $dataGateway = strtotime($dataGateway);
            $dataGateway = date('Y-m-d', $dataGateway);
            $this->data_atualizacao_gateway = $dataGateway;
        }

        $this->status_gateway = $status;
        if ($formaPagamento != null)
            $this->forma_pagamento_gateway = $formaPagamento;
        $this->codigo_gateway = $codigo;



        Inscricoes::getInstance()->save($this->id, $this);
        return $this;
    }


    /**
     * Confirma a inscrição, envia email de confirmação, etc
     * @param string $formaPagamento
     * @param null $valorPago
     * @return Inscricao
     */
    public function confirmar($formaPagamento = null, $valorPago = null)
    {
        // Obter pessoa
        /* @var $pessoa Pessoa */
        $pessoa = $this->pessoa();
        $evento = $this->evento();
        /* @var $evento Evento */
        $organizador = $evento->organizador();

//        $mensagem = Mensagens::getInstance()->get("email_confirmacao_inscricao_qrcode", $evento, $pessoa, $this, true);
//        var_dump($mensagem);
//        die("äaa");

        // Se confirmando pela primeira vez, enviar emails
        $primeiraConfirmacao = ($this->confirmado != '1');

//        if ($this->data_confirmacao == null || strtotime($this->data_confirmacao)<strtotime($data_pagseguro))
        $this->data_confirmacao = date('Y-m-d H:i:s');
        $this->confirmado = 1;
        if ($evento->pago == 'pago' && $formaPagamento != null)
            $this->forma_pagamento = $formaPagamento;

        if ($this->valor_inscricao > 0) {
            if ($valorPago == null)
                $valorPago = $this->valor_inscricao;
            if ($valorPago)
                $this->valor_pago = $valorPago;
        }

        Inscricoes::getInstance()->save($this->id, $this);
        // Conferir lotes, ver se precisa "virar o lote atual"

        //var_dump($evento->organizador());
        if (($evento->noFuturo() || $evento->acontecendo()) && $primeiraConfirmacao) {
            // Email para inscrito
            $assunto = 'Confirmação de Inscrição - ' . $evento->titulo;
            if (TGO_EVENTO_QRCODE===true)
                $mensagem = Mensagens::getInstance()->get("email_confirmacao_inscricao_qrcode", $evento, $pessoa, $this, true);
            else
                $mensagem = Mensagens::getInstance()->get("email_confirmacao_inscricao", $evento, $pessoa, $this, true);
            $organizador->enviarEmail($pessoa->email, $assunto, $mensagem);

            // Email para organizador
            $assunto = 'Inscrição Confirmada - ' . $evento->titulo . ' - ' . $pessoa->nome;
            $mensagem = Mensagens::getInstance()->get("email_confirmacao_inscricao_organizador", $evento, $pessoa, $this, true);
            $organizador->enviarEmail($organizador->email, $assunto, $mensagem);
        }

        if ($primeiraConfirmacao)
            // Notificar observadores
            Inscricoes::getInstance()->notify($this,Enum::INSCRICAO_CONFIRMADA);


        return $this;
    }

    /**
     * Cancelar a inscrição, envia email de confirmação, etc
     * @param string $formaPagamento
     * @return Inscricao
     */
    public function cancelar($motivo = null)
    {
        // Obter pessoas
        $pessoa = $this->pessoa();
        $evento = $this->evento();
        $organizador = $evento->organizador();

        // Se cancelando pela primeira vez, enviar emails
        $primeiroCancelamento = ($this->confirmado !== '0');

        if ($this->data_cancelamento == null)
            $this->data_cancelamento = date('Y-m-d H:i:s');
        $this->confirmado = '0';
        Inscricoes::getInstance()->save($this->id, $this);

        if (($evento->noFuturo() || $evento->acontecendo()) && $primeiroCancelamento) {
            if ($motivo == "vagas") {
                $assunto = 'Não temos como confirmar sua inscrição...';
                $mensagem = Mensagens::getInstance()->get("email_cancelamento_inscricao_vagas", $evento, $pessoa, $this, true);
            } else {
                $assunto = 'Cancelamento de inscrição';
                $mensagem = Mensagens::getInstance()->get("email_cancelamento_inscricao_generica", $evento, $pessoa, $this, true);
            }
            $organizador->enviarEmail($pessoa->email, $assunto, $mensagem);
        }

        if ($primeiroCancelamento)
            // Notificar observadores
            Inscricoes::getInstance()->notify($this,Enum::INSCRICAO_CANCELADA);
    }

    /**
     * Cancela a inscrição por problemas de pagamento
     * @return bool
     */
    public function cancelarFalhaPagamento($desfeito = false)
    {
        // Obter pessoas
        $pessoa = $this->pessoa();
        $evento = $this->evento();
        $organizador = $evento->organizador();

        // Se cancelando pela primeira vez, enviar emails
        $primeiroCancelamento = ($this->confirmado !== '0');

        if ($this->data_cancelamento == null)
            $this->data_cancelamento = date('Y-m-d H:i:s');
        $this->confirmado = '0';
        Inscricoes::getInstance()->save($this->id, $this);

        if (($evento->noFuturo() || $evento->acontecendo() || ($desfeito && $evento->aconteceuEmCincoDias())) && $primeiroCancelamento) {
            // Email para inscrito
            if ($desfeito) {
                $assunto = 'Estorno de pagamento e inscrição cancelada - ' . $evento->titulo;
                $mensagem = Mensagens::getInstance()->get("email_cancelamento_inscricao_gateway_desfeito", $evento, $pessoa, $this, true);
            } else {
                $assunto = 'Inscrição Cancelada - ' . $evento->titulo;
                $mensagem = Mensagens::getInstance()->get("email_cancelamento_inscricao_gateway", $evento, $pessoa, $this, true);
            }
            $organizador->enviarEmail($pessoa->email, $assunto, $mensagem);

//            $organizador->enviarEmail($organizador->email, 'Cópia - ' . $assunto, $mensagem);
            // Email para organizador
            if ($desfeito) {
                $assunto = 'Inscrição Cancelada - ' . $evento->titulo . ' - ' . $pessoa->nome;
                $mensagem = Mensagens::getInstance()->get("email_cancelamento_inscricao_gateway_organizador", $evento, $pessoa, $this, true);
                $organizador->enviarEmail($organizador->email, $assunto, $mensagem);
            }
        }

        if ($primeiroCancelamento)
            // Notificar observadores
            Inscricoes::getInstance()->notify($this,Enum::INSCRICAO_CANCELADA);
    }

    /**
     * Vence a insrição, enviando um email notificando o inscrito e setando os campos
     * Vencido e data_vencido
     */
    public function vencer()
    {
        // Obter pessoas
        $pessoa = $this->pessoa();
        $evento = $this->evento();
        $organizador = $evento->organizador();

        // Se vencendo pela primeira vez, enviar emails
        $enviarEmails = ($this->vencido == null);

        $this->data_vencido = date('Y-m-d H:i:s');
        $this->vencido = '1';
        $this->confirmado = '0';
        Inscricoes::getInstance()->save($this->id, $this);

        //var_dump($evento->organizador());
        if (($evento->noFuturo() || $evento->acontecendo()) && $enviarEmails) {
            // Email para inscrito
            $assunto = 'Inscrição Vencida - ' . $evento->titulo;
            if ($this->forma_pagamento_gateway != null)
                $mensagem = Mensagens::getInstance()->get("email_cancelamento_inscricao_tempo_boleto_gateway", $evento, $pessoa, $this, true);
            else
                $mensagem = Mensagens::getInstance()->get("email_cancelamento_inscricao_tempo", $evento, $pessoa, $this, true);
//            echo "Enviar email<br>$assunto<br>$mensagem<br><br><br>";
            $organizador->enviarEmail($pessoa->email, $assunto, $mensagem);
//            $organizador->enviarEmail($evento->organizador()->email,'[Cópia] '.$assunto, $mensagem);
        }
    }

    /**
     * Envia um email ao inscrito sugerindo que ele confirme sua inscrição realizando o pagamento
     */
    public function confirmeAgora()
    {
        // Obter pessoas
        $pessoa = $this->pessoa();
        $evento = $this->evento();
        $organizador = $evento->organizador();

        //var_dump($evento->organizador());
        if ($evento->noFuturo()) {
            // Email para inscrito
            $assunto = 'Confirme agora sua inscrição ' . $pessoa->primeiro_nome() . '!';
            $mensagem = Mensagens::getInstance()->get("email_confirme_agora", $evento, $pessoa, $this, true);
//            var_dump($mensagem);
            $organizador->enviarEmail($pessoa->email, $assunto, $mensagem);
//            $organizador->enviarEmail($evento->organizador()->email,'[Cópia] '.$assunto, $mensagem);
        }
    }

    public function enviarEmailInscricaoRealizada()
    {
        // Obter pessoas
        $pessoa = $this->pessoa();
        $evento = $this->evento();
        $organizador = $evento->organizador();

        // Avisa apenas se for um evento pago - não avisa quando for confirmação pelo organizador
        if ($evento->pago!=='pago') return;

        if ($evento->noFuturo()) {
            // Email para inscrito
            $assunto = 'Inscrição em "'.$evento->titulo.'"';
            $mensagem = Mensagens::getInstance()->get("email_realizacao_inscricao_pagar", $evento, $pessoa, $this, true);
            $organizador->enviarEmail($pessoa->email, $assunto, $mensagem);
            // Email para organizador
            $assunto = 'Inscrição - '.$evento->titulo.' - '. $pessoa->nome;
            $mensagem = Mensagens::getInstance()->get("email_realizacao_inscricao_organizador", $evento, $pessoa, $this, true);
            $organizador->enviarEmail($evento->organizador()->email, $assunto, $mensagem);
        }
    }


    /**
     * Marca presença
     */
    public function presenca()
    {
        if ($this->presente != 1) {
            $this->presente = 1;
            $this->confirmado = 1;
            Inscricoes::getInstance()->save($this->id, $this);
            if (TGO_EVENTO_GAMIFICATION === true) {
                // Primeira presença?
                if ($this->pessoa()->getCountConfirmados() == 1)
                    Gamification::getInstance()->broadcast('first_presence', $this->id_pessoa, $this);
                else
                    Gamification::getInstance()->broadcast('event_presence', $this->id_pessoa, $this);
            }
        }
        return $this;
    }

    /**
     * Retornar link público para inscrição
     */
    public function getLinkPagamento()
    {
        return get_permalink($this->id_evento) . '/?inscricao=1&ticket=' . ($this->id * 13);
    }

    /**
     * Retornar link público para cancelamento
     */
    public function getLinkCancelamento()
    {
        return get_permalink($this->id_evento) . '/?inscricao=1&ticket=' . ($this->id * 13).'&cancelar='.$this->id_pessoa;
    }


    public function getLinkAvaliacao()
    {
        return get_permalink($this->id_evento) . '/?avaliacao=' . ($this->id * 13);
    }

    public function getLinkCertificado()
    {
        return get_permalink($this->id_evento) . '/?certificado=' . ($this->id * 13);
    }

    /**
     * @param $width
     * @return string
     * @see https://developers.google.com/chart/infographics/docs/qr_codes?csw=1
     */
    public function getLinkQrCode($width=300)
    {
        return "https://chart.googleapis.com/chart?cht=qr&chs={$width}x{$width}&chl={$this->id_evento}-{$this->id}-{$this->id_pessoa}";
    }




//    /**
//     * Determina a categoria desta inscrição
//     * @param $idCategoria int
//     */
//    public function setCategoria($idCategoria)
//    {
//        $this->id_categoria = $idCategoria;
//        Categorias::getInstance()->save($this->id,$this);
//        return $this;
//    }


    /// Codigo antigo


//    static function rejeitarInscricao($inscricao)
//    {
//        // Obter pessoas
//        $pessoa = Pessoa::obterPorId($inscricao->id_pessoa);
//        $evento = Eventos::getInstance()->obterPorId($inscricao->id_evento);
//
//        global $wpdb;
//        $ok = $wpdb->update('ev_inscricoes', array('confirmado' => 0), array('id' => $inscricao->id));
//        if (!$ok) {
//            throw new Exception("Erro ao rejeitar inscrição.");
//        }
//
//        $mensagem = nl2br($evento->organizador()->email_semvagas);
//        $mensagem = $evento->substituirVariaveis($mensagem, $inscricao);
//        $assunto = $evento->post_title;
//
//        //echo $assunto."<br><Br>".$mensagem."<br><br><BR>";
//        //die();
//
//        enviarEmailOrganizador($evento->organizador()->titulo, $evento->organizador()->email, $pessoa->email, $assunto, $mensagem);
//    }


//    static function cobrarPagamento($inscricao)
//    {
//        // Obter pessoas
//        $pessoa = Pessoa::obterPorId($inscricao->id_pessoa);
//        $evento = Eventos::getInstance()->obterPorId($inscricao->id_evento);
//
//        // Evento pago
//        if ($evento->id_organizador == 597) {
//            // GBG - GDG
//            //%pessoa_nome% %evento_titulo% %evento_data_hora% %evento_local% %evento_local_endereco% %evento_local_telefone%
//            $mensagem = "Olá %pessoa_nome%,<br> <br>
//                        Você fez sua pré-inscrição no curso Gestão de Redes Sociais, dias 26 e 27 de julho.<br><br>
//                        Para garantir a inscrição no 1º lote promocional no valor de R$ 390,00, pedimos que confirme o pagamento até hoje.<br><br>
//                        Acesse este <a href='%link_inscrito%'>link</a> para realizar a confirmação.<br><br>
//                        Caso não consiga confirmar a tempo, você ainda poderá se inscrever no 2º lote, por R$ 490,00.<br><Br>
//                        Se desejar mais informações sobre o curso, responda este email com suas questões!<br><br>
//                        Abraços<br><br>
//                        Equipe Inspirar Digital";
//            $mensagem = $evento->substituirVariaveis($mensagem, $inscricao);
//            $assunto = 'Confirme sua inscrição!';
//
//            enviarEmailOrganizador($evento->organizador()->titulo, $evento->organizador()->email, $pessoa->email, $assunto, $mensagem);
//            echo "Email enviado para " . $pessoa->nome . '<Br>';
//
//            // Registrar que enviou email cobrando - depois
//        } else {
//            die ("Organizador sem evento pago! GBG GDG");
//        }
//    }


    public function titulo_forma_pagamento_gateway()
    {
        return PagSeguroUtil::getFormaPagamentoTituloString($this->forma_pagamento_gateway);
    }


    public function titulo_forma_pagamento()
    {
        if ($this->forma_pagamento != 'Gateway') {
            return $this->forma_pagamento;
        } else {
            return 'Gateway' . ($this->forma_pagamento_gateway != null ? " - " . $this->titulo_forma_pagamento_gateway() : "");
        }
    }

    /**
     * @param bool|true $considerarDados Irá considerar a forma de pagamento, se está confirmado, para então retornar o título mais realista
     * @return mixed
     */
    public function titulo_status_gateway($considerarDados = true)
    {
        if ($considerarDados) {
            if ($this->forma_pagamento != 'Gateway')
                return null;
            else {
                return PagSeguroUtil::getStatusTituloString($this->status_gateway);
            }
        } else {
            return PagSeguroUtil::getStatusTituloString($this->status_gateway);
        }
    }

    /**
     * Save current record
     * @return null
     */
    public function save()
    {
        return Inscricoes::getInstance()->save($this->id, $this);
    }

    /**
     * Retorna a resposta de uma pergunta desta inscrição, caso exista
     * @param $idPergunta
     * @return Resposta
     */
    public function getAvaliacaoResposta($idPergunta)
    {
        return Respostas::getInstance()->getByInscricaoPergunta($this->id, $idPergunta);
    }

    /**
     * Diz resposta de uma pergunta desta inscrição, caso exista
     * @param $idPergunta
     * @return bool
     */
    public function hasAvaliacaoResposta($idPergunta)
    {
        return $this->getAvaliacaoResposta($idPergunta) != null;
    }

    /**
     * Diz se esta inscrição já deu alguma avaliação
     */
    public function hasAvaliacao()
    {
        return Respostas::getInstance()->hasByInscricao($this->id);
    }

    public function setCategoria($id_categoria)
    {
        /* @var $categoria Categoria */
        $categoria = Categorias::getInstance()->getById($id_categoria);
        $this->id_categoria = $id_categoria;
        if ($categoria->id_preco != null) {
            $this->setPreco($categoria->id_preco);
        }
    }

    public function setPreco($id_preco)
    {
        /* @var $preco Preco */
        $preco = Precos::getInstance()->getById($id_preco);
        $this->id_preco = $id_preco;
        // Alterar valores e tal
        $this->valor_inscricao = $preco->getValorAtual();
    }




}
