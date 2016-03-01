<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 04/04/15
 * Time: 10:37
 */

namespace lib;

use Evento;
use Exception;
use Inscricao;
use Inscricoes;
use Integracao;
use Pessoa;
use stdClass;
use TiagoGouvea\PLib;

use Cielo\Cielo;
use Cielo\CieloException;
use Cielo\Transaction;
use Cielo\Holder;
use Cielo\PaymentMethod;

class CieloUtil
{
    static $erro;

    public static function getUrlPagamento(Inscricao $inscricao, $urlRetorno = null)
    {
        return self::getUrlCielo($inscricao->evento(), $inscricao->pessoa(), $inscricao, $urlRetorno);
    }

    public static function getUrlCielo(Evento $evento, Pessoa $pessoa, Inscricao $inscricao, $urlRetorno = null)
    {
        $integracao = $evento->getIntegracaoCielo();

        $valor = number_format($inscricao->valor_inscricao, 2, '', '');

        $order = new stdClass();
        $order->OrderNumber = $inscricao->id;
        $order->SoftDescriptor = $evento->organizador()->titulo;
        $order->Cart = new stdClass();
        $order->Cart->Items = array();
        $order->Cart->Items[0] = new stdClass();
        $order->Cart->Items[0]->Name = $evento->titulo;
        $order->Cart->Items[0]->Description = 'Inscrição em ' . $evento->titulo;
        $order->Cart->Items[0]->UnitPrice = $valor;
        $order->Cart->Items[0]->Quantity = 1;
        $order->Cart->Items[0]->Type = 'Service';
        $order->Shipping = new stdClass();
        $order->Shipping->Type = 'WithoutShipping';
        $order->Payment = new stdClass();
        $order->Payment->BoletoDiscount = 5;
        $order->Payment->DebitDiscount = 5;
        $order->Customer = new stdClass();
        $order->Customer->Identity = $pessoa->cpf;
        $order->Customer->FullName = $pessoa->nome;
        $order->Customer->Email = $pessoa->email;
        $order->Customer->Phone = PLib::only_numbers($pessoa->celular);
        //$order->Options = new stdClass();
        //$order->Options->AntifraudEnabled = false;

//        var_dump($order);
//
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://cieloecommerce.cielo.com.br/api/public/v1/orders');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($order));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'MerchantId: ' . $integracao->token,
            'Content-Type: application/json'
        ));

        $response = curl_exec($curl);
        curl_close($curl);

//        var_dump($response);die("a");

