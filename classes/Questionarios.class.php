<?php

use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Questionarios extends WPSimpleDAO{
    function init(){
        if (!$this->initialized)
            parent::_init(
                "ev_questionarios",
                Questionario,
                "titulo");
    }

    /**
     * @return Locais
     */
    static function getInstance(){
        return parent::getInstance();
    }


}