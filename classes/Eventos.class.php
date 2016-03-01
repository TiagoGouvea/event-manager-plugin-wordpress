<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use TiagoGouvea\WPDataMapper\WPSimpleDAO;

/**
 * Description of Eventos
 *
 * @author Tiago
 */
class Eventos extends WPSimpleDAO {

    private static $eventos;



    function init(){
        parent::_init(
            "wp_posts",
            'Evento',
            "post_title",
            "post_type='tgo_evento'");
    }

    /**
     * @return Eventos
     */
    static function getInstance(){
        return parent::getInstance();
    }


    // Popular evento com alguns critérios extras
    public function populate($data,$obj=null)
    {
        if (isset($data['ID']))
            $data = self::mergeMetaData($data,$data['ID']);
        /* @var $obj Evento */
        $obj = parent::populate($data);
        if ($data['ID'])
            $obj->id = $data['ID'];
        if ($data['post_title'])
            $obj->titulo = $data['post_title'];
        if ($data['post_status'])
            $obj->publicado = $data['post_status']=='publish';
        if ($data['post_status'])
            $obj->rascunho = $data['post_status']=='draft';
        if ($data['post_excerpt'])
            $obj->excerto = $data['post_excerpt'];
        return $obj;
    }

    /**
     * @param $id
     * @param bool $trazerPai
     * @return Evento
     */
    public function getById($id,$trazerPai=true){
        $evento = parent::getById($id);
        if ($trazerPai && $evento->id_evento_pai){
            $eventoPai = parent::getById($evento->id_evento_pai);
            $evento = self::mergeEventos($eventoPai,$evento);
//            var_dump($evento);
        }

//        \TiagoGouvea\PLib::var_dump($evento);
//        if (get_post_type($id)=='tgo_evento')
        return $evento;
    }

    /**
     * Faz merge do evento pai com o filho, todos os dados do filho existentes são mantidos
     * @param $eventoPai
     * @param $evento
     */
    private static function mergeEventos($eventoPai, $evento)
    {
        $vars = get_object_vars($eventoPai);
        foreach($vars as $var=>$value)
//            var_dump($var);
//            var_dump($value);
            if (($evento->$var==null || $evento->$var=='') && $value!=null)
                $evento->$var = $value;
//            var_dump($var);
        return $evento;
    }

    public function insert(&$obj){
        // Incluir post do evento
        /* @var $wpdb wpdb */
        $values = array('post_title'=>$obj->titulo,'post_status'=>'publish','post_type'=>'tgo_evento','post_date'=>'current_timestamp','post_date_gmt'=>'current_timestamp');
        $filter = function_exists('wp_db_null_value') ? 'wp_db_null_value' : 'DB::wpDbNullValue';
        add_filter( 'query', $filter );
        $ok = $this->wpdb()->insert($this->tableName, $values);
        if ($ok===false){
            throw new \Exception($this->wpdb()->last_error);
        }
        $id = $this->wpdb()->insert_id;
        remove_filter( 'query', $filter );
        $evento = $this->getById($id);

        // Obter permalink
        get_sample_permalink($id,$evento->titulo);

        $post_id = $id;

        // Descricao2
        meta_update($post_id, 'descricao_2', false);
        meta_update($post_id, 'descricao_3', false);
        // Publico Alvo
        meta_update($post_id, 'publico_alvo');
        // Local
        meta_update($post_id, 'id_local', false);
        // Organizador
        if (get_option('singleOrganizer',true) && get_option('idSingleOrganizer',null)!=null)
            update_post_meta($post_id, 'id_organizador',get_option('idSingleOrganizer'));
        else
            meta_update($post_id, 'id_organizador', false);
        meta_update($post_id, 'data');
        meta_update($post_id, 'hora');
        meta_update($post_id, 'data_fim');
        meta_update($post_id, 'hora_fim');
        meta_update($post_id, 'vagas');
        meta_update($post_id, 'pago');
        meta_update($post_id, 'valor');

        return $this->getById($id);
    }

    /**
     * @param null $orderBy
     * @return Evento[]
     */
    public function getAll($orderBy=null){
        if (self::$eventos==null){
            self::$eventos = parent::getAll($orderBy);
        }
        self::$eventos = self::ordenar(self::$eventos);
        return self::$eventos;
    }



