<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 30/10/15
 * Time: 11:44
 */

use TiagoGouvea\PHPEnum\PHPEnum;

class Enum extends PHPEnum{

    // Inscrição
    const INSCRICAO_REALIZADA = 0;
    const INSCRICAO_CONFIRMADA = 1;
    const INSCRICAO_CANCELADA = 2;

    public static function get($value)
    {
        return parent::_get(__CLASS__,$value);
    }
}