<?php

use TiagoGouvea\PLib;
use TiagoGouvea\WPDataMapper\WPSimpleMapper;

class Evento extends WPSimpleMapper
{
    public $id;
    public $titulo;
    public $id_instrutor;
    public $id_local;
    public $id_local_pagamento;
    public $id_organizador;
    public $publicado;
    public $rascunho;
    public $descricao_1;
    public $descricao_2;
    public $descricao_3;
    public $excerpt;
    public $publico_alvo;
    public $topicos;
    public $faq;
    public $data;
    public $hora;
    public $data_fim;
    public $hora_fim;
    public $data_inicio_inscricoes;
    public $data_fim_inscricoes;
    public $vagas;
    public $pago;
    public $pago_pagseguro;
    public $pago_dinheiro;
    public $pago_deposito;
    public $pago_cielo;
    public $id_integracao_pagseguro;
    public $fila_espera;
    public $beta;
    public $campos_extras;
    public $secoes_extras;
    public $duracao;
    public $horarios;
    public $requisitos;
    public $valor;
    public $material;
    public $certificado;
    public $confirmacao;
    public $validacao_pessoa;
    public $release;
    public $fb_conversion_track;
    public $area_aluno;
    public $id_questionario;
    public $tw_conversion_track;
    public $certificado_arquivo;
    public $certificado_incluir_nome;
    public $certificado_incluir_evento;
    public $certificado_altura_nome;
    public $id_evento_pai;
    public $id_integracao_cielo;


    private $_hasFilhos;

    function noFuturo()
    {
        if ($this->data == null)
            return true;
        else {
            return $this->inicio() > time();
        }
    }

    function acontecendo()
    {
        $acontecendo = $this->inicio() < time() && $this->fim() > time();
        return $acontecendo;
    }

    function comecaEmDuasHoras()
    {
        $dataInicio = strtotime($this->data . " " . $this->hora);
        $horasParaIniciar = (int)(($dataInicio - time())) / (60 * 60);
        $acontecenEmDuasHora = $horasParaIniciar < 2 and $horasParaIniciar > -2;

        return $acontecenEmDuasHora;
    }

    function aconteceuEmDoisDias()
    {
        $data_fim = $this->fim();
        $horasParaTerminar = (int)(($data_fim - time())) / (60 * 60);
        $terminouEmDoisDias = $horasParaTerminar > -(24 * 2) && $horasParaTerminar < 0;

        return $terminouEmDoisDias;
    }

    function aconteceuEmCincoDias()
    {
        $data_fim = $this->fim();
        $horasParaTerminar = (int)(($data_fim - time())) / (60 * 60);
        $terminouEmDoisDias = $horasParaTerminar > -(24 * 5) && $horasParaTerminar < 0;

        return $terminouEmDoisDias;
    }

    function inicioTimeStamp()
    {
        $timeStamp = strtotime($this->data . " " . $this->hora . " GMT-6");

        return $timeStamp;
    }

    function fimTimeStamp()
    {
        if ($this->data_fim == null)
            $timeStamp = strtotime($this->data . " " . $this->hora_fim . " GMT-6");
        else
            $timeStamp = strtotime($this->data_fim . " " . $this->hora_fim . " GMT-6");

        return $timeStamp;
    }

    function inicio()
    {
        if ($this->hora)
            return strtotime($this->data . " " . $this->hora);
        else if ($this->data)
            return strtotime($this->data);
        else
            return null;
    }

    function fim()
    {
        if ($this->data_fim != null)
            return strtotime($this->data_fim . " " . ($this->hora_fim ? $this->hora_fim : $this->hora));
        else
            return strtotime($this->data . " " . ($this->hora_fim ? $this->hora_fim : $this->hora));
    }

    function realizado()
    {
        if ($this->data == null)
            return false;
        else {
            return $this->fim() < time();
        }
    }


