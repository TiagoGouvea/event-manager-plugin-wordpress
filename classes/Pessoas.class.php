<?php

use TiagoGouvea\WPDataMapper\WPSimpleDAO;

class Pessoas extends WPSimpleDAO{
    static $networks = array('facebook'=>'Facebook','twitter'=>'Twitter','pinterest'=>'Pinterest','instagram'=>'Instagram','gplus'=>'Google+','skype'=>'Skype','linkedin'=>'Linkedin','site'=>'Site ou Blog','gravatar'=>'Gravatar','klout'=>'Klout','foursquare'=>'Foursquare','tumblr'=>'Tumblr','soundcloud'=>'SoundCloud','vimeo'=>'Vimeo','stackoverflow'=>'Stackoverflow','github'=>'GitHub','youtube'=>'YouTube');
    static $networksGreat = array('facebook'=>'Facebook','twitter'=>'Twitter','pinterest'=>'Pinterest','instagram'=>'Instagram','gplus'=>'Google+','skype'=>'Skype','linkedin'=>'Linkedin','site'=>'Site ou Blog','foursquare'=>'Foursquare','tumblr'=>'Tumblr','soundcloud'=>'SoundCloud','vimeo'=>'Vimeo','stackoverflow'=>'Stackoverflow','github'=>'GitHub','youtube'=>'YouTube');

    function init(){
        if (!$this->initialized)
            parent::_init(
                "ev_pessoas",
                'Pessoa',
                "nome");
    }

    /**
     * @return Pessoas
     */
    static function getInstance(){
        return parent::getInstance();
    }

    /**
     * Obtem uma pessoa buscando pelo email ou cpf com md5
     * @return Pessoa
     */
    public function getByMd5($md5)
    {
        $sql = "select * from ev_pessoas where cpf='$md5' or email='$md5' or md5(cpf)='$md5' or md5(email)='$md5'";
        /* @var $wpdb wpdb */
        $result = $this->wpdb()->get_row($sql);
        if ($result)
            return self::populate($result);
    }

    /**
     * Obtem uma pessoa buscando pelo email
     * @return Pessoa
     */
    public function getByEmail($email)
    {
        $sql = "select * from ev_pessoas where email='$email'";
        /* @var $wpdb wpdb */
        $result = $this->wpdb()->get_row($sql);
        if ($result)
            return self::populate($result);
    }

    public function getTodosArray()
    {
        $registros = $this->getAll();
        $array=array();
        foreach ($registros as $registro)
            $array[$registro->id]=$registro->nome;
        return $array;
    }

}