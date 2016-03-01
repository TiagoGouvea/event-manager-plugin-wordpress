<?php

class Resposta extends \TiagoGouvea\WPDataMapper\WPSimpleMapper {
    public $id;
    public $id_questionario;
    public $id_questionario_pergunta;
    public $id_pessoa;
    public $id_inscricao;
    public $dat_resposta;
    public $resposta;


    /**
     * @return Pergunta
     */
    public function pergunta()
    {
        return Perguntas::getInstance()->getById($this->id_questionario_pergunta);
    }


}
