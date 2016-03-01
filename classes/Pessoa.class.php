<?php

/**
 * Plugin Name: Pessoas
 * Plugin URI: http://www.tiagogouvea.com.br
 * Description: Gerenciador de Pessoas
 * Version: 00.00.01
 * Author: Tiago Gouvêa
 * Author URI: http://www.tiagogouvea.com.br
 * License: GPL2
 */
// Post types de Pessoas
use TiagoGouvea\PLib;
use TiagoGouvea\WPDataMapper\WPSimpleMapper;

/**
 * The Class.
 */
class Pessoa extends WPSimpleMapper
{

    public $id;
    public $nome;
    public $cpf;
    public $email;
    public $celular;
    public $extras;
    public $id_user;
    public $password;
    public $data_cadastro;
    public $end_cidade;
    public $end_estado;

    private $_imagem_url;
    private $_indiceQuebra;

    static function certificarPessoa($campoChave, $chave = null, $cpf = null, $pessoa)
    {
        $pessoa->$campoChave = $chave;
        $getBy = 'getBy' . ucfirst($campoChave);
        $pessoaTmp = Pessoas::getInstance()->$getBy($chave);
//        var_dump($pessoaTmp);
        if ($pessoaTmp == null) {
            Pessoas::getInstance()->insert($pessoa);
        } else {
            Pessoas::getInstance()->save($pessoaTmp->id, $pessoa);
        }

        return Pessoas::getInstance()->$getBy($chave);
    }

    /**
     * Obtem um array com as inscrições da pessoa
     * @return array
     */
    function getInscricoes()
    {
        return Inscricoes::getInstance()->getByPessoa($this->id);
    }


    public function getInscricoesConfirmadas($dataInicial = null)
    {
        $inscricoes = Inscricoes::getInstance()->getByPessoaConfirmado($this->id);
        if ($dataInicial != null) {
            $dataInicial = strtotime($dataInicial);
            $return = array();
            if ($inscricoes)
                foreach ($inscricoes as $inscricao) {
                    if (strtotime($inscricao->data_inscricao) >= $dataInicial)
                        $return[] = $inscricao;
                }
            return (count($return) > 0 ? $return : null);
        } else {
            return $inscricoes;
        }
    }

    public function getCountInscricoes()
    {
        return Inscricoes::getInstance()->getCountByPessoa($this->id);
    }

    public function getCountConfirmados()
    {
        return Inscricoes::getInstance()->getCountByPessoaConfirmados($this->id);
    }

    public function getCountPresentes($dataInicial)
    {
        return Inscricoes::getInstance()->getCountByPessoaPresente($this->id,$dataInicial);
    }

    public function getCountConfirmadoAntesCleanCode()
    {
        return Inscricoes::getInstance()->getCountByPessoaConfirmadosAntesCleanCode($this->id);
    }

    /**
     * Retorna os extras da pessoa
     * @param $extra String
     * @return array
     */
    public function getExtras()
    {
        if ($this->extras != null) {

            $extras = json_decode(Plib::unicode_to_utf8($this->extras), true);
//            var_dump($extras);
            if ($extras == null)
                $extras = json_decode($this->extras, true);
//            var_dump($extras);
            if ($extras == null)
                $extras = json_decode(stripslashes($this->extras), true);
//            var_dump($extras);
//            array_walk($extras,array('PLib','unicode_to_utf8'));
            return $extras;
        }
    }


