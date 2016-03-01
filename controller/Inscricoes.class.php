<?php
use Cielo\Cielo;
use lib\CieloUtil;
use lib\PagSeguroUtil;
use lib\PhormarUtil;
use TiagoGouvea\PLib;

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 25/03/15
 * Time: 22:15
 */
class ControllerInscricoes
{
    public static function dispatcher()
    {
        $action = $_GET['action'];

        $evento = null;
        $inscricao = null;

        if (isset($_GET['id_evento']))
            $evento = Eventos::getInstance()->getById($_GET['id_evento']);

        if (isset($_GET['id']))
            $inscricao = Inscricoes::getInstance()->getById($_GET['id']);

        $filter = null;
        if (isset($_GET['filter'])) {
            $filter = $_GET['filter'];
        }

        if ($action == null)
            self::showList($evento, $filter);
        if ($action == 'delete')
            self::delete($inscricao, $evento);
        if ($action == 'extras')
            self::extras($evento);
        if ($action == 'inscricao-confirmar')
            self::confirmar($inscricao);
        if ($action == 'inscricao-cancelar')
            self::cancelar($inscricao);
        if ($action == 'inscricao-informar-valor')
            self::informarValor($inscricao);
        if ($action == 'inscricao-presenca')
            self::presenca($inscricao);
        if ($action == 'exportarCsv')
            self::exportarCsv($evento, $filter);
        if ($action == 'exportarCsvFullContacts')
            self::exportarCsvFullContacts($evento, $filter);
        if ($action == 'importarCsv')
            self::importarCsv($evento);
        if ($action == 'cancelarNaoConfirmados')
            self::cancelarNaoConfirmados($evento);
        if ($action == 'add')
            self::add($evento);
    }

    public static function showList($evento=null, $filter = null, $headerEvento = true)
    {
        if ($evento!=null) {
            require_once PLUGINPATH . '/view/inscricoes/list.php';

            $inscritos = Inscricoes::getInstance()->getByFilterString($evento, $filter);
            if ($filter == 'preInscritos') {
                $subTitulo = "Pré-Inscritos (" . count($inscritos) . ")";
            } else if ($filter == 'confirmados') {
                $subTitulo = "Inscritos já confirmandos (" . count($inscritos) . ")";
            } elseif ($filter == 'naoConfirmados') {
                $subTitulo = "Inscritos Pendentes, não confirmandos  (" . count($inscritos) . ")";
            } elseif ($filter == 'rejeitados') {
                $subTitulo = "Inscrições canceladas (" . count($inscritos) . ")";
            } elseif ($filter == 'filaEspera') {
                $subTitulo = "Inscrições na Fila de Espera (" . count($inscritos) . ")";
            } elseif ($filter == 'presentes') {
                $subTitulo = "Presentes ao evento (" . count($inscritos) . ")";
            } else {
                $subTitulo = "Todos os incritos (" . count($inscritos) . ")";
            }

//        var_dump($inscritos);

            ListTableInscritos($evento, $inscritos, $subTitulo, $filter, $headerEvento);
        } else {
            // Obter todas as inscrições?
            $inscritos = Inscricoes::getInstance()->getAll();
            require_once PLUGINPATH . '/view/inscricoes/list_geral.php';
            ListTableInscritosGeral($inscritos);
        }
    }

    private static function delete($inscricao, $evento)
    {
        Inscricoes::getInstance()->delete($inscricao->id);
        self::showList($evento);
    }

    public static function direcionarServicoPagamento($servico, $idInscricao)
    {
        // Validar serviço e id informado antes de mais nada

        // Obter dados relevantes
        /* @var $inscricao Inscricao */
        $inscricao = Inscricoes::getInstance()->getById($idInscricao);
        $pessoa = $inscricao->pessoa();
        $evento = $inscricao->evento();

        if ($servico == 'pagseguro') {
            PagSeguroUtil::redirecionar($evento, $pessoa, $inscricao);
        } else if ($servico == 'cielo') {
            CieloUtil::redirecionar($evento, $pessoa, $inscricao);
        }
    }

