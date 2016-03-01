<?php

use TiagoGouvea\PLib;
use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Organizadores extends WPSimpleDAO
{
    private static $_extrasTitulosTipo;

    public static $organizadoresExternos = array('http://emjuizdefora.com/gbgjf', 'http://emjuizdefora.com/gdgjf', 'http://www.inspirardigital.com.br', 'http://www.cafedigitaljf.com.br', 'http://eventos.phormar.com.br');

    function init()
    {
        parent::_init(
            "ev_organizadores",
            Organizador,
            "titulo");
    }

    /**
     * @return Organizadores
     */
    static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Workaround que retorna todos os extras de todos os eventos, divididos por tipo
     */
    public function getExtrasTitulosTipos()
    {
        if (self::$_extrasTitulosTipo != null) return self::$_extrasTitulosTipo;
        $return = null;
        $sql = "SELECT meta_value AS extra FROM wp_postmeta WHERE meta_key='campos_extras' ORDER BY meta_id";
        $result = $this->wpdb()->get_results($sql);
        if ($result && count($result) > 0) {
            $return = array();
            foreach ($result as $extras) {
                $extras = explode("\r\n", PLib::unicode_to_utf8($extras->extra));
                foreach ($extras as $extra) {
                    $chave = substr($extra, 0, strpos($extra, '/'));
                    $titulo = str_replace($chave . '/', '', $extra);
                    if (strpos($titulo, '[ ]') > 0)
                        $tipo = "bool";
                    else if (strpos($titulo, '[file]') > 0)
                        $tipo = "file";
                    else
                        $tipo = 'string';
                    if (strpos($titulo, ' [') > 0)
                        $titulo = substr($titulo, 0, strpos($titulo, ' ['));
                    if ($chave != null && $titulo != null) {
                        $return[$chave] = json_decode(json_encode(array('Chave' => $chave, 'Titulo' => $titulo, 'Tipo' => $tipo)));;
                    }
                    //              var_dump($extra);
                    //              var_dump($chave);
                    //              $titulo
                }
            }
//            \TiagoGouvea\PLib::var_dump($return);
//            die();
        }

        self::$_extrasTitulosTipo = $return;

        return $return;
    }

    public function getTodosInscritos()
    {
        return $this->wpdb()->get_results("SELECT distinct id_pessoa, ev_pessoas.nome, ev_pessoas.email "
            . "FROM ev_inscricoes "
            . "LEFT JOIN ev_pessoas on ev_pessoas.id=ev_inscricoes.id_pessoa "
            . "ORDER BY ev_pessoas.nome");
    }

    public function getCountTodosInscritos()
    {
        $qtd = $this->wpdb()->get_results("SELECT count(DISTINCT id_pessoa) AS qtd FROM ev_inscricoes");
        return $qtd[0]->qtd;
    }
}