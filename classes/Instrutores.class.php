<?php

use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Instrutores extends WPSimpleDAO{
    function init(){
        if (!$this->initialized)
            parent::_init(
                "wp_users",
                Instrutor,
                "display_name");
    }

    /**
     * @return Instrutores
     */
    static function getInstance(){
        return parent::getInstance();
    }

    function getTodosArray(){
        $registros = $this->getAll();
        $array=array();
        foreach ($registros as $registro)
            $array[$registro->ID]=$registro->display_name. ($registro->pessoa()==null ? ' (vincule o usuário ao cadastro da pessoa!)' : '');
        return $array;
    }


}