    /**
     * Retorna os campos extras, caso existam, em formato de array
     */
    function getCamposExtras()
    {
        if ($this->campos_extras == null)
            return null;

        $extras = explode("\r\n", $this->campos_extras);
        $return = array();
        foreach ($extras as $extra) {
            if (trim($extra) == "")
                continue;
            // Dividir em indice e valor
            $campoExtra = explode("/", $extra);
            if (count($campoExtra) != 2)
                continue;

            $return[$campoExtra[0]] = $campoExtra[1];
        }
        //echo "<pre>";var_dump($return);die();
        return $return;
    }

    /**
     * Retorna os campos extras, caso existam, em formato de array
     */
    function getSecoesExtras()
    {
        if ($this->secoes_extras == null)
            return null;

        $extras = explode("\r\n", $this->secoes_extras);
        $return = array();
        foreach ($extras as $extra) {
            if (trim($extra) == "")
                continue;
            // Dividir em indice e valor
            $secaoExtra = explode("/", $extra);
            if (count($secaoExtra) != 2)
                continue;
            $return[$secaoExtra[0]] = $secaoExtra[1];
        }
        //echo "<pre>";var_dump($return);die();
        return $return;
    }


    /**
     * Obtem a sessão extra informada diretamente dos metadados
     * @param $secao
     */
    public function getSecaoExtra($secao)
    {
        // sanitizar isso aqui
        return get_post_meta($this->id, 'secao_' . $secao, true);
    }

    /**
     * Retorna um array com os topicos do treinamento
     */
    function getTopicos()
    {
        //var_dump($this->id_organizador);die();
        if ($this->topicos == null)
            return null;
        $topicos = explode("\r\n", $this->topicos);
        $return = array();
        foreach ($topicos as $topic) {
            if (trim($topic) == "")
                continue;
            // É um tópico ou sub-tópico?
            if (substr($topic, 0, 3) != '   ') {
                // Tópico
                $topico = array();

                // Existe marcação de material?
                if (strpos($topic, '[a]') || strpos($topic, '[v]')) {
                    $materials = trim(substr($topic, strpos($topic, '[')));
                    $materials = explode("[", $materials);
                    foreach ($materials as $material) {
                        if (trim($material) == "")
                            continue;
                        $material = substr($material, 0, 1);
                        if ($material == "v")
                            $material = "video";
                        if ($material == "a")
                            $material = "document";
                        $topico['material'][] = $material;
                    }
                    $topic = substr($topic, 0, strpos($topic, '['));
                }
                $topico['topico'] = trim($topic);

                // Adicionar ao retorno
                $return[] = $topico;
            } else {
                // SubTópico - Incrementar no último tópico
                $return[count($return) - 1]['subtopicos'][] = trim($topic);
            }
        }
        //echo "<pre>";var_dump($return);die();
        return $return;
    }


    /**
     * Obtem as categorias do evento
     * @return array Categoria
     */
    public function getCategorias(Pessoa $pessoa = null)
    {
        $categorias = Categorias::getInstance()->getByEvento($this->id, false);
        if ($pessoa != null) {
            // Verificar se existem categorias com inscrição exclusiva
            /** @var $categoria Categoria */
            foreach ($categorias as $chave => $categoria) {
                if ($categoria->condicao != null) {
                    $condicao = explode("=", stripslashes($categoria->condicao));
                    if (count($condicao) < 2) {
//                        var_dump($condicao);
                        die ("condição incorreta em categoria");
                    }
//                    echo "'".$pessoa->$condicao[0]."'=='$condicao[1]'<br>";

                    // Se na pessoa houver um campo (ou extra) com este valor, permitir a inscrição
                    $categoria->_permiteInscricao = ($pessoa->$condicao[0] == $condicao[1]);
                    if ($categoria->_permiteInscricao === false)
                        $categoria->_permiteInscricao = ($pessoa->getExtra($pessoa->$condicao[0]) == $condicao[1]);
//                    var_dump($categoria->_permiteInscricao);

                    $categorias[$chave] = $categoria;
                }
            }
        }
        return $categorias;
    }

    /**
     * Diz se o evento tem categorias
     * @return boolean
     */
    public function hasCategorias()
    {
        return Categorias::getInstance()->hasEvento($this->id, false);
    }

    /// Codigo antigo ////