    function getTodos($apenasFuturo=true,$ordenar=true,$orderBy='post_date desc',$limit=null,$where=null,$id_organizador=null) {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return=array();
        foreach ($eventos as $evento) {
            if ($evento->publicado && $id_organizador!=null && ($id_organizador==null || $evento->id_organizador==$id_organizador))
                $return[] = $evento;
        }

        // Ordenar
        if ($ordenar)
            $return = self::ordenar($return);

        return $return;
    }

    function getRascunhos($ordenar=true,$orderBy='post_date desc',$limit=null,$where=null,$id_organizador=null) {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return=array();
        foreach ($eventos as $evento) {
            if (!$evento->publicado && ($id_organizador==null || ($id_organizador==null || $evento->id_organizador==$id_organizador)))
                $return[] = $evento;
        }

        // Ordenar
        if ($ordenar)
            $return = self::ordenar($return);

        return $return;
    }


    function getAcontecendo($ordenar=true,$orderBy='post_date desc',$limit=null,$where=null,$id_organizador=null) {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return=array();
        /* @var $eventos Evento[] */
        foreach ($eventos as $evento) {
            if ($evento->publicado && $evento->acontecendo())
                if ($id_organizador==null || ($id_organizador==null || $evento->id_organizador==$id_organizador))
                    $return[] = $evento;
        }

        // Ordenar
        if ($ordenar)
            $return = self::ordenar($return);

        return $return;
    }

    function getAtuais($ordenar=true,$limit=null,$where=null) {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return=array();
        foreach ($eventos as $evento) {
            if ($evento->publicado && ($evento->noFuturo() || $evento->acontecendo()) && $evento->data!="")
                $return[] = $evento;
        }

        // Ordenar
        if ($ordenar)
            $return = self::ordenar($return);

        return $return;
    }

    function getAtuaisERecentes($ordenar=true,$limit=null,$where=null) {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return=array();
        /* @var $evento Evento */
        foreach ($eventos as $evento) {
            if (!$evento->preInscricao() && ($evento->noFuturo() || $evento->acontecendo() || $evento->aconteceuEmCincoDias()) && $evento->data!=""  && $evento->publicado)
                $return[] = $evento;
        }

        // Ordenar
        if ($ordenar)
            $return = self::ordenar($return,true);
        else
            $return = self::ordenar($return);

        return $return;
    }

    /**
     * @param bool|true $ordenar
     * @param string $orderBy
     * @param null $limit
     * @param null $where
     * @return Evento[]
     */
    function getPassados($ordenar=true,$orderBy='post_date desc',$limit=null,$where=null) {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return=array();
        foreach ($eventos as $evento) {
            /* @var $evento Evento */
            if ($evento->publicado && $evento->realizado())
                $return[] = $evento;
        }

        // Ordenar
        if ($ordenar)
            $return = self::ordenar($return,false);

        return $return;
    }

    function getFuturos($ordenar=true,$limit=null,$where=null) {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return = array();
        foreach ($eventos as $evento) {
            if ($evento->publicado && $evento->noFuturo())
                $return[] = $evento;
        }

        // Ordenar
        $return = self::ordenar($return,$ordenar);

        return $return;
    }

    public function getFuturosInscricao()
    {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return = array();
        foreach ($eventos as $evento) {
            if ($evento->publicado && $evento->noFuturo() && !$evento->preInscricao())
                $return[] = $evento;
        }

        // Ordenar
        $return = self::ordenar($return);

        return $return;
    }

    public function getPreInscricao()
    {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return = array();
        foreach ($eventos as $evento) {
            if ($evento->preInscricao() && $evento->publicado)
                $return[] = $evento;
        }

        // Ordenar
        $return = self::ordenar($return);

        return $return;
    }


