<?php

use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Respostas extends WPSimpleDAO{
    function init(){
        if (!$this->initialized)
            parent::_init(
                "ev_questionarios_respostas",
                Resposta,
                "id_questionario_pergunta");
    }

    /**
     * @return Respostas
     */
    static function getInstance(){
        return parent::getInstance();
    }

    public function createResposta(Pergunta $pergunta, Inscricao $inscricao, $resp)
    {
        // Certificar primeiro
        $resposta = Respostas::getInstance()->getByPerguntaInscricao($pergunta->id,$inscricao->id);
        $inserir = $resposta == null;

        // Criar
        if ($inserir)
            $resposta = new Resposta();

        $resposta->id_questionario=$pergunta->id_questionario;
        $resposta->id_questionario_pergunta=$pergunta->id;
        $resposta->id_pessoa = $inscricao->id_pessoa;
        $resposta->id_inscricao = $inscricao->id;
        $resposta->resposta = $resp;

        if ($inserir)
            $resposta = $this->insert($resposta);
        else
            $resposta = $this->save($resposta->id,$resposta);

        return $resposta;
    }

    /**
     * Diz se a inscrição informada tem alguma resposta
     * @param $idInscricao
     * @return null
     */
    public function hasByInscricao($idInscricao)
    {
        $sql = "select count(*) as count from ev_questionarios_respostas where id_inscricao=$idInscricao";
        $row = $this->wpdb()->get_row($sql);
        if ($row)
            return $row->count>0;
    }

    /**
     * Diz se o evento tem alguma resposta
     * @param $idInscricao
     * @return null
     */
    public function hasByEvento($idEvento)
    {
        $sql = "select count(*) as count
                from ev_questionarios_respostas
                left join ev_inscricoes i on i.id=ev_questionarios_respostas.id_inscricao
                where i.id_evento=$idEvento";
        $row = $this->wpdb()->get_row($sql);
        if ($row)
            return $row->count>0;
    }

    /**
     * Calcula a avaliação média de uma pergunta em um evento
     * @param $id
     * @param $id_pergunta
     */
    public function getMediaPerguntaEvento($id_evento, $id_pergunta)
    {
        $sql = "select avg(resposta) as avg
                from ev_questionarios_respostas
                left join ev_inscricoes on ev_inscricoes.id=ev_questionarios_respostas.id_inscricao
                where id_questionario_pergunta=$id_pergunta and id_evento=$id_evento $idQuestionarioPergunta";
        $row = $this->wpdb()->get_row($sql);
        if ($row)
            return $row->avg;
    }

    /**
     * Retorna a resposta de uma pessoa a uma pergunta
     * @param $idQuestionarioPergunta
     * @param $idPessoa
     * @return Resposta
     */
    private function getByPerguntaPessoa($idQuestionarioPergunta,$idPessoa)
    {
        $sql = "select * from ev_questionarios_respostas where id_questionario_pergunta=$idQuestionarioPergunta and id_pessoa=$idPessoa";
        $row = $this->wpdb()->get_row($sql);
        if ($row)
            return $this->populate($row);
    }

    /**
     * Retorna a resposta de uma inscricao a uma pergunta
     * @param $idQuestionarioPergunta
     * @param $idInscricao
     * @return Resposta
     */
    private function getByPerguntaInscricao($idQuestionarioPergunta,$idInscricao)
    {
        $sql = "select * from ev_questionarios_respostas where id_questionario_pergunta=$idQuestionarioPergunta and id_inscricao=$idInscricao";
        $row = $this->wpdb()->get_row($sql);
        if ($row)
            return $this->populate($row);
    }

    /**
     * Retorna a resposta com base na inscrição e pergunta
     * @param $idInscricao
     * @param $idQuestionarioPergunta
     * @return null
     */
    public function getByInscricaoPergunta($idInscricao,$idQuestionarioPergunta)
    {
        $sql = "select * from ev_questionarios_respostas where id_inscricao=$idInscricao and id_questionario_pergunta=$idQuestionarioPergunta";
        $row = $this->wpdb()->get_row($sql);
        if ($row)
            return $this->populate($row);
    }


}