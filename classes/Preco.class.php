<?php

use TiagoGouvea\WPDataMapper\WPSimpleMapper;

class Preco extends WPSimpleMapper {
    public $id;
    public $id_evento;
    public $titulo;
    public $vagas;
    public $valor;
    public $encerrado;
    private $_qtd_confirmados;
    private $valor_pago;

    private function getConfirmados()
    {
        // Obter inscrições confirmadas, pagas, por preço
        /* @var $wpdb wpdb */
        $sql = "select ep.titulo, coalesce(count(ev_inscricoes.id),0) as qtd_confirmados, coalesce(sum(ev_inscricoes.valor_pago),0) as valor_pago
                from ev_inscricoes
                left join ev_precos_eventos ep on ep.id=ev_inscricoes.id_preco
                where ev_inscricoes.id_preco=$this->id and ev_inscricoes.confirmado=1";
        $data = $this->wpdb()->get_row($sql);
        $this->_qtd_confirmados = $data->qtd_confirmados;
        $this->valor_pago = $data->valor_pago;
    }

    public function getVagasRestantes()
    {
        return ($this->vagas - $this->getQtdConfirmados());
    }

    public function getQtdConfirmados()
    {
        if ($this->_qtd_confirmados==null)
            self::getConfirmados();
        return $this->_qtd_confirmados;
    }

    public function getValorPago()
    {
        if ($this->_qtd_confirmados==null)
            self::getConfirmados();
        return $this->valor_pago;
    }

    public function getQtdInscritos()
    {
        // Obter inscrições totais (não canceladas)
        /* @var $wpdb wpdb */
        $sql = "select coalesce(count(ev_inscricoes.id),0) as qtd_inscritos
                from ev_inscricoes
                left join ev_precos_eventos ep on ep.id=ev_inscricoes.id_preco
                where ev_inscricoes.id_preco=$this->id and coalesce(ev_inscricoes.confirmado,1)<>0";
        $data = $this->wpdb()->get_row($sql);
        return $data->qtd_inscritos;
    }

    public function getValorAtual()
    {
        // Existem tickets de desconto na sessão?
        $valor = $this->evento()->getDescontoSessao($this->valor);
        return $valor;
    }

    private function evento()
    {
        return Eventos::getInstance()->getById($this->id_evento);
    }

}
