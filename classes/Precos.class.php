<?php
use TiagoGouvea\WPDataMapper\WPSimpleDAO;

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 03/04/15
 * Time: 18:17
 */

class Precos extends WPSimpleDAO {

    function init(){
        parent::_init(
            "ev_precos_eventos",
            'Preco',
            "titulo");
    }

    /**
     * @return Precos
     */
    static function getInstance(){
        return parent::getInstance();
    }

    public function getByEvento($id_evento)
    {
        return $this->getBy('id_evento',$id_evento,false);
    }

    /**
     * @param $id_evento
     * @return Preco
     */
    public function getByEventoAtual($id_evento)
    {
        $sql = "select * from ev_precos_eventos where id_evento=$id_evento and coalesce(encerrado,0)=0";
        $result = $this->wpdb()->get_row($sql);
        if ($result)
            return self::populate($result);
    }





//    static function obterAtualPorEvento($idEvento) {
//        global $wpdb;
//        return $wpdb->get_row("SELECT * FROM ev_precos_eventos WHERE id_evento = $idEvento and encerrado<>1 order by id limit 1");
//    }
//
//    static function obterMaiorPorEvento($idEvento) {
//        global $wpdb;
//        return $wpdb->get_row("SELECT max(valor) as valor FROM ev_precos_eventos WHERE id_evento = $idEvento");
//    }

}