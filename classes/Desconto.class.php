<?php
use TiagoGouvea\WPDataMapper\WPSimpleMapper;

class Desconto extends WPSimpleMapper {
    public $id;
    public $id_evento;
    public $id_pessoa;
    public $ticket;
    public $desconto;
    public $desconto_por;
    public $quantidade;
    public $data_criacao;
    public $data_validade;

    public $_qtd_confirmados;

    private function getConfirmados()
    {
        // Obter inscrições confirmadas, pagas
        /* @var $wpdb wpdb */
        $sql = "select coalesce(count(ev_desconto_inscricao.id),0) as _qtd_confirmados
                from ev_desconto_inscricao
                left join ev_inscricoes on ev_inscricoes.id=ev_desconto_inscricao.id_inscricao
                where id_desconto=$this->id and ev_inscricoes.confirmado=1";
        $data = $this->wpdb()->get_row($sql);
        $this->_qtd_confirmados = $data->_qtd_confirmados;
    }

    public function getQuantidadeRestante()
    {
        return ($this->quantidade - $this->getQtdConfirmados());
    }

    public function getQtdConfirmados()
    {
        if ($this->_qtd_confirmados==null)
            self::getConfirmados();
        return $this->_qtd_confirmados;
    }

    public function getQtdInscritos()
    {
        // Obter inscrições totais (não canceladas)
        /* @var $wpdb wpdb */
        $sql = "select coalesce(count(ev_desconto_inscricao.id),0) as qtd_inscritos
                from ev_desconto_inscricao
                left join ev_inscricoes on ev_inscricoes.id=ev_desconto_inscricao.id_inscricao
                where id_desconto=$this->id and coalesce(ev_inscricoes.confirmado,1)<>0";
        $data = $this->wpdb()->get_row($sql);
        return $data->qtd_inscritos;
    }

    public function pessoa()
    {
        return Pessoas::getInstance()->getById($this->id_pessoa);
    }
}