<?php

use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Perguntas extends WPSimpleDAO{
    function init(){
        if (!$this->initialized)
            parent::_init(
                "ev_questionarios_perguntas",
                Pergunta,
                "titulo");
    }

    /**
     * @return Perguntas
     */
    static function getInstance(){
        return parent::getInstance();
    }

    public function getByQuestionario($id_questionario)
    {
        $sql = "select *
                from ev_questionarios_perguntas
                where id_questionario=$id_questionario
                order by id";
        $result = $this->wpdb()->get_results($sql);
        if ($result)
            return $this->toObject($result);
    }


}