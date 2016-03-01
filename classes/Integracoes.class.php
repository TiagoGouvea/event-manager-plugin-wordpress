<?php

use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Integracoes extends WPSimpleDAO{

    function init(){
        parent::_init(
            "ev_integracoes",
            Integracao,
            "titulo");
    }

    /**
     * @return Integracoes
     */
    static function getInstance(){
        return parent::getInstance();
    }

    /**
     * @param $servico
     * @return Integracao
     */
    public function getByServico($servico)
    {
        return $this->getBy('Servico',$servico,false);
    }

    public function hasByServico($servico)
    {
        return $this->getBy('Servico',$servico,false)!=null;
    }

}