    static function queryEventos($maximo = null)
    {
        global $wp_query;

        $args = array(
            'post_type' => 'tgo_evento',
            'posts_per_page' => $maximo,
            //'paged' => themex_paged(),
            //'meta_query' => array(
            //    array(
            //        'key' => '_thumbnail_id',
            //    ),
            //),
        );

        //$order = ThemexCore::getOption('courses_order', 'date');
        //if (in_array($order, array('rating', 'popularity'))) {
        //    $args['orderby'] = 'meta_value_num';
        //    $args['meta_key'] = '_course_' . $order;
        //}

        query_posts($args);
    }

    public $registro;

    /**
     * Retorna objeto do instrutor
     * @return Instrutor
     */
    function instrutor()
    {
        return Instrutores::getInstance()->getById($this->id_instrutor);
    }

    /**
     * Retorna objeto do local
     * @return Local
     * @deprecated
     */
    function local()
    {
       return $this->getLocal();
    }

    /**
     * Retorna objeto do local
     * @return Local
     */
    function localPagamento()
    {
        if ($this->id_local_pagamento != null)
            return Locais::getInstance()->getById($this->id_local_pagamento);
    }

    /**
     * Retorna objeto do organizador
     * @return Organizador
     */
    function organizador()
    {
        if (TGO_EVENTO_SINGLE_ORGANIZER===true){
            $organizador = Organizadores::getInstance()->getAll();
            if ($organizador==null)
                throw new Exception("SINGLE_ORGANIZER sem organizador");
            return $organizador[0];
        } else if ($this->id_organizador)
            return Organizadores::getInstance()->getById($this->id_organizador);
        else if (Organizadores::getInstance()->getCountAll() == 1) {
            $organizador = Organizadores::getInstance()->getAll();
            return $organizador[0];
        } else {
            // Ops!
            throw new Exception("Não foi possível encontrar uma definição de organizador para um evento");
        }
    }

    /*
     * Retorna o valor atual do evento - aplicando possíveis descontos
     */

    function getValorAtual()
    {
        // Obter Preço atual
        $valor = $this->valor();
        // Existem tickets de desconto na sessão?
        $valor = $this->getDescontoSessao($valor);
        return $valor;
    }

    function getDescontoSessaoArray()
    {
        return $_SESSION['Evento_' . $this->id]['descontos'];
    }

    function getDescontoSessao($valorCheio = null)
    {
        if ($valorCheio == null) $valorCheio = $this->valor();
        $valor = $valorCheio;
        if ($this->descontoSessao()) {
            $descontos = $this->getDescontoSessaoArray();
            foreach ($descontos as $desconto) {
                if ($desconto->desconto_por == 'percentual')
                    $valor = $valor - (($desconto->desconto * $valorCheio) / 100);
                else
                    $valor = $valor - $desconto->desconto;
            }
        }
        return $valor;
    }

    /*
     * Retorna se existem ou não descontos na sessão para este evento
     */

    function descontoSessao()
    {
        return count($_SESSION['Evento_' . $this->id]['descontos']) > 0;
    }

    /*
     * Retorna o valor atual do evento - aplicando possíveis descontos
     */

    function valor()
    {
        // Obter Preço atual
        $preco = $this->getPrecoAtual();
        if ($preco != null)
            return $preco->valor;
    }

    /**
     * Retorna o lote ainda disponível para inscrições
     */
    function loteDisponivel()
    {
        $preco = Precos::getInstance()->getByEventoAtual($this->id);
        if ($preco && $preco->getVagasRestantes() > 0)
            return $preco;
    }


    /**
     * Diz se a inscrição está aberta (por prazo e lotes)
     */
    function inscricaoAberta()
    {
        $abertas = ($this->inscricaoAbertaPrazo() || $this->preInscricao());
        $abertas = $abertas && $this->vagasDisponiveis() > 0;
        // Se tiver lotes, considerar por lotes, senão geral apenas
        //    return ($this->inscricaoAbertaLote() && $this->inscricaoAbertaPrazo()) || $this->preInscricao();

        return $abertas;
    }