    public function getExtrasArray($retornarChaveArray=true)
    {
        $titulosTipos = Organizadores::getInstance()->getExtrasTitulosTipos();
        $return = array();
        if ($this->extras == null) return;
        $extras = $this->getExtras();
//        var_dump($extras);
        if ($extras == null) return;

//        var_dump($extras);

        foreach ($extras as $key => $extra) {
            if ($extra['valor'] == '') continue;
            // Tentar obter o extra pelo titulo.....
            if (strpos($extra['titulo'], ' [ ') > 0)
                $titulo = substr($extra['titulo'], 0, strpos($extra['titulo'], ' [ '));
            else
                $titulo = $extra['titulo'];

            $tituloTipo = null;
            if ($titulosTipos) {
                foreach ($titulosTipos as $titTip) {
//                echo trim(strtolower($titulo)).'=='.(trim(strtolower($titTip->Titulo))).'<br>';
                    if (trim(strtolower($titTip->Titulo)) == trim(strtolower($titulo))) {
                        $tituloTipo = $titTip;
                        break;
                    }
                }
            }

            if ($tituloTipo) {
//                echo "<pre>";
//                var_dump($extra);
//                echo "</pre>";
//                var_dump($tituloTipo);
                $nExtra = $tituloTipo;

                if ($tituloTipo->Tipo == 'bool')
                    $nExtra->Valor = ($extra['valor'] == '1' ? 'Sim' : 'Não');
                else if ($tituloTipo->Tipo == 'file') {
                    if ($extra['arquivo']) {
                        $arquivo = get_stylesheet_directory() . "/../../uploads/eventos/" . $extra['arquivo'];
                        $arquivo = '/' . $this->get_absolute_path($arquivo);
//                        var_dump($arquivo);
                        if (file_exists($arquivo))
                            $nExtra->Valor = $arquivo;
                        else
                            $nExtra->Valor = null;
                    }
                } else {
                    $nExtra->Valor = $extra['valor'];
                }
                $chave = $titTip->Chave;
//                echo "<pre>";
////                var_dump($nExtra);
//                echo "</pre>";
            } else {
                $nExtra = json_decode(json_encode(array('Tipo' => 'string')));
                $nExtra->Valor = $extra['valor'];
                $nExtra->Titulo = $extra['titulo'];
                $chave = $nExtra->Titulo;
//                var_dump($nExtra);
            }
            if ($retornarChaveArray)
                $return[$key] = $nExtra;
            else
                $return[] = $nExtra;

//            } else if ($extra['arquivo']){
//                $extra['tipo']="file";
//                $arquivo = get_stylesheet_directory()."/../../uploads/eventos/".$extra['arquivo'];
//                if (file_exists($arquivo)){
//                    $extra['valor'] = get_stylesheet_uri()."/../../../uploads/eventos/".$extra['arquivo'];
//                } else {
//                    $extra['valor'] = "";
//                }
//            }
//            if (strpos($extra['titulo'], ' [ ') > 0)
//                $extra['titulo'] = substr($extra['titulo'],0,strpos($extra['titulo'], ' [ '));
//            $return[]=$extra;
        }
        return $return;
    }


    public function user()
    {
        return Instrutores::getInstance()->getById($this->id_user);
    }