    /**
     * Confirma uma inscrição
     * @param $inscricao
     */
    private static function confirmar(Inscricao $inscricao)
    {
        $formaPagamento = ($_GET['forma_pagamento'] ? $_GET['forma_pagamento'] : 'Dinheiro');
        $inscricao = $inscricao->confirmar($formaPagamento, $_GET['valor_pago']);
        echo json_encode(array("html" => $inscricao->getSituacaoString() . $inscricao->getSituacaoAcoes(true)));
        wp_die();
    }

    /**
     * Cancela uma inscrição
     * @param $inscricao
     */
    private static function cancelar(Inscricao $inscricao)
    {
        $inscricao = $inscricao->cancelar();
        echo json_encode(array("html" => $inscricao->getSituacaoString() . $inscricao->getSituacaoAcoes(true)));
        wp_die();
    }

    /**
     * Dá presença em uma inscrição
     * @param Inscricao $inscricao
     */
    private static function presenca(Inscricao $inscricao)
    {
        $inscricao = $inscricao->presenca();
        echo json_encode(array("html" => $inscricao->getSituacaoString() . $inscricao->getSituacaoAcoes(true)));
        wp_die();
    }


    /**
     * Informa o valor da inscrição em questão
     * @param $inscricao
     */
    private static function informarValor(Inscricao $inscricao)
    {
        $valor = $_GET['valor'];
        $forma_pagamento = $_GET['forma_pagamento'];
        $inscricao->valor_pago = $valor;
        $inscricao->forma_pagamento = $forma_pagamento;
        $inscricao = $inscricao->save();
        echo json_encode(array("html" => $inscricao->getSituacaoString() . $inscricao->getSituacaoAcoes(true)));
        wp_die();
    }

    /**
     * Obtem os dados dos gateways em uso e processa
     */
    public static function sincronizarGateways()
    {

        // Obter a transação retornada
        // Verificar situação

        $dateTimeZoneTaipei = new DateTimeZone('America/Sao_Paulo');
        $dateTimeTaipei = new DateTime("now", $dateTimeZoneTaipei);
        $timeOffset = $dateTimeTaipei->getOffset();

        // Filtro de datas (1 mes)
        $timestamp = time() + $timeOffset;
        $initialDate = gmdate("Y-m-d\TH:i:s\Z", $timestamp - (30 * 24 * 60 * 60));
        $finalDate = gmdate("Y-m-d\TH:i:s\Z", $timestamp);

        // Por credencial
        /* @var $integracoes Integracao[] */
        $integracoes = Integracoes::getInstance()->getAll();
        foreach ($integracoes as $integracao) {
//            var_dump($integracao);
            if ($integracao->servico == 'PagSeguro') {
                include_once PLUGINPATH . '/vendor/PagSeguro/PagSeguroLibrary.php';
                echo "<h1>$integracao->titulo ($integracao->servico)</h1>";
                /** @var $transacoes PagSeguroTransactionSearchResult */
                $transacoes = PagSeguroUtil::getTransacoesDatas($integracao, $initialDate, $finalDate);
//                continue;
                if (get_class($transacoes) == 'PagSeguroTransactionSearchResult') {
                    PagSeguroUtil::processarTransacoes($integracao, $transacoes);
                } else {
                    echo "Falha ao obter transações<br>";
                }
            } else if ($integracao->servico == 'Cielo') {

                // Obter todos os pedidos locais - sob algum filtro

                $transcacao = CieloUtil::getTransacaoPedido($integracao, 53);
                var_dump($transcacao);

//                $transacoes = CieloUtil::getTransacoesDatas($integracao, $initialDate, $finalDate);
//                continue;
//                if (get_class($transacoes) == 'PagSeguroTransactionSearchResult') {
//                    PagSeguroUtil::processarTransacoes($integracao, $transacoes);
//                } else {
//                    echo "Falha ao obter transações<br>";
//                }
            }
        }

        echo "<br><br>Fim de sincronização<br><br>";
    }