    /**
     * Diz se é permitido confirmar as inscrições
     */
    public function permiteConfirmar()
    {
        return (($this->noFuturo() || $this->acontecendo()) && PLib::coalesce($this->fila_espera, 0) == 0);
    }

    /// Métodos para de data em timestamp

    private function dataInicioTimestamp()
    {
        $data = strtotime($this->data . " " . $this->hora);
        return $data;
    }

    private function dataInicioInscricoesTimestamp()
    {
        $data = strtotime($this->data_inicio_inscricoes);
        if ($data == false) $data = time() - 60 * 24;
        return $data;
    }

    private function dataFimInscricoesTimestamp()
    {
        $data = strtotime($this->data_fim_inscricoes);
        if ($data == false || $data > $this->dataInicioTimestamp())
            $data = $this->dataInicioTimestamp();
        return $data;
    }

    /// Métodos para data em formato string

    public function dataFimInscricoes()
    {
        return date("Y-m-d", $this->dataFimInscricoesTimestamp());
    }

    /**
     * Diz se a inscrição está aberta (por prazo e lotes)
     */
    function inscricaoAbertaPrazo()
    {
        $dataInicio = $this->dataInicioInscricoesTimestamp();
        $dataFim = $this->dataFimInscricoesTimestamp();
        $inscricaoAberta = $dataInicio < time() && $dataFim > time();
        // Lotes??
        return $inscricaoAberta;
    }

    /**
     * Diz se a inscrição está aberta (por prazo e lotes)
     */
    function inscricaoAbertaLote()
    {
        // Se existem lotes
        $loteDisponivel = $this->loteDisponivel;
        return $loteDisponivel;
    }

    /**
     * @return int Quantidade de visitantes ao evento
     */
    function qtdVisitantes()
    {
        return getVisitantesEvento($this->id);
    }

    /**
     * @return int Quantidade de pré-inscritos no evento
     */
    function qtdPreInscritos()
    {
        return Inscricoes::getInstance()->getPreInscritosCount($this->id);
    }

    /**
     * @return int Quantidade de inscritos no evento
     */
    function qtdInscritos()
    {
        return Inscricoes::getInstance()->getInscritosCount($this->id);
    }

    /**
     * @return int Quantidade de inscritos novos no evento
     */
    function qtdInscritosNovos()
    {
        $inscritosNovos = 0;
        $inscritos = Inscricoes::getInstance()->getByEvento($this->id);
        if ($inscritos)
            foreach ($inscritos as $inscrito) {
                $inscricoes = count(Inscricoes::getInstance()->getByPessoa($inscrito->id_pessoa));
                if ($inscricoes < 2)
                    $inscritosNovos++;
            }
        return $inscritosNovos;
    }

    /**
     * @return int Quantidade de inscritos presentes no evento
     */
    function qtdPresentes()
    {
        return Inscricoes::getInstance()->getPresentesCount($this->id);
    }

    function permitirPresenca()
    {
        return $this->comecaEmDuasHoras() || $this->aconteceuEmDoisDias();
    }

    /**
     * @return int Quantidade de cancelados/rejeitados no evento
     */
    function qtdCancelados()
    {
        return PLib::coalesce(Inscricoes::getInstance()->getRejeitadosCount($this->id), 0);
    }

    /**
     * @return int Quantidade de participantes não confirmados ainda no evento
     */
    function qtdNaoConfirmados()
    {
        return PLib::coalesce(Inscricoes::getInstance()->getNaoConfirmadosCount($this->id), 0);
    }

    /**
     * @return int Quantidade de confirmados no evento
     */
    function qtdConfirmados()
    {
        return PLib::coalesce(Inscricoes::getInstance()->getConfirmadosCount($this->id), 0);
    }

    public function qtdConfirmadosMulheres()
    {
        return PLib::coalesce(Inscricoes::getInstance()->getConfirmadosMulheresCount($this->id), 0);
    }

    public function qtdConfirmadosHomens()
    {
        return PLib::coalesce(Inscricoes::getInstance()->getConfirmadosHomensCount($this->id), 0);
    }

    /**
     * Obtem a quantidade de participantes na fila de espera
     */
    public function qtdFilaEspera()
    {
        return PLib::coalesce(Inscricoes::getInstance()->getFilaEsperaCount($this->id), 0);
    }

