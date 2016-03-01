<?php
use TiagoGouvea\WPDataMapper\WPSimpleDAO;

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 03/04/15
 * Time: 18:17
 */

class Categorias extends WPSimpleDAO {

    function init(){
        parent::_init(
            "ev_categorias_eventos",
            'Categoria',
            "titulo");
    }

    /**
     * @return Categorias
     */
    static function getInstance(){
        return parent::getInstance();
    }

    /**
     * @param $id_evento
     * @return array Categoria
     */
    public function getByEvento($id_evento)
    {
        return $this->getBy('id_evento',$id_evento,false);
    }
}