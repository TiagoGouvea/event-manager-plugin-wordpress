<?php
use TiagoGouvea\PLib;

/**
 * User: TiagoGouvea
 * Date: 02/08/14
 * Time: 14:36
 */
class ControllerEventos
{

    /**
     *
     */
    public static function dispatcher()
    {
        if (isset($_GET['id'])){
            $evento = Eventos::getInstance()->getById($_GET['id']);
            set_the_evento($evento);
        }
        if (isset($_GET['action']))
            $action = $_GET['action'];

        if ($action == null) {
            if (get_user_role() == 'author') {
                // Listar inscrições
                self::inscricoes();
            } else {
                // Listar eventos
                self::showList();
            }
        }
        else if ($action == 'add-new' || $action == 'edit')
            //self::showForm($action, $evento);
            self::novoEvento();
        else if ($action=='delete')
            self::delete($evento);
        else if ($action=='view')
            self::view($evento);
        else if ($action=='inscricoes')
            self::inscricoes($evento);
        else if ($action=='financeiro')
            self::financeiro($evento);
        else if ($action=='avaliacoes')
            self::avaliacoes($evento);
        else if ($action=='configuracoes')
            self::configuracoes($evento);
        else if ($action=='comunicacao')
            self::comunicacao($evento);
        else if ($action == "editAreaAluno")
            self::editAreaAluno($evento);
        else if ($action == "editCertificado")
            self::editCertificado($evento);
        else echo "Action não encontrada em ".__CLASS__.": ".$action;
    }

    public static function showForm($action, $evento = null)
    {
        // Postando?
        if (count($_POST) > 0) {
            // Validar
            $evento = Eventos::getInstance()->populate($_POST);

            // Salvar ou incluir?
            if ($_POST['id'] == null) {
                $evento = Eventos::getInstance()->insert($evento);
            } else {
                $evento = Eventos::getInstance()->save($_POST['id'], $evento);
            }
            self::showList();
        } else {
            require_once PLUGINPATH . '/admin/Evento-edit.php';
        }
    }

    public static function showList()
    {
        require_once PLUGINPATH . '/view/eventos/list.php';

        // Acontecendo
        $eventos = Eventos::getInstance()->getAcontecendo();
        ListTableEventos($eventos, "Eventos Acontecendo");

        // Futuros
        $eventos = Eventos::getInstance()->getFuturosInscricao();
        ListTableEventos($eventos, "Eventos no Futuro");

        // PreInscricao
        $eventos = Eventos::getInstance()->getPreInscricao();
        ListTableEventos($eventos, "Pré-inscrição");

        // Rascunhos
        $eventos = Eventos::getInstance()->getRascunhos();
        ListTableEventos($eventos, "Rascunhos");

        // Passados
        $eventos = Eventos::getInstance()->getPassados();
        ListTableEventos($eventos, "Eventos Realizados");

        if (Eventos::getInstance()->getCountAll()==0){
            ButtonNovoEvento();
        }
    }

    private static function delete($evento)
    {
        Eventos::getInstance()->delete($evento->id);
        self::showList();
    }

    /*
     * Incluir novo evento
     * 1 - Pergunta o template de evento e o título
     * 2 - Insere o evento no banco setando título e template
     * 3 - Edita o evento
     */
    public static function novoEvento()
    {
        if (count($_POST) == 0) {
            // Exibir Página perguntando por template
            require_once PLUGINPATH . '/templates/AdminEvento_NovoEvento.php';
        } else {
            // Validar informações
            // Inserir Evento
            // Create post object
            $my_post = array(
                    'post_title' => $_POST['post_title'],
                    //'post_content'  => 'This is my post.',
                    'post_status' => 'draft',
                    'post_type' =>'tgo_evento'
            );
            $idPost = wp_insert_post($my_post);

            // Setar id_template
            meta_update($idPost, 'id_template', $idPost);

            // Redirecionar para edição do evento - não é a solução mais elegante.. mas...
            $url = admin_url() . '/post.php?&action=edit&post=' . $idPost;
            echo "<script>document.location='$url';</script>";
            //wp_redirect($url);
        }

    }

    private static function view($evento)
    {
        require_once PLUGINPATH . '/view/eventos/view.php';
    }

    private static function configuracoes($evento)
    {
        require_once PLUGINPATH . '/view/eventos/configuracoes.php';
    }

    private static function inscricoes($evento=null)
    {
        if ($evento!=null) {
            $filter = null;
            if (isset($_GET['filter'])) {
                $filter = $_GET['filter'];
            }
            require_once PLUGINPATH . '/view/eventos/inscricoes.php';

            ControllerInscricoes::showList($evento, $filter, false);
        } else {
            ControllerInscricoes::showList();
        }
    }

    private static function comunicacao($evento)
    {
        require_once PLUGINPATH . '/view/eventos/comunicacao.php';
    }

    private static function financeiro($evento)
    {
        require_once PLUGINPATH . '/view/eventos/financeiro.php';
    }

    private static function avaliacoes($evento)
    {
        require_once PLUGINPATH . '/view/eventos/avaliacoes.php';
    }

    private static function editAreaAluno($evento)
    {
        if ($_POST){
            meta_update($evento->id,'area_aluno',false);
            require_once PLUGINPATH . '/view/eventos/view_area_aluno.php';
        } else {
            require_once PLUGINPATH . '/view/eventos/form_area_aluno.php';
        }
    }

    private static function editCertificado(Evento $evento)
    {
//        var_dump($evento->certificado_arquivo);
//        die();
        $file = wp_upload_dir();
        $file = $file['path'];
//        var_dump($file);
        if ($_POST){
//            var_dump($_FILES);
            // Nova imagem
            if ($_FILES['arquivo']){

//                var_dump($_FILES);
                $name = strtolower($_FILES['arquivo']['name']);
                $name = str_replace(' ','_',$name);
                if (strpos($name,'.png')===false)
                    die("O arquivo deve ser .PNG");
                $file.="/".$name;
//                var_dump($file);
                // Ajustar nome do arquivo
                // Mover
                move_uploaded_file($_FILES['arquivo']['tmp_name'],$file);
//                var_dump(file_exists($file));
                // Ajustar tamanho
                PLib::smart_resize_image($file,null,1280,0,true,'file',$file,false);

                // Atualizar evento
                update_post_meta($evento->id,'certificado_arquivo',$file);
                update_post_meta($evento->id,'certificado_incluir_evento',$_POST['evento']==1);
                update_post_meta($evento->id,'certificado_incluir_nome',$_POST['nome']==1);
                $evento->certificado_arquivo = $file;
                $evento->certificado_incluir_evento = $_POST['evento']==1;
                $evento->certificado_incluir_nome = $_POST['nome']==1;
            }
            // Demais configurações
            $altura_nome=$_POST['altura_nome'];
            if ($altura_nome) {
                update_post_meta($evento->id, 'certificado_altura_nome', $altura_nome);
                $evento->certificado_altura_nome=$altura_nome;
            }

            require_once PLUGINPATH . '/view/eventos/view_certificado.php';
        } else {
//            echo "<pre>";
//            var_dump($evento);
//            echo "</pre>";
            if ($evento->hasCertificadoArquivo())
                require_once PLUGINPATH . '/view/eventos/view_certificado.php';
            require_once PLUGINPATH . '/view/eventos/form_certificado.php';
        }
    }
}