<?php
use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Descontos extends WPSimpleDAO {

    function init(){
        parent::_init(
            "ev_descontos_eventos",
            'Desconto',
            "titulo");
    }

    /**
     * @return Descontos
     */
    static function getInstance(){
        return parent::getInstance();
    }

    /**
     * @param $idEvento
     * @param $ticket
     * @return Desconto
     */
    function getByEventoTicket($idEvento,$ticket) {
        $row = $this->wpdb()->get_row("SELECT * FROM ev_descontos_eventos WHERE id_evento=$idEvento and ticket = '$ticket'");
        if ($row)
            return $this->populate($row);
    }

    /**
     * Retorna o ticket se existir no banco
     * @param $ticket
     * @return Desconto
     */
    public function getByTicket($ticket)
    {
        $row = $this->wpdb()->get_row("SELECT * FROM ev_descontos_eventos WHERE ticket = '$ticket'");
        if ($row)
            return $this->populate($row);
    }




}