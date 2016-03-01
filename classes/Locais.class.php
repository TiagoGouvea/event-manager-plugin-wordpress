<?php

use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Locais extends WPSimpleDAO{
    function init(){
        if (!$this->initialized)
            parent::_init(
                "ev_locais",
                Local,
                "titulo");
    }

    /**
     * @return Locais
     */
    static function getInstance(){
        return parent::getInstance();
    }


}