    function vagasDisponiveis()
    {
        if ($this->pago == 'pago' && $this->loteDisponivel())
            return $this->loteDisponivel()->getVagasRestantes();
        else if ($this->vagas == null)
            return true;
        else
            return $this->vagas - $this->qtdConfirmados();
    }

    function conversaoVisitantesInscritos()
    {
        if ($this->qtdVisitantes() == 0) return null;
        $per = round(($this->qtdInscritos() / $this->qtdVisitantes()) * 100);
        $per = $this->decorarConversãoVisitantesInscritos($per);
        return $per;
    }

    function conversaoVisitantesConfirmados()
    {
        if ($this->qtdVisitantes() == 0) return null;
        $per = round(($this->qtdConfirmados() / $this->qtdVisitantes()) * 100) . '%';
//        $per = $this->decorarConversãoVisitantesConfirmados($per);
        return $per;
    }

    function conversaoInscritosConfirmados()
    {
        if ($this->qtdInscritos() == 0) return null;
        $per = round(($this->qtdConfirmados() / $this->qtdInscritos()) * 100);
        $per = $this->decorarConversãoInscritosConfirmados($per);
        return $per;
    }

    function conversaoConfirmadosPresentes()
    {
        if ($this->qtdConfirmados() == 0) return null;
        return round(($this->qtdPresentes() / $this->qtdConfirmados()) * 100) . "%";
    }




    public function hasFilhos($futuro=true)
    {
        if ($this->_hasFilhos!=null) return $this->_hasFilhos;
        $sqlToday = date('Y-m-d');
        $sql = "
                SELECT wp_posts.ID
                FROM wp_posts
                LEFT JOIN wp_term_relationships ON wp_posts.ID=wp_term_relationships.object_id
                LEFT JOIN wp_postmeta ON wp_postmeta.post_id=wp_posts.id
                WHERE wp_posts.post_type = 'tgo_evento'
                AND wp_posts.post_status = 'publish'
                AND
                (wp_posts.ID in
                   ( SELECT post_id
                      FROM wp_postmeta
                      WHERE meta_key='id_evento_pai' and meta_value=$this->id
                   )
                )
                AND meta_key='data'
                AND CAST(wp_postmeta.meta_value as DATE)>='$sqlToday'";
        $results = wpdb()->get_results($sql);
        $this->_hasFilhos = count($results)>0;
        return $this->_hasFilhos;
    }

    //********* AÇÕES **************


    /**
     * Encerra as inscrições para o evento, colocando a data de agora na data final
     * e envia email para todos não confirmados com o email configurado no organizador
     */
//    function encerrarInscricoes()
//    {
//        // Registrar data final
//        update_post_meta($this->id, 'data_fimInscricoes', date('Y-m-d H:i:s'));
//        $enviados = 0;
//        // Obter inscritos, não confirmados
//        $inscritos = Inscricao::obterPorEvento($this->id);
//        foreach ($inscritos as $inscrito) {
//            if ($inscrito->confirmado == '') {
//                // Rejeitar inscrição
//                Inscricao::rejeitarInscricao($inscrito);
//                $enviados++;
//            }
//        }
//        echo "Foram enviados $enviados emails para as pessoas que não foram confirmadas para o evento.";
//    }

    /**
     * Retorna um array com as perguntas frequentes no formato pergunta=>resposta
     */
    function perguntas_frequentes()
    {
        if ($this->faq == null)
            return null;

        $perguntas = explode('- ', $this->faq);
        $return = array();
        foreach ($perguntas as $pergunta) {
            if (trim($pergunta) == "")
                continue;
            $pergunta = explode("\r", $pergunta);
            $return[$pergunta[0]] = $pergunta[1];
        }
        //var_dump($perguntas);
        //die();
        return $return;
    }


    function preInscricao()
    {
        return $this->confirmacao == "preinscricao";
    }

    function permalink()
    {
        return esc_url(apply_filters('the_permalink', get_permalink($this->id)));
    }

