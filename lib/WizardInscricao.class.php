<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 04/04/15
 * Time: 18:58
 */

namespace lib;

use Categoria;
use Categorias;
use DescontoInscricao;
use DescontosInscricoes;
use Evento;
use Eventos;
use Exception;
use Inscricao;
use Inscricoes;
use Integracao;
use Integracoes;
use PagSeguroAccountCredentials;
use PagSeguroTransactionSearchService;
use Pessoa;
use Pessoas;
use Preco;
use Precos;
use TiagoGouvea\PLib;

define('CHAVE', 1);
define('DADOS_PESSOAIS', 2);
define('CATEGORIAS', 3);
define('PAGAMENTO', 4);
define('FIM', 5);

class WizardInscricao
{

    private static $instance;
    public $etapa;
    public $etapaFinal = false;
    public $etapaSeguinte;
    public $avancar;
    /* @var $erro String */
    public $erro;
    public $forcarEtapa;
    public $campoChave;
    public $valorChave;
    /* @var $evento Evento */
    public $evento;
    /** @var $pessoa Pessoa */
    public $pessoa;
    /** @var $inscricao Inscricao */
    public $inscricao;
    public $avancarTexto;
    private $urlForm;

    public function __construct($idEvento, $ticket = null)
    {
        self::$instance = $this;


        // Obter evento
        $this->evento = Eventos::getInstance()->getById($idEvento);
        // Definir campo chave
        if ($this->evento->validacao_pessoa == 'cpf')
            $this->campoChave = "cpf";
        else
            $this->campoChave = "email";

        // Recebendo um ticket? Levar para etapa de pagamento?
        if ($ticket) {
            $this->etapa = PAGAMENTO;
            $this->inscricao = Inscricoes::getInstance()->getById($ticket / 13);
            $this->pessoa = $this->inscricao->pessoa();
            $this->etapaFinal = true;
            return;
        }

        // Determinar etapa
        $this->etapa = $_POST['etapa'];
        if ($this->etapa == null) {
            $this->etapa = 1;
            $this->forcarEtapa = (isset($_GET['cpf'])) || (isset($_GET['email']));
        }

        // Estamos recebendo confirmação de pagamento?
        $this->transactionId = $_GET['transaction_id'];
        $this->id_integracao = $_GET['id_integracao'];
        if ($this->transactionId != null) {
            $result = Inscricoes::getInstance()->callbackPagamento($this->id_integracao, $this->transactionId);
            if (get_class($result) == 'Inscricao') {
                $this->inscricao = $result;
                $this->pessoa = $this->inscricao->pessoa();
            } else
                $this->erro = $result;
            $this->etapa = FIM;
            $this->etapaFinal = true;
        }




        // URL atual
        $this->urlForm = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        if (strpos($this->urlForm, 'ttp://') == false) {
            $this->urlForm = "http://" . $this->urlForm;
        }
        if (strpos($this->urlForm,"&ticket")!==false)
            $this->urlForm = substr($this->urlForm,0,strpos($this->urlForm,"&ticket"));

        // Determinar valores padrão
        $this->avancarTexto = "Avançar";
        $this->avancar = false;

        // Postando? Validar
        if (count($_POST) > 0 || $this->forcarEtapa) {
            $this->validarEtapa();
        }
    }

    static function getInstance(){
        return self::$instance;
    }

    public function getEvento()
    {
        return $this->evento;
    }

