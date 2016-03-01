<?php
use TiagoGouvea\AgileCRM_PHPWrapper\AgileCrm;
use TiagoGouvea\AgileCRM_PHPWrapper\AgileCrmContact;
use TiagoGouvea\PHPObserver\Observer;
use TiagoGouvea\PHPObserver\Subject;
use TiagoGouvea\PLib;
use TiagoGouvea\WPDataMapper\WPSimpleDAO;

require_once 'InscricoesObserver.php';

class Inscricoes extends WPSimpleDAO implements Subject
{
    /* @var $observers Observer[] */
    private $observers = array();

    /**
     * @return Inscricoes
     */
    static function getInstance()
    {
        return parent::getInstance();
    }

    function init()
    {
        if (!$this->initialized) {
            $this->_init("ev_inscricoes", "Inscricao", "id desc");
            // Configurar observers
            $this->attach(new InscricaoObserver());
        }
    }

    public function getInscritos($idEvento)
    {
        return $this->getByEvento($idEvento);
    }

    public function getInscritosCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, 'coalesce(pre_inscricao,0)=0');
    }

    public function getPreInscritos($idEvento)
    {
        return $this->getByEvento($idEvento, 'pre_inscricao=1 and confirmado is null');
    }

    public function getPreInscritosCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, 'pre_inscricao=1 and confirmado is null');
    }

    public function getConfirmados($idEvento)
    {
        return $this->getByEvento($idEvento, 'confirmado=1');
    }

    public function getConfirmadosCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, 'confirmado=1');
    }


    public function getConfirmadosMulheresCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, "confirmado=1 and ev_pessoas.extras like '%Feminino%'");
    }

    public function getConfirmadosHomensCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, 'confirmado=1 and ev_pessoas.extras like "%Masculino%"');
    }

    public function getNaoConfirmados($idEvento)
    {
        return $this->getByEvento($idEvento, 'confirmado is null and coalesce(id_situacao,0)<>10 and coalesce(pre_inscricao,0)=0');
    }

    private function getConfirmadosFalse($idEvento)
    {
        return $this->getByEvento($idEvento, 'coalesce(confirmado,0)=0');
    }

    public function getNaoConfirmadosCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, 'confirmado is null and coalesce(id_situacao,0)<>10 and coalesce(pre_inscricao,0)=0');
    }

    public function getRejeitados($idEvento)
    {
        return $this->getByEvento($idEvento, 'confirmado=0');
    }

    public function getRejeitadosCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, 'confirmado=0');
    }

    public function getFilaEspera($idEvento)
    {
        return $this->getByEvento($idEvento, 'id_situacao=10');
    }

    public function getFilaEsperaCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, 'id_situacao=10');
    }

    public function getPresentesCount($idEvento)
    {
        return $this->getByEventoCount($idEvento, 'confirmado=1 and presente=1');
    }


    public function getPresentesComAvaliacao($id_evento)
    {
        $inscricoes = $this->getPresentes($id_evento);
        $return = null;
        foreach ($inscricoes as $inscrito){
            if (!$inscrito->hasAvaliacao()) continue;
            $return[]=$inscrito;
        }
        return $return;
    }

    /**
     * @param $idEvento
     * @return Inscricao[]
     */
    public function getPresentes($idEvento)
    {
        return $this->getByEvento($idEvento, 'confirmado=1 and presente=1');
    }

    public function getByEvento($idEvento, $whereExtra = null, $orderBy = 'ev_inscricoes.id desc')
    {
        $sql = "SELECT ev_inscricoes.*, ev_pessoas.nome, ev_pessoas.extras, ev_pessoas.email "
            . "FROM ev_inscricoes "
            . "LEFT JOIN ev_pessoas on ev_pessoas.id=ev_inscricoes.id_pessoa "
            . "WHERE id_evento = $idEvento "
            . ($whereExtra != null ? " and $whereExtra " : '')
            . ($orderBy != null ? " order by $orderBy " : "");
//        var_dump($sql);
        /* @var $wpdb wpdb */
        $result = $this->wpdb()->get_results($sql);
//        var_dump($result);
        if ($result)
            return $this->toObject($result);
    }

    public function getByEventoCount($idEvento, $whereExtra = null, $orderBy = 'ev_inscricoes.id desc')
    {
        $sql = "SELECT coalesce(count(ev_inscricoes.id),0) as count "
            . "FROM ev_inscricoes "
            . "LEFT JOIN ev_pessoas on ev_pessoas.id=ev_inscricoes.id_pessoa "
            . "WHERE id_evento = $idEvento "
            . ($whereExtra != null ? " and $whereExtra " : '')
            . ($orderBy != null ? " order by $orderBy " : "");
        /* @var $wpdb wpdb */
        $result = $this->wpdb()->get_results($sql);
        if ($result)
            return $result[0]->count;
    }

    public function getByPessoa($idPessoa)
    {
        $sql = "SELECT * FROM ev_inscricoes WHERE id_pessoa=$idPessoa order by id desc";
        $result = $this->wpdb()->get_results($sql);
        if ($result)
            return $this->toObject($result);
    }

    public function getCountByPessoa($idPessoa)
    {
        $sql = "SELECT count(id) as count FROM ev_inscricoes WHERE id_pessoa=$idPessoa order by id desc";
        $row = $this->wpdb()->get_row($sql);
        return $row->count;
    }

    /**
     * @param $idPessoa
     * @param null $order
     * @return Inscricao[]
     */
    public function getByPessoaConfirmado($idPessoa,$order=null)
    {
        return $this->where('confirmado=1 and id_pessoa=' . $idPessoa,false,$order);
    }

    public function getCountByPessoaConfirmados($idPessoa)
    {
        $sql = "SELECT count(id) as count FROM ev_inscricoes WHERE id_pessoa=$idPessoa and confirmado=1 order by id desc";
        $row = $this->wpdb()->get_row($sql);
        return $row->count;
    }

    public function getCountByPessoaPresente($idPessoa,$dataInicial)
    {
        if ($dataInicial!=null)
            $whereExtra = " and data_inscricao>='$dataInicial'";
        $sql = "SELECT count(id) as count
                FROM ev_inscricoes
                WHERE id_pessoa=$idPessoa and confirmado=1 and presente=1 $whereExtra
                order by id desc";
        $row = $this->wpdb()->get_row($sql);
        return $row->count;
    }

    public function getCountByPessoaConfirmadosAntesCleanCode($idPessoa)
    {
        $sql = "SELECT count(id) as count FROM ev_inscricoes WHERE id_pessoa=$idPessoa and confirmado=1 and id_evento<1065 order by id desc";
        $row = $this->wpdb()->get_row($sql);
        return $row->count;
    }


    /**
     * Retorna as inscrições que devem ser vencidas por passarem do prazo para confirmação
     * 1 - Marcadas como pagamento com boleto, após 7 dias corridos
     */
    public function getVencerDia($idEvento)
    {
        $dataLimite7 = time() - (24 * 60 * 60 * 7); // 7 dias corridos... poderia ser definido por organizador?
        $dataLimite7 = date('Y-m-d', $dataLimite7);
        $sql = "SELECT ev_inscricoes.* "
            . "FROM ev_inscricoes "
            . "WHERE id_evento=$idEvento and confirmado is null and vencido is null and coalesce(id_situacao,0)<>10 and
            (
             (data_inscricao<='$dataLimite7' and forma_pagamento_gateway=2)
            )";
        $result = $this->wpdb()->get_results($sql);
        if ($result)
            return $this->toObject($result);
    }

    /**
     * Retorna as inscrições que devem ser vencidas por passarem do prazo para confirmação, por hora
     * 1 - Marcadas como pagamento de cartão, após 6 horas corridas
     * 2 - Sem marcar nenhuma opção de pagamento, após 28 horas
     * 3 - Marcadas como pagamento com boleto, após 6 dias corridos
     */
    public function getVencerHora($idEvento)
    {
        $dataLimite6 = time() - (3 * 60 * 60); // 6 horas
        $dataLimite6 = date('Y-m-d H:00:00', $dataLimite6);

        $dataLimite28 = time() - (28 * 60 * 60); // 28 horas
        $dataLimite28 = date('Y-m-d H:00:00', $dataLimite28);

        $dataLimite6dias = time() - (6 * 24 * 60 * 60); // 6 dias
        $dataLimite6dias = date('Y-m-d H:00:00', $dataLimite6dias);

        $sql = "SELECT ev_inscricoes.* "
            . "FROM ev_inscricoes "
            . "WHERE id_evento=$idEvento and confirmado is null and vencido is null and coalesce(id_situacao,0)<>10 and
            (
              (data_inscricao<='$dataLimite6' and forma_pagamento_gateway=1)
              or
              (data_inscricao<='$dataLimite28' and forma_pagamento_gateway is null)
              or
              (data_inscricao<='$dataLimite6dias' and forma_pagamento_gateway=2)
            )";
//        PLib::var_dump($sql);
        $result = $this->wpdb()->get_results($sql);
        if ($result)
            return $this->toObject($result);
    }

    /**
     * Retorna as inscrições que devem ser vencidas em breve, para notificar
     * 1 - Marcadas como pagamento de cartão, após 2 horas corridas
     * 2 - Sem marcar nenhuma opção de pagamento, após 23 horas
     * 3 - Boleto notificar em 4 dias
     */
    public function getPagueAgora($idEvento)
    {
        $dataLimite23 = time() - (23 * 60 * 60); // 23 horas
        $dataLimite23 = date('Y-m-d H:00:00', $dataLimite23);
        $dataLimite24 = time() - (24 * 60 * 60); // 24 horas
        $dataLimite24 = date('Y-m-d H:00:00', $dataLimite24);

        $dataLimite3 = time() - (3 * 60 * 60); // 3 horas
        $dataLimite3 = date('Y-m-d H:00:00', $dataLimite3);
        $dataLimite2 = time() - (2 * 60 * 60); // 2 horas
        $dataLimite2 = date('Y-m-d H:00:00', $dataLimite2);

        $dataLimite5dias = time() - (4 * 24 * 60 * 60) + (1 * 60 * 60); // 4 dias e uma hora
        $dataLimite5dias = date('Y-m-d H:00:00', $dataLimite5dias);
        $dataLimite5dias2 = time() - (4 * 24 * 60 * 60); // 4 dias
        $dataLimite5dias2 = date('Y-m-d H:00:00', $dataLimite5dias2);
        $sql = "SELECT ev_inscricoes.* "
            . "FROM ev_inscricoes "
            . "WHERE id_evento=$idEvento and confirmado is null and vencido is null and coalesce(id_situacao,0)<>1 and
             (
              (data_inscricao>='$dataLimite3' and data_inscricao<'$dataLimite2' and forma_pagamento_gateway=1)
              or
              (data_inscricao>='$dataLimite24' and data_inscricao<'$dataLimite23' and forma_pagamento_gateway is null)
               or
              (data_inscricao>='$dataLimite5dias2' and data_inscricao<'$dataLimite5dias' and forma_pagamento_gateway=2)
             )";
//        PLib::var_dump($sql);
        $result = $this->wpdb()->get_results($sql);
        if ($result)
            return $this->toObject($result);
    }

    /**
     * @param Evento $evento
     * @param Pessoa $pessoa
     * @param bool $preInscricao
     * @param null $id_preco
     * @param null $id_categoria
     * @param null $c_utmz
     * @return Inscricao
     */
    function certificarInscricao(Evento $evento, Pessoa $pessoa, $preInscricao=false, $id_preco = null, $id_categoria=null, $c_utmz=null)
    {
        // Verificar se já existe inscrição no banco - que não esteja cancelada nem vencida
        /* @var $wpdb wpdb */
        /* @var $inscricao Inscricao */
        $inscricao = $this->getByEventoPessoa($evento->id, $pessoa->id, true);
//        var_dump($inscricao); die();

        if ($inscricao != null && $inscricao->pre_inscricao && $preInscricao == false)
            $inscricao = null;

        // Incluir?
        if ($inscricao == null) {
            $campos = array('id_evento' => $evento->id, 'id_pessoa' => $pessoa->id, 'pre_inscricao' => $preInscricao);

            // Agregar campos
            if ($evento->fila_espera)
                $campos['id_situacao'] = 10;

            $ok = $this->wpdb()->insert('ev_inscricoes', $campos);

            /* @var $inscricao Inscricao */
            $inscricao = Inscricoes::getInstance()->getById($this->wpdb()->insert_id);
        } else {
            // "Ressucitar" inscrição?

        }

        if ($c_utmz!=null)
            $inscricao->c_utmz=$c_utmz;

        if ($id_categoria != null)
            $inscricao->setCategoria($id_categoria);
        else if ($id_preco != null)
            $inscricao->setPreco($id_preco);

//        var_dump($inscricao);
//        die();
        // Notificar observadores
        $this->notify($inscricao,Enum::INSCRICAO_REALIZADA);

//        // TESTE DE OBSERVER E INTEGRACOES E TAL
//        require_once PLUGINPATH.'/vendor/TiagoGouvea/AgileCRM_PHPWrapper/AgileCrm.php';
//        // Considerando que este seja o local "oficial" onde a inscrição acontece
//        $agileCrm = Integracoes::getInstance()->getByServico('AgileCRM');
//        $agileCrm = $agileCrm[0];
//        // Imaginando que avisei que teve a inscrição
//        // Imaginando que o Agile estava escutando e foi avisado
//        $email = $agileCrm->client;
//        $token = $agileCrm->token;
//        $domain = substr($email,strpos($email,'@')+1);
//        $domain = substr($domain,0,strpos($domain,'.'));
//        $agile = new AgileCrm($email,$domain,$token);
//        $agile = AgileCrm::getInstance();
//        $contact = $agile->findByEmail($inscricao->pessoa()->email);
//        if ($contact==null){
//            $contact = new AgileCrmContact();
//            $contact->firstName=$inscricao->pessoa()->nome;
//            $contact->email=$inscricao->pessoa()->email;
//            $agile->addContact($contact);
//        } else {
//            var_dump($contact);
//        }
//        die();

        // Obter objeto da inscricao

        if ($evento->confirmacao == 'imediata')
            $inscricao->confirmar();
        else
            $inscricao->enviarEmailInscricaoRealizada();

        $inscricao->save();

        return $inscricao;
    }

    /**
     * Retorna uma inscrição no evento informado da pessoa informada
     * @param $idEvento
     * @param $idPessoa
     * @param $ativa Indica se apenas deve obtida uma inscrição ativa (não cancelada nem confirmada)
     * @return Inscricao
     */
    function getByEventoPessoa($idEvento, $idPessoa, $ativa = false)
    {
        $whereAtiva = null;
        if ($ativa)
            $whereAtiva = " and confirmado is null and vencido is null";
        $sql = "SELECT * FROM ev_inscricoes WHERE id_evento = $idEvento and id_pessoa=$idPessoa" . $whereAtiva;
        $record = $this->wpdb()->get_row($sql);
        if ($record)
            return $this->populate($record);
    }

    /**
     * Retorna uma inscrição no evento informado da pessoa informada, confirmada
     * @param $idEvento
     * @param $idPessoa
     * @return Inscricao
     */
    function getByEventoPessoaConfirmado($idEvento, $idPessoa)
    {
        $whereAtiva = null;
        $sql = "SELECT * FROM ev_inscricoes WHERE id_evento = $idEvento and id_pessoa=$idPessoa and confirmado=1";
//        var_dump($sql);
        $record = $this->wpdb()->get_row($sql);
//        var_dump($record);
        if ($record)
            return $this->populate($record);
    }


    /**
     * Processa um lote de transações
     * Retorna um array com inscrições e falhas
     * @param PagSeguroTransactionSummary[] $transacoes
     * @return array mixed
     */
    public function processarTransacoes($servico, $transacoes)
    {
        $return = array();
        $result['inscricoes'] = null;
        $result['falhas'] = null;
        foreach ($transacoes as $transacao) {
            $result = $this->processarTransacaoPagSeguro($transacao);
            if (!is_string($result) && get_class($result) == 'Inscricao')
                $return['inscricoes'][] = $result;
            else
                $return['falhas'][] = $result;
        }
        return $return;
    }

    /**
     * Processa uma transação recebida por um gateway de pagamento
     * @param $servico
     * @param Object $transaction
     * @return Inscricao em caso de sucesso, ou a string com a falha
     * @internal param $string
     */
    public function processarTransacaoPagSeguro($transaction)
    {
        // Iniciar variáveis
        $id_inscricao = null;

        if (get_class($transaction)!='PagSeguroTransactionSummary' && get_class($transaction)!='PagSeguroTransaction') {
            return "Registro do tipo " . get_class($transaction);
        }

//        Plib::var_dump($servico);
//        Plib::var_dump($transaction);

        $status = $transaction->getStatus()->getValue();
        $id_inscricao = $transaction->getReference();
        $valorBruto = $transaction->getGrossAmount();
        $valorLiquido = $transaction->getNetAmount();
        $taxa = $valorBruto - $valorLiquido;
        $data = $transaction->getDate();
        $data_pagseguro = $transaction->getLastEventDate();
        $codigo = $transaction->getCode();
        if ($transaction->getPaymentMethod()!=null)
            $forma_pagamento = $transaction->getPaymentMethod()->getType()->getValue();
        //if ($transaction->getPaymentMethod()->getCode())
        //$forma_pagamento = $transaction->getPaymentMethod()->getCode()->getValue();

//        if ($id_inscricao!=546) return null;

//        Plib::var_dump($transaction);

        if ($id_inscricao == null) {
//            PLib::var_dump($transaction);
//            die();
            return "Id da inscrição nulo. Codigo Transação: " . $codigo.". Valor: ".PLib::format_cash($valorBruto) .". Data: ".$data;
        }

        /** @var $inscricao Inscricao */
        $inscricao = Inscricoes::getInstance()->getById($id_inscricao);

        if ($inscricao == null)
            return "Transação de inscrição não encontrada no banco de dados: " . $id_inscricao . " - Codigo Transação: " . $codigo;;

        // Pago?
        if ($status == 3 || $status == 4) {
            // Sim
//            echo $inscricao->pessoa()->nome."<br>";
//            var_dump($inscricao->forma_pagamento);
//            echo "<br>";

            if ($inscricao->confirmado != 1 || $inscricao->forma_pagamento_gateway != $forma_pagamento || $inscricao->valor_liquido != $valorLiquido || $inscricao->forma_pagamento != 'Gateway' || ($inscricao->confirmado==0 && strtotime($inscricao->data_cancelamento)<strtotime($data_pagseguro))) {
                $inscricao->_observacao = "Inscrição confirmada";
                $inscricao->confirmarPagamento($status, $data, $valorBruto, $valorLiquido, $taxa, $codigo, $data_pagseguro, $forma_pagamento);
            }
            if ($inscricao->status_gateway != $status) {
                $inscricao = $inscricao->registrarStatusGateway($data_pagseguro, $codigo, $status, $forma_pagamento);
            }
        } else if ($status == 6 || $status == 7) {
            // 6 - Estorno / 7 - Cancelada
            // Ainda não - Registrar o que? A situação né...
            // Se já estava pago e foi para Cancelada, alterar registro, avisar
            // Se já estava pago, registrar falha, pois é um estorno ou coisa do tipo
            // Pensar, porque pode ter sido paga em dinheiro, ai tem que reativar e tal, ignorar o cancelado

            echo "<hr>";
            var_dump(strtotime($inscricao->data_confirmacao));
            var_dump($inscricao->data_confirmacao);
            echo "<br>";
            var_dump(strtotime($data_pagseguro));
            var_dump($data_pagseguro);
            echo "<br>";
            var_dump(strtotime($inscricao->data_confirmacao)<strtotime($data_pagseguro));
            echo "<br>";echo "<br>";

            // Cancelada
            if ($inscricao->confirmado == '1' && $inscricao->forma_pagamento != 'Dinheiro' && $inscricao->forma_pagamento != 'Depósito' && strtotime($inscricao->data_confirmacao)<strtotime($data_pagseguro)) {
                $inscricao->_observacao = "Pagamento cancelado (desfeito)";
                $inscricao->cancelarFalhaPagamento(true);
            } else if ($inscricao->confirmado === null) {
                $inscricao->_observacao = "Pagamento cancelado";
                $inscricao->cancelarFalhaPagamento();
            }
            // Se foi pago em dinheiro, a inscrição permanece confirmada
        } else {
            // Registrar status apenas quando não confirmado
            if ($inscricao->confirmado == null && $inscricao->vencido == null)
                $inscricao = $inscricao->registrarStatusGateway($data_pagseguro, $codigo, $status, $forma_pagamento);
        }

        $inscricao->_status_gateway = $status . " - " . \lib\PagSeguroUtil::getStatusTituloString($status);

        $inscricao->save();

        return $inscricao;
    }

    public function processarTransacaoCielo($transcao)
    {
        // Descer dados
        $status = $transcao['payment_status'];
        $id_inscricao = $transcao['order_number'];
        $valorBruto = $transcao['amount'];
        $data = strtotime($transcao['created_date']);
        $codigo = $transcao['checkout_cielo_order_number'];
        $forma_pagamento = $transcao['payment_method_type'];

//        payment_method_type=1,
//        payment_method_brand=1,
//        payment_status=7
//        amount=1000

        // 1 - identificar inscrição
        /** @var $inscricao Inscricao */
        $inscricao = Inscricoes::getInstance()->getById($id_inscricao);

        if ($inscricao == null)
            return "Inscrição não encontrada no banco de dados: " . $id_inscricao;

        //1	Pendente (Para todos os meios de pagamento)
        //2	Pago (Para todos os meios de pagamento)
        //3	Negado (Somente para Cartão Crédito)
        //5	Cancelado (Para cartões de crédito)
        //6	Não Finalizado (Todos os meios de pagamento)
        //7	Autorizado (somente para Cartão de Crédito)

        // Pagamento
        // order_number=43,amount=1000,checkout_cielo_order_number=43f39f6b3dff4cbeab39c05b2ea4e68c,"created_date=08/12/2015 14:32:50","customer_name=Tiago Gouvêa",customer_phone=3288735683,customer_identity=06804501659,customer_email=tiago@phormar.com.br,shipping_type=5,shipping_price=0,payment_method_type=1,payment_method_brand=1,payment_maskedcreditcard=406655******7460,payment_installments=1,payment_antifraudresult=1,payment_status=7,tid=1070722607000000002A
        // Mudança de Status
        // checkout_cielo_order_number=43f39f6b3dff4cbeab39c05b2ea4e68c,amount=1000,order_number=43,payment_status=7
//        "data=2016-01-27 - 18:15:48",checkout_cielo_order_number=cf00b44049464f04a4f1a726ab76be33,amount=43000,order_number=46,payment_status=2


        // Pago?
        if ($status == 2 || $status==7) {
            // 2 - Pago / 7 - Autorizado - Sim, pago!
            if ($inscricao->confirmado != 1 || $inscricao->forma_pagamento_gateway != $forma_pagamento || $inscricao->valor_pago != $valorBruto || $inscricao->forma_pagamento != 'Gateway' || ($inscricao->confirmado == 0 && strtotime($inscricao->data_cancelamento) < $data)) {
                $inscricao->_observacao = "Inscrição confirmada";
                $inscricao->confirmarPagamento($status, $data, $valorBruto, null, null, $codigo, null, $forma_pagamento);
            }
            if ($inscricao->status_gateway != $status) {
                $inscricao = $inscricao->registrarStatusGateway(null, $codigo, $status, $forma_pagamento);
            }
        } else if ($status==3 || $status==5 || $status==6){
            // 3 - Negado / 5 - Cancelada / 6 - Não Finalizado
            if ($inscricao->confirmado == '1' && $inscricao->forma_pagamento != 'Dinheiro' && $inscricao->forma_pagamento != 'Depósito' && strtotime($inscricao->data_confirmacao)<strtotime($data)) {
                $inscricao->_observacao = "Pagamento cancelado (desfeito)";
                $inscricao->cancelarFalhaPagamento(true);
            } else if ($inscricao->confirmado === null) {
                $inscricao->_observacao = "Pagamento cancelado";
                $inscricao->cancelarFalhaPagamento();
            }
        } else if ($status == 600 || $status == 700) {

            // Ainda não - Registrar o que? A situação né...
            // Se já estava pago e foi para Cancelada, alterar registro, avisar
            // Se já estava pago, registrar falha, pois é um estorno ou coisa do tipo
            // Pensar, porque pode ter sido paga em dinheiro, ai tem que reativar e tal, ignorar o cancelado

            // Cancelada
//            if ($inscricao->confirmado == '1' && $inscricao->forma_pagamento != 'Dinheiro' && $inscricao->forma_pagamento != 'Depósito' && strtotime($inscricao->data_confirmacao)<strtotime($data_pagseguro)) {
//                $inscricao->_observacao = "Pagamento cancelado (desfeito)";
//                $inscricao->cancelarFalhaPagamento(true);
//            } else if ($inscricao->confirmado === null) {
//                $inscricao->_observacao = "Pagamento cancelado";
//                $inscricao->cancelarFalhaPagamento();
//            }
            // Se foi pago em dinheiro, a inscrição permanece confirmada
        } else {
            // Registrar status apenas quando não confirmado
            if ($inscricao->confirmado == null && $inscricao->vencido == null)
                $inscricao = $inscricao->registrarStatusGateway(null, $codigo, $status, $forma_pagamento);
        }

        $inscricao->_status_gateway = $status . " - " . \lib\CieloUtil::getStatusTituloString($status);
        $inscricao->save();

        return $inscricao;
    }


    /* Obtem os inscritos de acordo com o filtro String */
    public function getByFilterString(Evento $evento, $filter)
    {
        if ($filter == 'preInscritos') {
            $inscritos = Inscricoes::getInstance()->getPreInscritos($evento->id);
        } else if ($filter == 'confirmados') {
            $inscritos = Inscricoes::getInstance()->getConfirmados($evento->id);
        } elseif ($filter == 'naoConfirmados') {
            $inscritos = Inscricoes::getInstance()->getNaoConfirmados($evento->id);
        } else if ($filter=='confirmadosFalse'){
            $inscritos = Inscricoes::getInstance()->getConfirmadosFalse($evento->id);
        } elseif ($filter == 'rejeitados') {
            $inscritos = Inscricoes::getInstance()->getRejeitados($evento->id);
        } elseif ($filter == 'filaEspera') {
            $inscritos = Inscricoes::getInstance()->getFilaEspera($evento->id);
        } elseif ($filter == 'presentes') {
            $inscritos = Inscricoes::getInstance()->getPresentes($evento->id);
        } else {
            $inscritos = Inscricoes::getInstance()->getByEvento($evento->id);
        }
        return $inscritos;
    }

    public function callbackPagamento($id_integracao,$transaction_id)
    {
        //echo "<h1>$transaction_id</h1>";
        //?transaction_id=42E40F60-032A-4590-A357-D067CAA5858F
        // Validar o pagamento

        // Incluir PagSeguro
        include_once PLUGINPATH . '/vendor/PagSeguro/PagSeguroLibrary.php';
        // Obter a transação retornada
        // Verificar situação
        /* @var $integracao Integracao */
        $integracao = Integracoes::getInstance()->getById($id_integracao);
        $credentials = new PagSeguroAccountCredentials($integracao->client, $integracao->token);
        $transaction = PagSeguroTransactionSearchService::searchByCode(
            $credentials,
            $transaction_id
        );

        $inscricao = Inscricoes::getInstance()->processarTransacaoPagSeguro($transaction);
        return $inscricao;
    }


    /**
     * Attach an SplObserver
     * @link http://php.net/manual/en/splsubject.attach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to attach.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function attach(Observer $observer)
    {
        $this->observers[]=$observer;
    }

    /**
     * Detach an observer
     * @link http://php.net/manual/en/splsubject.detach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to detach.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function detach(Observer $observer)
    {
        // TODO: Implement detach() method.
    }

    /**
     * Notify an observer
     * @link http://php.net/manual/en/splsubject.notify.php
     * @param $object
     * @param $action
     * @internal param Subject $subject
     * @since 5.1.0
     */
    public function notify($object, $action)
    {
//        var_dump($action);
        // Notificar observadores
        $acao = Enum::get($action);

//        echo "<hr>";
//        echo $action."<br>";
//        echo $acao."<br>";
//        var_dump($object);
//        die();
        foreach ($this->observers as $observer){
            $observer->update($object,$action);
        }

    }

    public function aplicarTicket(Evento $evento,$ticket)
    {
        $ticket = sanitize_text_field($ticket);
        $desconto = Descontos::getInstance()->getByEventoTicket($evento->id, $ticket);
        if ($desconto == null)
            $desconto = Descontos::getInstance()->getByTicket($ticket);
        $dados = $ticket != null && $desconto != null;
        if ($dados) {
            if ($desconto->getQuantidadeRestante() <= 0) {
                $erro = "Este ticket de desconto já foi utilizado...";
            } else {
                $_SESSION['ticket'] = $ticket;
                $_SESSION['Evento_' . $evento->id]['descontos'][$desconto->ticket] = $desconto;
                if (count($_SESSION['Evento_' . $evento->id]['descontos']) == 1)
                    setFlash("Muito bom! Você aplicou o ticket <b>$desconto->ticket</b> com <b>".($desconto->desconto_por == 'percentual' ? $desconto->desconto . '%' : PLib::format_cash($desconto->desconto))."</b> de desconto, e agora seu invesimento será de apenas ".PLib::format_cash($evento->getValorAtual())."!");
                else
                    setFlash("Você aplicou mais um desconto de <b>$desconto->desconto%</b>, agora terá o invesimento de apenas ".PLib::format_cash($evento->getValorAtual())."!");
                return true;
            }
        } else if (!$ticket)
            $erro = "Informe um ticket de desconto";
        else if (!$desconto)
            $erro = "Ticket de desconto inválido ($ticket). Confira se digitou corretamente ou entre em contato com a Inspirar Digital.";

        return $erro;
    }



}