    function post_thumbnail($size = 'normal')
    {
        return get_the_post_thumbnail($this->id, $size);
    }

    /**
     * Ao converter para array tornar registro atributos do array
     * @return type
     */
    public function toArray()
    {
        return (array)$this;
    }

    public function __get($name)
    {
        if ($name == "id")
            return parent::__get("ID");
        else
            return parent::__get($name);
    }

    /**
     * @return Preco[]
     */
    public function getPrecos()
    {
        return Precos::getInstance()->getByEvento($this->id);
    }

    /**
     * @return Preco
     */
    public function getPrecoAtual()
    {
        return Precos::getInstance()->getByEventoAtual($this->id);
    }


    public function getValorPago()
    {
        $sql = "select coalesce(sum(ev_inscricoes.valor_pago),0) as valor_pago
                from ev_inscricoes
                where ev_inscricoes.id_evento=$this->id and ev_inscricoes.confirmado=1";
        $data = $this->wpdb()->get_row($sql);
        return $data->valor_pago;
    }

    /**
     * Valida todo o evento e retorna um array de erros, caso existam
     */
    public function getErros()
    {
//        PLib::var_dump($this);
        $return = array('erros'=>array(),'warning'=>array());
        if ($this->id_evento_pai) {
            $warning = "warning";
            $herdado = " (será herdado do evento pai)";
        } else {
            $warning = "error";
            $herdado = null;
        }

        if (!$this->preInscricao()) {
            if ($this->id_local == null)
                $return[$warning][] = "Evento sem local".$herdado.". <a href='post.php?action=edit&post=$this->id'>Editar Evento</a> ";
        } else {
            if ($this->fila_espera)
                $return['error'][] = "Não se pode fazer fila de espera em um evento na pré-inscrição.";
        }

        if ($this->pago==null){
            $return[$warning][] = "O evento é pago ou gratuito??".$herdado;
        }

        // Evento pago
        if ($this->pago == 'pago' && !$this->preInscricao()) {
            // Evento sem organizador
            if ($this->organizador() == null)
                $return[$warning][] = "Evento pago sem organizador".$herdado;

            // Sem preços?
            if ($this->getPrecos() == null)
                $return['error'][] = "Evento pago porém sem nenhum Preço definido. <a href='admin.php?page=Precos&id_evento=$this->id'>Definir preços</a>";

            // Evento com pagamento em dinheiro, sem local?
            if ($this->pago_dinheiro && $this->id_local_pagamento == null)
                $return[$warning][] = "Evento aceita pagamento em dinheiro porém não foi informado o local".$herdado;

            // Evento com pagamento em depósito sem dados de conta
            if ($this->pago_deposito && $this->organizador() != null && $this->organizador()->inscricao_dados_conta == '')
                $return[$warning][] = "Evento aceita pagamento em depóstio mas os dados da conta não estão informados no organizador".$herdado;

            // Integração com pagseguro configurada
            if ($this->pago_pagseguro && $this->id_integracao_pagseguro == null)
                $return[$warning][] = "Evento aceita pagamento por pagSeguro mas nenhuma Integração foi configurada".$herdado;
        }

        return $return;
    }

    /**
     * @return Integracao
     */
    public function getIntegracaoPagSeguro()
    {
        return Integracoes::getInstance()->getById($this->id_integracao_pagseguro);
    }

    /**
     * @return Integracao
     */
    public function getIntegracaoCielo()
    {
        return Integracoes::getInstance()->getById($this->id_integracao_cielo);
    }

    private function decorarConversãoVisitantesInscritos($per)
    {
        $cor = 'black';
        if ($per < 15) $cor = 'green';
        if ($per < 10) $cor = 'blue';
        if ($per < 7) $cor = 'orange';
        if ($per < 5) $cor = 'red';
        return '<span style="color: ' . $cor . '">' . $per . '%</span>';
    }

    private function decorarConversãoInscritosConfirmados($per)
    {
        $cor = 'green';
        if ($per < 50) $cor = 'blue';
        if ($per < 35) $cor = 'orange';
        if ($per < 20) $cor = 'red';
        return '<span style="color: ' . $cor . '">' . $per . '%</span>';
    }