    public function get_absolute_path($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * Retorna o valor apenas de um campo extra da pessoa
     * @param $extra String
     */
    public function getExtra($extra)
    {
        if ($this->extras != null) {
            $extras = $this->getExtrasArray();
//            var_dump($extras);
            return $extras[$extra]->Valor;
        }
    }

    /**
     * Diz se a pessoa tem ou não determinado extra
     * @param $extra
     * @return bool
     */
    public function hasExtra($extra)
    {
        if ($this->extras != null) {
            $extras = $this->getExtrasArray();
//            PLib::var_dump($extras);
            return $extras[$extra] != '';
        }
    }

    public function getExtrasExibicao($extrasDefinidos = null, $redesSociais = null, $apenasRedesBoas = true)
    {
        $extras = $this->getExtrasArray();
//        echo "<pre>";
//        var_dump($extras);
//        echo "</pre>";
        if ($extras == null) return null;
        $return = '';
        $social = '';
        foreach ($extras as $extraIndice => $extra) {
            if (substr($extraIndice, 0, 1) == '_') continue;
            if ($extra->Titulo == 'Id Mailchimp') continue;
            if ($extraIndice == 'Photo' || $extra->Titulo == 'Photo' || $extraIndice == 'gravatar') continue;
            if ($extrasDefinidos != null && !in_array($extraIndice, $extrasDefinidos)) continue;

            if ($extra->Tipo == 'file')
                $extra->Valor = "<a href='$extra->Valor'>Abrir</a>";

            // Redes Sociais
            if (
                ($apenasRedesBoas == false && array_key_exists($extraIndice, Pessoas::$networks)) ||
                ($apenasRedesBoas == true && array_key_exists($extraIndice, Pessoas::$networksGreat))
            ) {
                if ($extraIndice == 'gplus') $extraIndice = 'googleplus';
                $social .= ($social != null ? '&nbsp;' : '') . '<a href="' . $extra->Valor . '"><img src="https://www.fullcontact.com/wp-content/themes/fullcontact/assets/images/social/' . $extraIndice . '.png"></a>';
            } else {
                $return .= '<b>'.$extra->Titulo . ':</b> ' .nl2br($extra->Valor) . "<br>";
            }
        }

        if ($redesSociais === true && $social) {
            $return = $social;
        } else if ($redesSociais === null && $social) {
            $return = ($return != null ? $return . '<br>' : '') . $social;
        } else if ($redesSociais === true) {
            $return = null;
        }

        return $return;
    }

    /**
     * Determinar o valor de um extra apenas da pessoa, se já existir, atualiza
     * @param $chave
     * @param $titulo
     * @param $valor
     */
    public function setExtra($chave, $titulo, $valor)
    {
        $extras = (Array)json_decode($this->extras, true);
//        var_dump($extras);
//      echo "$chave = $titulo = $valor<br>";
        if (!isset($extras[$chave])) {
            $extra = array();
            $extra ['titulo'] = $titulo;
            $extra ['data_criacao'] = time();
        } else {
            $extra = $extras[$chave];
            if ($extra ['valor'] == addslashes($valor)) return;
//            if ($extra['data_atualizacao']!=null && )
            $extra ['data_atualizacao'] = time();
        }
        $extra ['valor'] = addslashes($valor);
        $extras[$chave] = $extra;
        $json = json_encode($extras);
        if ($json == null || $json == false) {
            throw new Exception("json vazio");
        }
        $this->extras = $json;
    }

    public function setExtras($extras)
    {
        if ($extras != null && count($extras) > 0) {
            foreach ($extras as $chave => $extra) {
                if ($extra['titulo'] == null) {
//                    var_dump($extra);
                    throw new Exception("Extra sem titulo?");
                }
                $this->setExtra($chave, $extra['titulo'], $extra['valor']);
            }
        }
    }

    public function __get($name)
    {
        $value = parent::__get($name);

        if ($value == null)
            return $this->getExtra($name);
    }

    public function primeiro_nome()
    {
        $nome = explode(" ", mb_convert_case(mb_strtolower($this->nome), MB_CASE_TITLE, "UTF-8"));
        if (count($nome) == 1)
            return $this->nome;
        else
            return $nome[0];
    }

    public function segundo_nome()
    {
        $nome = explode(" ", mb_convert_case(mb_strtolower($this->nome), MB_CASE_TITLE, "UTF-8"));
        $segundo = null;
        if (count($nome) >= 2)
            $segundo = $nome[1];
        if (in_array(strtolower($segundo), array('de', 'da', 'do', 'dos', 'das')) && count($nome) >= 3)
            $segundo = $nome[1] . ' ' . $nome[2];
        return $segundo;
    }

    public function primeiro_segundo_nome()
    {
        return $this->primeiro_nome() . ' ' . $this->segundo_nome();
    }

    private function iniciais()
    {
        $nomes = explode(" ", mb_convert_case(mb_strtolower($this->nome), MB_CASE_TITLE, "UTF-8"));
        if (count($nomes) >= 2) {
            $return = '';
            foreach ($nomes as $nome) {
                $return .= substr($nome, 0, 1);
            }
            return $return;
        } else
            return substr($this->nome, 0, 1);
    }

    /**
     * Retorna se a pessoa está inscrito no evento informado
     */
    public function hasInscricaoEventoConfirmado($idEvento)
    {
        return Inscricoes::getInstance()->getByEventoPessoaConfirmado($idEvento, $this->id) != null;
    }

    /**
     * Retorna o password, criando um novo caso não exista
     */
    public function getPassword()
    {
        if ($this->password != null) return $this->password;
        $this->password = mb_convert_case(mb_convert_case($this->iniciais(),MB_CASE_LOWER),MB_CASE_TITLE) . ($this->id * 13) . '!';
        Pessoas::getInstance()->save($this->id, $this);
        return $this->password;
    }

    /**
     * Salva o registro atual
     * @return Pessoa
     */
    public function save()
    {
        return Pessoas::getInstance()->save($this->id, $this);
    }

    /**
     * Diz se a pessoa tem alguma referencia a imagem
     */
    public function hasPicture($size = 80)
    {
        // Verificar em arquivo
        if ($this->hasPictureFile())
            return true;

        $extras = $this->getExtrasArray();
        if ($extras['photo']) {
            $this->_imagem_url = $extras['photo']->Valor;
            return true;
        }
        if ($extras['gravatar']) {
            $this->_imagem_url = 'http://www.gravatar.com/avatar/' . md5($this->email) . "?s=$size&d=mm";
            return true;
        }
        return false;
    }

    private function hasPictureFile()
    {
        $arquivoLocal = ABSPATH.'/imagens/pessoa/'.$this->id.'.png';
        if (file_exists($arquivoLocal)){
            $miniatura = ABSPATH.'/imagens/pessoa/'.$this->id.'_200x200.png';
            if (file_exists($miniatura)){
                return true;
            } else {
                ControllerPessoas::createMiniatura($arquivoLocal,$miniatura,200,200,80);
                // Gerar miniatura?
            }
        }
        return false;
    }

    public function getPictureUrl($size = 80)
    {
        if ($this->hasPictureFile()){
            return home_url().'/imagens/pessoa/'.$this->id.'_200x200.png?time='.time();
        } else if ($this->hasPicture($size))
            return $this->_imagem_url;
        else
            return "http://www.gravatar.com/avatar/" . md5(strtolower(trim($this->email))) . "?s=$size&d=mm"; // plugins_url('/Eventos/public/img/grey_user.png');
    }

    /*
     * Diz em quantos % das inscrições realizadas, não foi ao evento
     */
    public function getIndiceQuebra($dataInicial=null)
    {
        if ($this->_indiceQuebra) return $this->_indiceQuebra;

        // Obter eventos inscritos depois de 1 de agosto
        $inscricoes = $this->getInscricoesConfirmadas($dataInicial);
        if (count($inscricoes) <= 2)
            return null;
        $qtdInscricoes = count($inscricoes);
        $qtdPresente = 0;
        foreach ($inscricoes as $inscricao)
            if ($inscricao->presente)
                $qtdPresente++;
        $indice = (($qtdInscricoes - $qtdPresente) / $qtdInscricoes) * 100;
        $this->_indiceQuebra = round($indice, 0);
        return $this->_indiceQuebra;
    }

    public function HasPerfilIncompleto()
    {
        $incompleto =($this->getExtra('empresa') == '' || $this->getExtra('cargo') == '' || $this->getExtra('minibio') == '');
        if ($incompleto=== false){
            $sociais = 0;
            if ($this->getExtra('facebook')!='')
                $sociais++;
            if ($this->getExtra('twitter')!='')
                $sociais++;
            if ($this->getExtra('instagram')!='')
                $sociais++;
            if ($this->getExtra('gplus')!='')
                $sociais++;
            if ($this->getExtra('linkedin')!='')
                $sociais++;
            if ($this->getExtra('pinterest')!='')
                $sociais++;
            if ($this->getExtra('github')!='')
                $sociais++;
            if ($this->getExtra('skype')!='')
                $sociais++;

            $incompleto = $sociais<=1;
        }
        if ($incompleto===false){
            $incompleto = !$this->hasPicture();
        }
        return $incompleto;
    }

    /*
     * Retorna o link para um extra
     * Usado para redes sociais como instagram, que precisam formar o link diferente
     */
    public function getExtraLink($extra)
    {
        $link = $this->getExtra($extra);
        if ($extra=='instagram')
            $link = "http://instagram.com/".$extra;
        if ($extra=='skype')
            $link = 'skype:'.$link.'?call';
        return $link;
    }

    public function getStructuredDataJs()
    {
//        var_dump($this->data);
//        var_dump($this->hora);
//        var_dump($this->inicio());

        $gender=null;
        if ($this->getExtra('gender')){
            $gender=',
            "gender": "'.$this->getExtra('gender').'"';
        }

        $description=null;
        if (strlen($this->getExtra('minibio'))>2){
            $description=',
            "description": "'. Plib::remove_line_break($this->getExtra('minibio')).'"';
        }

        $sameAs=null;
//        $extras= $this->getExtrasExibicao(null,true);
//        if (count($extras)>0){
//            var_dump($extras);die();
//            $sameAs=',
//            "sameAs" : [';
//            foreach($extras as $extra){
//                var_dump($extra);
//                die();
//                $sameAs.=$extra.',
//                ';
//            }
//            $sameAs.=']';
//        }

        return '
        <script type="application/ld+json">
            {
              "@context": "http://schema.org",
              "@type": "Person",
              "image": "'.$this->getPictureUrl().'",
              "jobTitle": "'.$this->getExtra('cargo').'",
              "name": "'.$this->nome.'" '.$gender.$description.$sameAs.'
            }
        </script>';
    }


}