    static function httprequest($paEndereco, $paPost)
    {
        $sessao_curl = curl_init();
        curl_setopt($sessao_curl, CURLOPT_URL, $paEndereco);

        curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);
        //  CURLOPT_SSL_VERIFYPEER
        //  verifica a validade do certificado
        curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, true);
        //  CURLOPPT_SSL_VERIFYHOST
        //  verifica se a identidade do servidor bate com aquela informada no certificado
        curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 2);
        //  CURLOPT_SSL_CAINFO
        //  informa a localização do certificado para verificação com o peer
//        curl_setopt($sessao_curl, CURLOPT_CAINFO, getcwd() .
//            "/ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt");
        curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 3);
        //  CURLOPT_CONNECTTIMEOUT
        //  o tempo em segundos de espera para obter uma conexão
        curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 10);
        //  CURLOPT_TIMEOUT
        //  o tempo máximo em segundos de espera para a execução da requisição (curl_exec)
        curl_setopt($sessao_curl, CURLOPT_TIMEOUT, 40);
        //  CURLOPT_RETURNTRANSFER
        //  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
        //  invés de imprimir o resultado na tela. Retorna FALSE se há problemas na requisição
        curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($sessao_curl, CURLOPT_POST, true);
        curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $paPost);
        $resultado = curl_exec($sessao_curl);

        curl_close($sessao_curl);

        var_dump($resultado);
        if ($resultado) {
            return $resultado;
        } else {
            return curl_error($sessao_curl);
        }
    }

    public static function processarNotificacao($id_integracao, $notificationCode)
    {
        // Logar notificacao
        self::logarNotificacao($_POST);

        /* @var $integracao Integracao */
        $integracao = Integracoes::getInstance()->getById($id_integracao);

        // Processar notificação
        if (strtolower($integracao->servico) == 'pagseguro') {
            $transacao = PagSeguroUtil::getTransacaoNotificacao($integracao, $notificationCode);
            if (get_class($transacao) == 'PagSeguroTransaction') {
                $return = Inscricoes::getInstance()->processarTransacaoPagSeguro($transacao);
                if (get_class($return) != 'Inscricao')
                    throw new Exception('PagSeguro - ' . $return);
            } else {
                throw new Exception("Falha ao obter transação da notificação: $notificationCode");
            }
        } elseif (strtolower($integracao->servico) == 'cielo') {
            // Me notificar pra eu entender a coisa
            $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            enviarEmail(TGO_EVENTO_ADMIN, 'Notificacao cielo', 'URL: '.$actual_link.'<br><br>GET:<pre>' . print_r($_POST, true) . '</pre><br><br>POST<pre>:' . print_r($_POST, true).'</pre>');

            $transacao = $_POST;
            $return = Inscricoes::getInstance()->processarTransacaoCielo($transacao);
            if (get_class($return) != 'Inscricao')
                throw new Exception('Cielo - ' . $return);
            else
                echo '<status>OK</status>';
        }
    }

    private static function logarNotificacao($post)
    {
        // Salvar em arquivo
        //$dados = implode(', ', array_map(function ($v, $k) { return sprintf("%s='%s'", $k, $v); }, $post, array_keys($post)));
        $fp = fopen(ABSPATH . '/log/notificacoes.csv', 'a');
        if (count($_POST) > 0) {
            $postCsv = array();
            $postCsv[] = "data=" . date("Y-m-d - H:i:s") . " - POST";
            foreach ($post as $key => $value) {
                $postCsv[] = $key . "=" . $value;
            }
            fputcsv($fp, $postCsv);
        }
        if (count($_GET) > 0) {
            $getCsv = array();
            $getCsv[] = "data=" . date("Y-m-d - H:i:s") . " - GET";
            foreach ($_GET as $key => $value) {
                $getCsv[] = $key . "=" . $value;
            }
            fputcsv($fp, $getCsv);
        }
        fclose($fp);
    }

    private static function exportarCsv(Evento $evento, $filter)
    {
        $inscritos = Inscricoes::getInstance()->getByFilterString($evento, $filter);
        $cabecalho = array();
        foreach ($inscritos[0] as $campo => $valor) {
            $cabecalho[0][ucfirst($campo)] = ucfirst($campo);
        }
        $cabecalho[0]['Nome'] = 'Nome';
        $cabecalho[0]['Cpf'] = 'Cpf';
        $cabecalho[0]['Rg'] = 'Rg';
        $cabecalho[0]['Celular'] = 'Celular';
        $cabecalho[0]['Email'] = 'Email';
        $camposExtrasEvento = $evento->getCamposExtras();
        if ($camposExtrasEvento) {
            foreach ($camposExtrasEvento as $key => $campoExtra)
                $cabecalho[0][$key] = $campoExtra;
        }

        foreach ($inscritos as $k => $inscrito) {
            /* @var $inscrito Inscricao */
            if ($inscrito->confirmado == '0') continue;
            $inscritos[$k]->Nome = $inscrito->pessoa()->nome;
            $inscritos[$k]->Cpf = $inscrito->pessoa()->cpf;
            $inscritos[$k]->Rg = $inscrito->pessoa()->getExtra('rg');
            $inscritos[$k]->Celular = $inscrito->pessoa()->celular;
            $inscritos[$k]->Email = $inscrito->pessoa()->email;

            if ($camposExtrasEvento) {
                foreach ($camposExtrasEvento as $key => $campoExtra)
                    $inscritos[$k]->$key = $inscrito->pessoa()->getExtra($key);
            }
        }

        $inscritos = array_merge($cabecalho, $inscritos);
//        echo "<pre>";
//        var_dump($inscritos);
//        die();
        Plib::array_to_csv_download($inscritos, 'Inscritos.csv');
        die();


        if ($filter == 'confirmados')
            $inscritos = Inscricoes::getInstance()->getConfirmados($evento->id);
        else if ($filter == 'rejeitados')
            $inscritos = Inscricoes::getInstance()->getRejeitados($evento->id);
        else if ($filter == '')
            $inscritos = Inscricoes::getInstance()->getByEvento($evento->id);
        $cabecalho = array();
        foreach ($inscritos[0] as $campo => $valor) {
            $cabecalho[0][ucfirst($campo)] = ucfirst($campo);
        }
        $cabecalho[0]['Nome'] = 'Nome';
        $cabecalho[0]['Cpf'] = 'Cpf';
        $cabecalho[0]['Celular'] = 'Celular';
        $cabecalho[0]['Email'] = 'Email';
        foreach ($inscritos as $k => $inscrito) {
            $inscritos[$k]->Nome = $inscrito->pessoa()->nome;
            $inscritos[$k]->Cpf = $inscrito->pessoa()->cpf;
            $inscritos[$k]->Celular = $inscrito->pessoa()->celular;
            $inscritos[$k]->Email = $inscrito->pessoa()->email;
        }

        $inscritos = array_merge($cabecalho, $inscritos);
//        echo "<pre>";
//        var_dump($inscritos);
//        die();
        Plib::array_to_csv_download($inscritos, 'Inscritos.csv');
        die();
    }


    private static function exportarCsvFullContacts(Evento $evento, $filter)
    {
        $inscritos = Inscricoes::getInstance()->getByFilterString($evento, $filter);
        $fields = array(
            'First Name' => 'nome',
            'E-Mail Address' => 'email',
            'Phone Number' => 'celular'

        );
        $fixedFields = array(
            'Phone Number Type' => 'Mobile'
        );

        $dados = array();
//        ,Last Name,,E-Mail Type,
//        Organization Name,Organization Title,
        foreach ($fields as $key => $campoExtra)
            $dados[0][$key] = $key;

        $i = 1;
        foreach ($inscritos as $k => $inscrito) {
            /* @var $inscrito Inscricao */
            $pessoa = $inscrito->pessoa();
            foreach ($fields as $key => $localField)
                $dados[$i][$key] = $pessoa->get($localField);
            foreach ($fixedFields as $key => $value)
                $dados[$i][$key] = $value;

            $i++;
        }

//        echo "<pre>";
//        var_dump($dados);
//        die();
        Plib::array_to_csv_download($dados, 'Inscritos.csv', ',');
        die();
    }

    private static function cancelarNaoConfirmados($evento)
    {
        $inscritos = Inscricoes::getInstance()->getNaoConfirmados($evento->id);

        foreach ($inscritos as $inscrito) {
            /* @var $inscrito Inscricao */
            $inscrito->vencer();
            echo "Cancelada " . $inscrito->id . " - " . $inscrito->pessoa()->nome . '<br>';
        }

    }

    /**
     * Gera a imagem de certificado do inscrito
     */
    public static function certificado($id_inscricao)
    {
        require_once PLUGINPATH . '/vendor/TiagoGouvea/PHPCertificate/PHPCertificate.php';

        /* @var $inscricao Inscricao */
        $inscricao = Inscricoes::getInstance()->getById($id_inscricao);
        if ($inscricao == null) die();

        $imagem = $inscricao->evento()->getCertificadoArquivo();

        // Criar e exibir certificado
        $certificate = new PHPCertificate($imagem);

        // Incluir nome
        $fontColor = array('r' => 0, 'g' => 0, 'b' => 0);
        $certificate->setFont('OpenSans-Light', 38, $fontColor);
        $certificate->setPosition(null, PLib::coalesce($inscricao->evento()->certificado_altura_nome, 420), 0);
        $certificate->setText(1, $inscricao->pessoa()->nome);
        $certificate->showImage();
    }

    /**
     * Exibe os extras dos inscritos agrupados e tal..
     * @param $evento
     */
    private static function extras(Evento $evento)
    {
        $extras = $evento->getCamposExtras();

        $inscritos = Inscricoes::getInstance()->getConfirmados($evento->id);

        foreach ($extras as $chave => $titulo) {
            $respostas = array();
            if (strpos($titulo, '[') !== false) {
                $titulo = substr($titulo, 0, strpos($titulo, '['));
                echo "<h1>$titulo</h1>";

                /* @var $inscrito Inscricao */
                foreach ($inscritos as $inscrito) {
                    $extrasPessoa = $inscrito->pessoa()->getExtra($chave);

                    if ($respostas[$extrasPessoa] == null) {
                        $respostas[$extrasPessoa] = 0;
                    }
                    $respostas[$extrasPessoa] = $respostas[$extrasPessoa] + 1;
//                    echo $extrasPessoa;
                }
                arsort($respostas);

                echo "<table><tr><th>Resposta</th><th>Votos</th></tr>";

                foreach ($respostas as $resposta => $qtd) {
                    echo "<tr><td>$resposta</td><td>$qtd</td></tr> ";
                }
                echo "</table>";
//                var_dump($respostas);
            }
        }
//        var_dump($extras);
    }

    private static function add($evento, $inscricao = null)
    {
        // Postando?
        if (count($_POST) > 0) {
            // Validar
            /* @var $inscricao Inscricao */
            $inscricao = Inscricoes::getInstance()->populate($_POST, $inscricao);

            if ($_POST['id'] == null) {
                $inscricao = Inscricoes::getInstance()->insert($inscricao);
            } else {
                $inscricao = Inscricoes::getInstance()->save($_POST['id'], $inscricao);
            }
            self::showList($evento);
        } else {
            require_once PLUGINPATH . '/view/inscricoes/form.php';
        }
    }

    private static function importarCsv($evento)
    {
        if (count($_POST) > 0) {
            $confirmar = $_POST['confirmar'] == 1;
            $presente = $_POST['presente'] == 1;
            $novasPessoas = 0;
            $inscricoes = 0;
            // Subir arquivo
            if ($_FILES['arquivo']) {
                $file = $_FILES['arquivo']['tmp_name'];
                $row = 1;
                $handle = fopen($file, "r");
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
//                    echo "<hr>";
//                    var_dump($data);
//                    echo "<hr>";
                    $dados = explode(";", $data[0]);
//                    var_dump($dados);
//                    echo "<hr>";
                    if ($dados) {
                        $nome = trim(ucfirst(utf8_encode($dados[0])));
                        $email = trim(strtolower($dados[1]));
                        $cpf = trim($dados[2]);
                        $celular = trim($dados[3]);
//                        echo "$nome $email $cpf $celular<br>";
                        if ($nome != null && (filter_var($email, FILTER_VALIDATE_EMAIL) != false || PLib::validade_cpf($cpf))) {
                            if ($email)
                                $valor = $email;
                            else
                                $valor = $cpf;
                            // Certificar pessoa
                            $pessoa = Pessoas::getInstance()->getByMd5($valor);
                            if ($pessoa == null) {
                                $pessoa = new Pessoa();
                                $pessoa->nome = $nome;
                                $pessoa->email = $email;
                                $pessoa->cpf = $cpf;
                                $pessoa->celular = $celular;
                                $pessoa = Pessoas::getInstance()->insert($pessoa);
                                $novasPessoas++;
                            }
                            $inscricoes++;
//                            var_dump($pessoa);echo "<hr>";
                            // Certificar inscrição
                            $inscricao = Inscricoes::getInstance()->getByEventoPessoa($evento->id, $pessoa->id);
                            if ($inscricao == null)
                                $inscricao = Inscricoes::getInstance()->certificarInscricao($evento, $pessoa);
//                            var_dump($inscricao);
                            if ($confirmar)
                                $inscricao->confirmar();
                            if ($presente)
                                $inscricao->presenca();
                        } else {
                            echo "<hr>Sem dados suficientes em um registro para importação:";
                            echo "<pre>";
                            var_dump($dados);
                            echo "</pre>";
                            echo "<b>Inscrição não importada.</b><br>";
                        }
                    }

                }
                fclose($handle);
            }
            // Abrir
            // Percorrer contatos
//            var_dump($_POST);
//            var_dump($_FILES);
            //confirmar presente

//            setFlash("Inscrições importadas: ".$inscricoes.  "<br>Novas pessoas importadas: ".$novasPessoas);
            echo "<br><h2>Resultado da Importação</h2></h2><hr>Inscrições importadas: " . $inscricoes . "<br>Novas pessoas importadas: " . $novasPessoas;

//            self::showList($evento);
        }
//        } else {
        require_once PLUGINPATH . '/view/inscricoes/importar_csv.php';
//        }
    }

    public static function setMeioPagamento($id_inscricao, $set_meio_pagamento)
    {
        /* @var $inscricao Inscricao */
        $inscricao = Inscricoes::getInstance()->getById($id_inscricao);
        $inscricao->meio_pagamento = ucfirst($set_meio_pagamento);

        $inscricao->evento()->organizador()->enviarEmail(
            $inscricao->evento()->organizador()->email,
            'Meio de Pagamento - Inscrição: ' . $id_inscricao . ' - ' . $set_meio_pagamento,
            Mensagens::getInstance()->get('email_definido_meio_pagamento_organizador', $inscricao->evento(), $inscricao->pessoa(), $inscricao)
        );
    }


}