    /**
     * @return Questionario
     */
    public function getQuestionarioAvaliacao()
    {
        return Questionarios::getInstance()->getById(PLib::coalesce($this->id_questionario,1));
    }

    /**
     * Diz se existe um arquivo de certificado para este evento
     */
    public function hasCertificadoArquivo()
    {
        return file_exists($this->getCertificadoArquivo());
    }

    public function getCertificadoArquivo()
    {
        if ($this->certificado_arquivo)
            return $this->certificado_arquivo;
        else
            return ABSPATH . '/wp-content/uploads/certificado_' . $this->id . '.png';
    }

    public function getCertificadoOriginalUrl()
    {
        return home_url('/wp-content/uploads/'.$this->getCertificadoParcialUpload());
    }

    private function getCertificadoParcialUpload()
    {
        if ($this->certificado_arquivo!=null)
            $file=$this->certificado_arquivo;
        else
            $file=ABSPATH . '/wp-content/uploads/certificado_' . $this->id . '.png';
        if (file_exists($file)){
            $file = substr($file,strpos($file,'/wp-content/uploads')+20);
        }
        return $file;
    }

    public function hasCertificadoArquivoMiniatura()
    {
        return file_exists($this->getCertificadoArquivoMiniatura());
    }

    public function getCertificadoArquivoMiniatura()
    {
        return ABSPATH . '/wp-content/uploads/certificado_' . $this->id . '_mini.png';
    }

    public function getCertificadoArquivoMiniaturaUrl()
    {
        return home_url('/wp-content/uploads/certificado_' . $this->id . '_mini.png');
    }

    /**
     * Diz se este evento tem alguma avaliação
     */
    public function hasAvaliacao()
    {
        return Respostas::getInstance()->hasByEvento($this->id);
    }

    /** Retorna a média das avaliações, da pergunta informada */
    public function getAvaliacaoMediaPergunta($id_pergunta)
    {
        return Respostas::getInstance()->getMediaPerguntaEvento($this->id, $id_pergunta);
    }

    public function getLinkInscricao()
    {
        return get_permalink($this->id).'/?inscricao=1';
    }

    public function isPago()
    {
        return $this->pago=='pago';
    }

    /**
     * @return Local
     */
    public function getLocal()
    {
        if ($this->id_local != null)
            return Locais::getInstance()->getById($this->id_local);
    }

    public function getStructuredDataJs()
    {
        $place=null;

        if ($this->id_local){
            $local = $this->getLocal();
            $place = '
             "location" : {
                "@type" : "Place",
                "name" : "'.$local->titulo.'",
                "address" : "'.str_replace("\r\n"," ",$local->endereco).'",
                "telephone" : "'.$local->telefone.'" ';

            if ($local->latitude)
                $place.=',
                "geo": {
                    "@type": "GeoCoordinates",
                    "latitude": "'.$local->latitude.'",
                    "longitude": "'.$local->longitude.'"
                  }
                ';
            $place.='
              }
            ';
        }

        $offers=null;
        if ($this->isPago() && $this->noFuturo()){
            $val = number_format($this->getValorAtual(),2,'.','');
            $offers = '
            "offers" :{
                "price" : "'.$val.'",
                "priceCurrency" : "BRL",
                "url" : "'.$this->permalink().'"
            }
            ';
        }

//        var_dump($this->data);
//        var_dump($this->hora);
//        var_dump($this->inicio());

        return '
        <script type="application/ld+json">
            {
              "@context": "http://schema.org",
              "@type": "Event",
              "name": "'.$this->titulo.'",
              "startDate" : "'.PLib::date_iso8601($this->inicio()).'",
               "url" : "'.$this->permalink().'"
              '.($place? ','.$place : '').'
              '.($offers? ','.$offers : '').'
            }
        </script>';
    }

    public function getTags($nameApenas=true)
    {
        $tags = wp_get_post_tags($this->id);
        if (count($tags)>0){
            $nTags = array();
            foreach($tags as $tag){
                $nTags[]=$tag->name;
            }
            return $nTags;
        } else
            return null;
    }

}