    public function getFuturosByCategoria($idCategoria){
        $sqlToday = date('Y-m-d');
        $sql = "
                SELECT wp_posts.post_title, wp_posts.ID, wp_terms.name FROM wp_posts
                LEFT JOIN wp_term_relationships ON wp_posts.ID=wp_term_relationships.object_id
                LEFT JOIN wp_term_taxonomy ON wp_term_taxonomy.term_taxonomy_id=wp_term_relationships.term_taxonomy_id
                LEFT JOIN wp_terms ON  wp_terms.term_id=wp_term_taxonomy.term_id
                LEFT JOIN wp_postmeta ON wp_postmeta.post_id=wp_posts.id AND wp_postmeta.meta_key='data'
                WHERE wp_posts.post_type = 'tgo_evento'
                AND wp_posts.post_status = 'publish'
                AND wp_term_taxonomy.term_id = $idCategoria
                AND wp_postmeta.meta_value>='$sqlToday'";
//        var_dump($sql);

//                SELECT wp_posts.post_title, wp_posts.ID, wp_terms.name
//                FROM wp_posts
//                LEFT JOIN wp_term_relationships ON wp_posts.ID=wp_term_relationships.object_id
//                LEFT JOIN wp_term_taxonomy ON wp_term_taxonomy.term_taxonomy_id=wp_term_relationships.term_taxonomy_id
//                LEFT JOIN wp_terms ON  wp_terms.term_id=wp_term_taxonomy.term_id
//                LEFT JOIN wp_postmeta ON wp_postmeta.post_id=wp_posts.id
//                WHERE wp_posts.post_type = 'tgo_evento'
//                AND wp_posts.post_status = 'publish'
//                AND wp_terms.term_id = $idCategoria
//                AND meta_key='data'
//                AND wp_postmeta.meta_value>='$sqlToday'";
//        echo $sql;
        $results = wpdb()->get_results($sql);
        if ($results){
            $return=array();
            foreach ($results as $result){
                $return[]=$this->getById($result->ID);
            }
            return $return;
        }
    }


    public function getEventosOrfaos($id_evento_ignorar=null)
    {
        if ($id_evento_ignorar)
            $whereEventoIgnorar = " AND wp_posts.ID<>$id_evento_ignorar ";
        $sql = "
                SELECT wp_posts.ID
                FROM wp_posts
                LEFT JOIN wp_postmeta ON wp_postmeta.post_id=wp_posts.id and wp_postmeta.meta_key='data'
                WHERE wp_posts.post_type = 'tgo_evento'
                AND wp_posts.post_status = 'publish'
                $whereEventoIgnorar
                AND wp_posts.ID not in
                ( SELECT post_id FROM wp_postmeta
                  WHERE meta_key='id_evento_pai' and meta_value is not null and meta_value<>'')
                ORDER BY wp_postmeta.meta_value";
//        echo $sql;
        $results = wpdb()->get_results($sql);
//        var_dump(wpdb()->last_error);
//        var_dump($results);
        if ($results){
            $return=array();
            foreach ($results as $result){
                $return[]=$this->getById($result->ID);
            }
            return $return;
        }
    }



    public function getByCategory($idCategoria)
    {
        $sql = "
                SELECT wp_posts.post_title, wp_posts.ID, wp_terms.name FROM wp_posts
                LEFT JOIN wp_term_relationships ON wp_posts.ID=wp_term_relationships.object_id
                LEFT JOIN wp_term_taxonomy ON wp_term_taxonomy.term_taxonomy_id=wp_term_relationships.term_taxonomy_id
                LEFT JOIN wp_terms ON  wp_terms.term_id=wp_term_taxonomy.term_id
                WHERE wp_posts.post_type = 'tgo_evento' AND wp_posts.post_status = 'publish' AND wp_term_taxonomy.term_id = $idCategoria";
//        echo $sql;
        $results = wpdb()->get_results($sql);
        if ($results){
            $return=array();
            foreach ($results as $result){
                $return[]=$this->getById($result->ID);
            }
            return $return;
        }
    }

    /**
     * Obtem eventos futuros do pai e dos filhos dele (todas as ocorrencias do evento pai)
     * @param $id_evento_pai
     * @return Evento[]
     */
    public function getFuturosByPai($id_evento_pai)
    {
        $sqlToday = date('Y-m-d');
        $sql = "
                SELECT wp_posts.ID
                FROM wp_posts
                LEFT JOIN wp_term_relationships ON wp_posts.ID=wp_term_relationships.object_id
                LEFT JOIN wp_postmeta ON wp_postmeta.post_id=wp_posts.id
                WHERE wp_posts.post_type = 'tgo_evento'
                AND wp_posts.post_status = 'publish'
                AND
                (wp_posts.ID in
                   ( SELECT post_id
                      FROM wp_postmeta
                      WHERE meta_key='id_evento_pai' and meta_value=$id_evento_pai
                   )
                 OR
                   wp_posts.ID=$id_evento_pai
                )
                AND meta_key='data'
                AND CAST(wp_postmeta.meta_value as DATE)>='$sqlToday'";
//        echo $sql;
        $results = wpdb()->get_results($sql);
        if ($results){
            $return=array();
            foreach ($results as $result){
                $return[]=$this->getById($result->ID);
            }
            return $return;
        }
    }