//        PLib::var_dump($response,'response');
        if ($response != null && strlen($response) > 1) {
            $json = json_decode($response);
            if ($json != false) {
//                PLib::var_dump($json,'json');
//                die();
                // Url de checkout
                if ($json->settings != null && $json->settings->checkoutUrl)
                    return $json->settings->checkoutUrl;
            } else {
                die('erro no json decode cielo?');
            }
        } else {

        }

        return false;
    }

    public static function redirecionar($evento, $pessoa, $inscricao)
    {
        $url = self::getUrlCielo($evento, $pessoa, $inscricao);
        header('Location: ' . $url);
        exit;
    }

    public static function getTransacoesDatas($integracao, $initialDate, $finalDate)
    {
        try {
            // Incluir Cielo
            // Obter a transação
            // Verificar situação
//            $credentials = new CieloAccountCredentials($integracao->client, $integracao->token);
//            $transacoes = CieloTransactionSearchService::searchByDate($credentials, 1, 1000, $initialDate, $finalDate);
        } catch (Exception $e) {
//            echo "Exception em getTransacoesDatas.";
        }

//        return $transacoes;
    }

    public static function getTransacaoNotificacao(Integracao $integracao, $notificationCode)
    {
//        try {
//            // Incluir Cielo
//            include_once PLUGINPATH . '/vendor/Cielo/CieloLibrary.php';
//            // Obter a transação
//            // Verificar situação
//            $credentials = new CieloAccountCredentials($integracao->client, $integracao->token);
//            $transacao = \CieloNotificationService::checkTransaction($credentials, $notificationCode); // CieloTransactionSearchService::searchByCode($credentials, $notificationCode);
//        } catch (CieloServiceException $e) {
//            throw new Exception($e->getMessage());
//        }
//
//        return $transacao;
    }

    /**
     * Retorna em string o status da transação
     * @param $status_gateway
     */
    public static function getStatusTituloString($status_gateway)
    {
        $status = array(
            1 => 'Pendente',
            2 => 'Pago',
            3 => 'Negado',
            4 => 'Cancelado',
            6 => 'Não Finalizado',
            7 => 'Autorizado'
        );
        return $status[$status_gateway];
    }

    /**
     * Retorna em string o status da transação
     * @param $status_gateway
     */
    public static function getStatusDescricaoString($status_gateway)
    {
        $status = array(
            1 => 'O comprador iniciou a transação, mas até o momento o Cielo não recebeu nenhuma informação sobre o pagamento.',
            2 => 'O comprador optou por pagar com um cartão de crédito e o Cielo está analisando o risco da transação.',
            3 => 'A transação foi paga pelo comprador e o Cielo já recebeu uma confirmação da instituição financeira responsável pelo processamento.',
            4 => 'A transação foi paga e chegou ao final de seu prazo de liberação sem ter sido retornada e sem que haja nenhuma disputa aberta.',
            5 => 'O comprador, dentro do prazo de liberação da transação, abriu uma disputa.',
            6 => 'O valor da transação foi devolvido para o comprador.',
            7 => 'A transação foi cancelada sem ter sido finalizada.'
        );
        return $status[$status_gateway];
    }

    public static function getFormaPagamentoTituloString($forma_pagamento_gateway)
    {
//        $status = array(
//            1 => 'Cartão de crédito',
//            2 => 'Boleto',
//            3 => 'Débito Online (TEF)',
//            4 => 'Saldo Cielo',
//            5 => 'Oi Paggo'
//        );
//        return $status[$forma_pagamento_gateway];
//        Métodos de pagamento - payment_method_type:
//
//Valor	Descrição
//1	Cartão de Crédito
//2	Boleto Bancário
//3	Débito Online
//4	Cartão de Débito
//Bandeira - payment_method_brand:
//
//Valor	Descrição
//1	Visa
//2	Mastercad
//3	AmericanExpress
//4	Diners
//5	Elo
//6	Aura
//7	JCB
    }

    public static function processarTransacoes($integracao, CieloTransactionSearchResult $transacoes)
    {
//        //PLib::var_dump(count($transacoes->getTransactions()));
//        if (count($transacoes->getTransactions()) > 0) {
//            $results = Inscricoes::getInstance()->processarTransacoes($integracao->servico, $transacoes->getTransactions());
//            if (count($results['inscricoes']) > 0) {
//                echo "<h2>Inscrições</h2>";
//                echo "<table>
//                        <thead>
//                            <th>Codigo<br>Gateway</th>
//                            <th>Ticket</th>
//                            <th>Evento</th>
//                            <th>Pessoa</th>
//                            <th>Forma<br>Pagamento</th>
//                            <th>Status<br>Transação</th>
//                            <th>Ultima<br>Atualização</th>
//                            <th>Pagamento</th>
//                            <th>Valor<br>Pago</th>
//                            <th>Sincronização<br>atual</th>
//                        </thead>";
//                /** @var $inscricao Inscricao */
//                foreach ($results['inscricoes'] as $inscricao) {
//                    echo "<tr>
//                            <td>" . $inscricao->codigo_gateway . "</td>
//                            <td>" . $inscricao->id . "</td>
//                            <td><a href='admin.php?page=Eventos&action=view&id='" . $inscricao->id_evento . "'>" . $inscricao->evento()->titulo . "</a></td>
//                            <td><a href='admin.php?page=Pessoas&action=view&id='" . $inscricao->id_pessoa . "'>" . $inscricao->pessoa()->primeiro_nome() . "</a></td>
//                            <td>" . CieloUtil::getFormaPagamentoTituloString($inscricao->forma_pagamento_gateway) . "</td>
//                            <td>" . $inscricao->_status_gateway . "</td>
//                            <td>" . PLib::date_relative($inscricao->data_atualizacao_gateway, true) . "</td>
//                            <td>" . PLib::date_relative($inscricao->data_pagamento, true) . "</td>
//                            <td>" . ($inscricao->valor_pago > 0 ? PLib::format_cash($inscricao->valor_pago) : '') . "</td>
//                            <td>" . $inscricao->_observacao . "</td>
//                          </tr>";
//                    //                            var_dump($inscricao);
//                }
//                echo "</table>";
//            }
//            if (count($results['falhas']) > 0) {
//                echo "<h2>Falhas</h2>";
//                foreach ($results['falhas'] as $falha) {
//                    echo $falha . '<br>';
//                }
//            }
//        } else
//            echo "Sem transações no período<br>";
    }

    public static function getTransacaoPedido(Integracao $integracao, $numeroPedido)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <requisicao-consulta-chsec id=\"a51489b1-93d5-437f-bb4f-5b932fade248\" versao=\"1.2.1\">
                <numero-pedido>$numeroPedido</numero-pedido>
                <dados-ec>
                    <numero>$integracao->client</numero>
                    <chave>$integracao->token</chave>
                </dados-ec>
            </requisicao-consulta-chsec>";

        $return = self::enviar($xml);
        echo "<pre>";
        var_dump($xml);
    }

    // Envia Requisição
    public static function Enviar($vmPost, $transacao)
    {
        // ENVIA REQUISIÇÃO SITE CIELO
        $vmResposta = self::httprequest("https://qasecommerce.cielo.com.br/servicos/ecommwsec.do", "mensagem=" . $vmPost);
        var_dump($vmResposta);

        //VerificaErro($vmPost, $vmResposta);

        return simplexml_load_string($vmResposta);
    }


    // Envia requisição
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
        curl_setopt($sessao_curl, CURLOPT_CAINFO, getcwd() ."/ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt");
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

        var_dump($resultado);

        curl_close($sessao_curl);
        if ($resultado) {
            return $resultado;
        } else {
            return curl_error($sessao_curl);
        }
    }

}

