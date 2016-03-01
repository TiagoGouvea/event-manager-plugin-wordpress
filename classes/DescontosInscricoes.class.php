<?php
use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class DescontosInscricoes extends WPSimpleDAO {

    function init(){
        parent::_init(
            "ev_desconto_inscricao",
            'DescontoInscricao',
            "id");
    }

    /**
     * @return DescontosInscricoes
     */
    static function getInstance(){
        return parent::getInstance();
    }

    public function hasByDescontoInscricao($id_desconto,$id_inscricao)
    {
        /* @var $wpdb wpdb */
        $sql = "select coalesce(count(*),0) as count from ev_desconto_inscricao where id_desconto=$id_desconto and id_inscricao=$id_inscricao";
        $row = $this->wpdb()->get_row($sql);
        return $row->count>0;
    }

}