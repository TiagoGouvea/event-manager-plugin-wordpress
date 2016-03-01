<?php

use TiagoGouvea\WPDataMapper\WPSimpleMapper;

class Instrutor extends WPSimpleMapper {
    public $ID;
    public $display_name;

    /**
     * @return Pessoa
     */
    function pessoa(){
        $pessoa = Pessoas::getInstance()->getBy('id_User',$this->ID);
//        var_dump($pessoa);
        return $pessoa;
    }
}