    public function validarEtapa()
    {

//        PLib::var_dump($this->etapa,"Etapa em validarEtapa");
        // Etapa 1
        if ($this->etapa == CHAVE) {
            $_SESSION['id_inscricao'] = null;
            $_SESSION['id_pessoa'] = null;
            $this->validarEtapaChave();
        }


        // Etapa 2 - Dados completos da pessoa
        if ($this->etapa == DADOS_PESSOAIS) {
            $this->validarEtapaDadosPessoais();
        }

        // Etapa 3 - Categorias
        if ($this->etapa == CATEGORIAS) {
            $this->validarEtapaCategorias();
        }

        if ($this->avancar)
            $this->getEtapaSeguinte($this->evento, $this->etapa);

        // Indo para pagamento? Garantir valores e categorias
        if ($this->etapa == PAGAMENTO) {
            $this->prepararEtapaPagamento();
            $this->etapaFinal = true;
        }

        // Ajustes e tratamentos finais
        $temoInscricao = "inscrição";
        //if ($this->evento->confirmacao == "preinscricao")
        //    $temoInscricao = "pré-inscrição";
        if ($this->pessoa == null && $_SESSION['id_pessoa'] != null)
            $this->pessoa = Pessoas::getInstance()->getById($_SESSION['id_pessoa']);
        if ($this->inscricao == null && $_SESSION['id_inscricao'] != null)
            $this->inscricao = Inscricoes::getInstance()->getById($_SESSION['id_inscricao']);

        if ($this->etapa == FIM) {
            // Limpar dados de inscrição da sessão
            $_SESSION['id_inscricao'] = null;
            $_SESSION['id_pessoa'] = null;
            $this->etapaFinal = true;
        }



    }

    public function validarEtapaChave()
    {
        // De acordo com indetificação principal?
        if ($this->campoChave == 'cpf') {
            $this->valorChave = PLib::coalesce($_POST['cpf'], $_GET['cpf']);
            $cpf = PLib::validade_cpf($this->valorChave);
            if ($cpf === fase)
                $this->erro = "Informe um CPF válido.";
            else
                $this->valorChave = Plib::str_mask(PLib::only_numbers($this->valorChave), "###.###.###-##");
        } else {
            $this->valorChave = sanitize_email($_POST['email']);
            $email = is_email($this->valorChave);
            if (!$email)
                $this->erro = "Informe um email válido.";
            else
                $this->valorChave = trim(strtolower($this->valorChave));
        }


        if ($this->erro == null) {
            $this->avancar = true;

            $obterPor = 'getBy' . ucfirst($this->campoChave);
            // Tentar localizar pessoa
            $this->pessoa = Pessoas::getInstance()->$obterPor($this->valorChave);


            // Encontramos alguém?
            if ($this->pessoa != null) {
                // Trazer dados para sessão
                $_SESSION['id_pessoa'] = $this->pessoa->id;
                $this->pessoaNome = $this->pessoa->nome;
            } else {
                $this->pessoa = new Pessoa();
                // Tentar obter a pessoa por uma integração?
                /** @var $integracao Integracao */
                $integracao = Integracoes::getInstance()->getByServico('PhormarPessoa');
                if ($integracao) {
                    $pessoaIntegragao = $integracao->getPessoa($this->valorChave);
                    if ($pessoaIntegragao != null) {
                        $this->pessoa = $pessoaIntegragao;
//                        var_dump($pessoaIntegragao->extras);
//                        var_dump($this->pessoa);
//                        die('ok');
                        // Salvar pessoa, certificar que esteja registrada
                        $this->certificarPessoa();
                    }
//                    var_dump($pessoaIntegragao);
                }
                // Nova pessoa
                $this->pessoa->newsletter = true;
            }
            $_SESSION['inscricao_chave'] = $this->valorChave;

//            if ($debug)
//                PLib::var_dump($this->pessoa,"Pessoa");

            // Se confirmação imediata
            if ($this->evento->confirmacao == 'imediata' || $this->evento->confirmacao == 'posterior')
                $this->avancarTexto = "Concluir Inscrição";
        }


        if ($this->erro==null){
            // Aplicar desconto?
            if ($_POST['ticket']!=null){
                $result = Inscricoes::getInstance()->aplicarTicket($this->evento,$_POST['ticket']);
                if ($result!==true)
                    $erro = $result;
            }
        }
    }