    /**
     * @param bool|true $ordenar
     * @param null $limit
     * @param null $where
     * @return array|null
     */
    public function getAcontecendoFuturos($ordenar=true,$limit=null,$where=null)
    {
        $eventos = Eventos::getInstance()->getAll();
        if ($eventos==null) return null;
        $return = array();
        foreach ($eventos as $evento) {
            if ($evento->publicado && ($evento->acontecendo() || $evento->noFuturo()))
                $return[] = $evento;
        }

        // Ordenar
        $return = self::ordenar($return,$ordenar);

        return $return;
    }




    /**
     * Ordena um array de eventos
     * @param $eventos
     * @param bool $crescente
     * @return mixed
     */
    function ordenar($eventos,$crescente=false) {
        if ($eventos==null) return $eventos;
        if ($crescente)
            usort($eventos,array(__CLASS__,"ordem"));
        else
            usort($eventos,array(__CLASS__,"ordemDesc"));
        return $eventos;
    }

    /**
     * Regra para ordenar eventos
     * - Por data
     * - Depois pré-inscrição
     * @param Evento $a
     * @param Evento $b
     * @return int
     */
    function ordem(Evento $a, Evento $b) {
        // Se pré-inscrição, por ultimo
        if ($a->preInscricao() && !$b->preInscricao()) return 1;
        if (!$a->preInscricao() && $b->preInscricao()) return -1;

        if (strtotime($a->data) == strtotime($b->data)) {
            return 0;
        }
        return (strtotime($a->data) < strtotime($b->data)) ? -1 : 1;
    }

    /**
     * Regra para ordenar eventos
     * - Por data
     * - Depois pré-inscrição
     * @param Evento $a
     * @param Evento $b
     * @return int
     */
    function ordemDesc(Evento $a, Evento $b) {
        if ($a->preInscricao() && !$b->preInscricao()) return -1;
        if (!$a->preInscricao() && $b->preInscricao()) return 1;

        // Se pré-inscrição, por ultimo
        if (strtotime($a->data) == strtotime($b->data)) {
            return 1;
        }
        return (strtotime($a->data) > strtotime($b->data)) ? -1 : 1;
    }


    // Código antigo //




    private function mergeMetaData($post,$id)
    {
        $metas = $this->wpdb()->get_results("SELECT * FROM wp_postmeta WHERE post_id = '" . $id . "'", ARRAY_A);
        $data = array();
        foreach ($metas as $meta) {
            $data[$meta['meta_key']] = $meta['meta_value'];
        }

        $record = array_merge($post, $data);
        return $record;
    }

    public function setRewriteRule($post)
    {
        if ($post->post_name==null) return;
        $terms = wp_get_post_terms($post->ID, 'tgo_evento_tipo', array("fields" => "all"));
        $rule = "index.php?tgo_evento={$post->post_name}";
        if (count($terms)>0){
            foreach($terms as $term){
                $regex = "^{$term->slug}\/{$post->post_name}\$";
                add_rewrite_rule($regex, $rule, 'top');
            }
        } else {
            $regex = "^{$post->post_name}\$";
            add_rewrite_rule($regex, $rule, 'top');
        }
//        var_dump($regex);
//        var_dump($rule);
//        die();
        flush_rewrite_rules(false);
    }

    public function getUrl($post)
    {
        if ($post->post_name==null) return;
        if ($post->post_type!='tgo_evento') return;

        $terms=array();

        // URL por categoria
        if (TGO_EVENTO_URL_MODE==1){
            // Criar a url pelas categrias
            $post_categories = wp_get_post_categories($post->ID);
            foreach($post_categories as $c){
                $slug=null;
                $cat = get_category( $c );
                $slug=$cat->slug;
                if ($cat->category_parent){
                    while ($cat->category_parent){
                        $cat = get_category( $cat->category_parent );
                        $slug=$cat->slug."/".$slug;
                    }
                }
                $terms[] = array( 'name' => $cat->name, 'slug' => $slug );
            }
        }
        // URL por tag
        //$terms = (array)wp_get_post_terms($post->ID, 'post_tag', array("fields" => "all"));

        // URL padrão?
        if (TGO_EVENTO_URL_MODE==0){
            // Criar a url pelas taxonomias de tipos de eventos?
            $terms = (array)wp_get_post_terms($post->ID, 'tgo_evento_tipo', array("fields" => "all"));
        }

        if (count($terms)>0){
            foreach($terms as $term) {
                $term=(array)$term;
                return home_url("$term[slug]/$post->post_name");
            }
        } else {
//            die("home");
            return home_url( "/$post->post_name" );
        }



    }




}