<?php

class Local extends \TiagoGouvea\WPDataMapper\WPSimpleMapper {
    public $id;
    public $titulo;
    public $endereco;
    public $cidade;
    public $telefone;
    public $latitude;
    public $longitude;
    public $site;

    public function getLinkMaps()
    {
        return "http://www.google.com/maps/place/".$this->latitude.",".$this->longitude;
    }
}