    public function validarEtapaDadosPessoais()
    {
        // Validar dados
        $nome = sanitize_text_field(trim($_POST['nome']));
        $celular = ltrim(PLib::only_numbers(sanitize_text_field(trim($_POST['celular'])), '0'));
        $email = sanitize_text_field(trim($_POST['email']));



//        PLib::var_dump($_POST);
        // Quais outros dados são obrigatórios??
        if (!$nome)
            $this->erro = "Informe seu nome.";
        if (strpos($nome, " ") === false)
            $this->erro = "Informe ao menos nome e sobrenome.";
        if (!$celular)
            $this->erro = "Informe seu telefone celular.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $this->erro = "Informe um endereço de email válido.";

        // Validar telefone celular e formatar
        if ($this->erro == null) {
            $len = strlen($celular);
            if ($len < 10 || $len > 12)
                $this->erro = "Informe o celular com o prefixo.";
            else {
                if ($len == 10)
                    $_POST['celular'] = PLib::str_mask($celular, "(##)####-####");
                else
                    $_POST['celular'] = PLib::str_mask($celular, "(##)#####-####");
            }
        }



        if ($this->erro == null) {
            // Temos todos os dados da pessoa
            $this->valorChave = $_SESSION['inscricao_chave'];

            $extras = array();
            $this->pessoa = new Pessoa();
            // Já tinhamos a pessoa? Obter os extras para merge com extras novos
            if ($_SESSION['id_pessoa'] != null) {
                $this->pessoa = Pessoas::getInstance()->getById($_SESSION['id_pessoa']);
                $pessoaExtras = $this->pessoa->getExtras();
            }
            $this->pessoa = Pessoas::getInstance()->populate($_POST, $this->pessoa);

//            if ($debug)
//                PLib::var_dump($this->pessoa,"Pessoa 1");

            // Existem campos extras?
            if ($this->evento->campos_extras != null) {
                foreach ($this->evento->getCamposExtras() as $extraIndice => $extraTitulo) {
                    if (isset($_POST[$extraIndice]) || isset($_FILES[$extraIndice])) {
                        // Zerar valor
                        $extraValor = null;
                        $extraTituloClean = $extraTitulo;

                        // Clean no titulo
                        if (strpos($extraTitulo, '[') !== false && strpos($extraTitulo, ']') !== false) {
                            $opcoes = substr($extraTitulo, strpos($extraTitulo, '['));
                            $extraTituloClean = str_replace($opcoes, "", $extraTitulo);
                        }
                        if (strpos($extraTitulo, '( )') !== false) {
                            $opcoes = substr($extraTitulo, strpos($extraTitulo, '('));
                            $extraTituloClean = trim(str_replace($opcoes, "", $extraTitulo));
                        }

                        if (isset($_POST[$extraIndice]))
                            $extraValor = sanitize_text_field($_POST[$extraIndice]);

                        $extras[$extraIndice] = array();
                        $extras[$extraIndice]['titulo'] = $extraTituloClean;
                        $extras[$extraIndice]['valor'] = $extraValor;
                        //var_dump($extras[$extraIndice]);

                        // File upload
                        if (strpos($extraTitulo, '[file]') !== false && isset($_FILES[$extraIndice])) {
                            $tmpFile = $_FILES[$extraIndice]["tmp_name"];
                            $tmpName = $_FILES[$extraIndice]["name"];
//                            var_dump($tmpFile);
//                            var_dump($_FILES[$extraIndice]['error']);
                            $target_dir = WP_CONTENT_DIR . "/uploads/eventos/";
                            $target_file = $target_dir . basename($tmpName);
//                            var_dump($target_file);
                            $uploadOk = 1;
                            $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                            if (file_exists($target_file))
                                unlink($target_file);
                            // Allow certain file formats
                            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                                && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "bmp"
                            ) {
                                $erro = "Apenas arquivos JPG, JPEG, PNG, GIF, BMP, PDF e DOC são aceitos no campo \"$extraTituloClean\".";
                                $uploadOk = 0;
                            }

                            if ($uploadOk == 1) {
//                                error_reporting(E_ALL);
//                                echo "<br><br>mover $tmpFile para $target_file<br>";
                                if (!move_uploaded_file($tmpFile, $target_file)) {
                                    $erro = "Não foi possível fazer upload dos arquivos.";
//                                    print_r(error_get_last());
//                                    die("Sorry, there was an error uploading your file.");
                                }
                                $extras[$extraIndice]['arquivo'] = basename($tmpName);
                            }
                        }
                    }
                }
                if (count($extras) > 0)
                    $this->pessoa->setExtras($extras);
            }

//            if ($debug)
//                PLib::var_dump($this->pessoa,"Pessoa 2");

            if ($this->erro == null) {
                // Salvar pessoa, certificar que esteja registrada
                $this->certificarPessoa();
//                die("aaa");
                // Verificar se pessoa já está inscrita e confirmada
                $inscricao = Inscricoes::getInstance()->getByEventoPessoaConfirmado($this->evento->id, $this->pessoa->id);
//                var_dump($inscricao);die();
                if ($inscricao != null)
                    $this->erro = "Você já está inscrito e confirmado para este evento!";
            }

//            var_dump($this->erro);
//            die("aass");


            if ($this->erro == null) {
                $this->avancar = true;
                if ($this->evento->pago == "pago") {
                    // Obter Preço atual
                    $preco = Precos::getInstance()->getByEventoAtual($this->evento->id);
                    if ($preco != null)
                        $id_preco = $preco->id;
                }

                // Já salvar registro de inscrição
                $this->inscricao = Inscricoes::getInstance()->certificarInscricao($this->evento, $this->pessoa, $this->evento->confirmacao == "preinscricao", $id_preco, null, $_POST['c_utmz']);
                $_SESSION['id_inscricao'] = $this->inscricao->id;

                // Existe arquivos?
                if (count($extras) > 0) {
                    $extrasModificados = false;
                    foreach ($extras as $extraIndice => $extra) {
                        if ($extra['arquivo']) {
//                            var_dump($extra['arquivo']);
                            $target_dir = WP_CONTENT_DIR . "/uploads/eventos/";
                            $target_file = $target_dir . $extra['arquivo'];
//                            var_dump($target_file);
                            $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                            // Rename file
                            $target_file_new = $target_dir . 'inscricao_' . $this->inscricao->id . '_' . $extraIndice . '.' . $imageFileType;
                            rename($target_file, $target_file_new);
//                            var_dump($target_file_new);
                            $extras[$extraIndice]['arquivo'] = basename($target_file_new);
                            $extrasModificados = true;
                        }
                    }
                    if ($extrasModificados) {
                        $this->pessoa->extras = json_encode($extras);
                        $this->pessoa = Pessoas::getInstance()->save($this->pessoa->id, $this->pessoa);
                    }
                }

//
            }
        }

//            PLib::var_dump($this->pessoa,"Pessoa final");
//        PLib::var_dump($this->erro,"Erro");
    }

    public
    function validarEtapaCategorias()
    {
        // Selecionou uma categoria?
        $idCategoria = $_POST['categoria'];
        if ($idCategoria == null) {
            $this->erro = "Por favor, selecione uma categoria.";
        }
        if ($this->erro == null) {
            $this->avancar = true;

            // Obter categoria
            /** @var $categoria Categoria */
            $categoria = Categorias::getInstance()->getById($idCategoria);

            // Registrar categoria e preço na inscrição
            $this->inscricao = Inscricoes::getInstance()->getById($_SESSION['id_inscricao']);
            $this->inscricao->id_categoria = $idCategoria;
            $this->inscricao->setPreco($categoria->getPreco()->id);

            Inscricoes::getInstance()->save($this->inscricao->id, $this->inscricao);

//            if ($debug)
//                PLib::var_dump($this->inscricao,"Inscrição");
        }
    }

    public
    function prepararEtapaPagamento()
    {
        // Calcular tudo aqui? Descontos?
        // Registrar categoria e preço na inscrição
        if ($_SESSION['id_inscricao'] == null) throw new Exception("id_inscricao null" .
            '<br><br><br>post:<br>' . print_r($_POST, true) .
            '<br><br><br>session:' . print_r($_SESSION, true)
        );

        $this->inscricao = Inscricoes::getInstance()->getById($_SESSION['id_inscricao']);


        // Definição do preço
        if ($this->evento->pago == 'pago') {
//            if ($this->inscricao->id_categoria != null) {
//                $categoria = Categorias::getInstance()->getById($this->inscricao->id_categoria);
//                if ($categoria->id_preco)
//                    $this->inscricao->id_preco = $categoria->id_preco;
//            } else {
//                // Sem categoria... preço atual do evento
//                $this->inscricao->id_preco = $this->evento->getPrecoAtual()->id;
//            }
//            if ($this->inscricao->id_preco != null) {
//                /* @var $preco Preco */
//                $preco = Precos::getInstance()->getById($this->inscricao->id_preco);
//                $this->inscricao->valor_inscricao = $this->evento->getValorAtual();
//            }

            // Se tiver descontos, registrar no banco
            $descontos = $this->evento->getDescontoSessaoArray();
            if ($descontos) {
                foreach ($descontos as $desconto) {
                    if (!DescontosInscricoes::getInstance()->hasByDescontoInscricao($desconto->id, $this->inscricao->id)) {
//                        var_dump($desconto);
                        $descontoInscricao = new DescontoInscricao();
                        $descontoInscricao->id_inscricao = $this->inscricao->id;
                        $descontoInscricao->id_desconto = $desconto->id;
                        $descontoInscricao = DescontosInscricoes::getInstance()->insert($descontoInscricao);
//                        var_dump($descontoInscricao);
                    }
                }
            }

            // Se o inscrito recebeu 100% de desconto, ou irá pagar 0 reais, já confirmar a inscrição
            if ($this->inscricao->valor_inscricao == 0)
                $this->inscricao = $this->inscricao->confirmar();

//            PLib::var_dump($descontos);
//            die();
        }

//        PLib::var_dump($this->inscricao);
//        die();


        $this->inscricao = Inscricoes::getInstance()->save($this->inscricao->id, $this->inscricao);

//        if ($debug)
//            PLib::var_dump($this->inscricao,"Inscrição com pagamento");
    }


    public
    function getEtapaSeguinte()
    {
        $this->etapaSeguinte = $this->etapa;

//        PLib::var_dump($this->etapa,"Etapa em getEtapaSeguinte");

        // Informou corretamente o valor de chave - 1
        if ($this->etapa == CHAVE) {
            $this->etapaSeguinte = DADOS_PESSOAIS;
        }

        // Informou dados pessoais corretamente - 2
        if ($this->etapa == DADOS_PESSOAIS) {
            // Fila de espera?
            if ($this->evento->fila_espera)
                $this->etapaSeguinte = FIM;
            else if ($this->evento->preInscricao())
                $this->etapaSeguinte = FIM;
            else if ($this->evento->hasCategorias())
                $this->etapaSeguinte = CATEGORIAS;
            else {
                if ($this->evento->pago == 'gratuito')
                    $this->etapaSeguinte = FIM;
                else
                    $this->etapaSeguinte = PAGAMENTO;
            }
        }

        // Escolheu categoria corremante - 3
        if ($this->etapa == CATEGORIAS) {
            if ($this->evento->pago == 'gratuito')
                $this->etapaSeguinte = FIM;
            else
                $this->etapaSeguinte = PAGAMENTO;
        }

//        PLib::var_dump(null,"Etapa: $this->etapa - Seguinte: $this->etapaSeguinte");

        $this->etapa = $this->etapaSeguinte;

        return $this->etapaSeguinte;
    }

    private
    function certificarPessoa()
    {
        // Garantir que não seja incluida duas vezes (mover tudo isso pra classe)
        $this->pessoa = Pessoa::certificarPessoa($this->campoChave, $this->valorChave, null, $this->pessoa);
//                if ($debug)
        //PLib::var_dump($this->pessoa,"Pessoa Certificada");
        // Salvar pessoa na sessão
        $_SESSION['id_pessoa'] = $this->pessoa->id;
    }

    public function getUrlForm()
    {
        return $this->urlForm;
    }

    public function getInscricao()
    {
        return $this->inscricao;
    }

    public function getPessoa()
    {
        return $this->pessoa;
    }


}