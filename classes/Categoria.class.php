<?php

use TiagoGouvea\WPDataMapper\WPSimpleMapper;

class Categoria extends WPSimpleMapper {
    public $id;
    public $id_evento;
    public $id_preco;
    public $titulo;
    public $condicao;
    public $_permiteInscricao;

    /**
     * Retorna o objeto preço atual da categoria
     * @return Preco
     */
    public function getPreco(){
        if ($this->id_preco==null) return;
        return Precos::getInstance()->getById($this->id_preco);
    }

}
