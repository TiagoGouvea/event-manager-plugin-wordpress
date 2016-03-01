<?php

class Questionario extends \TiagoGouvea\WPDataMapper\WPSimpleMapper {
    public $id;
    public $titulo;

    public function getPerguntas()
    {
        return Perguntas::getInstance()->getBy('id_questionario',$this->id,false);
    }
}
