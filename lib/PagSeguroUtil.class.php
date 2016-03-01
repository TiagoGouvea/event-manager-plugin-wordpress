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
use Integracoes;
use Monolog\Logger;
use Pessoa;
use PagSeguroAccountCredentials;
use PagSeguroPaymentRequest;
use PagSeguroServiceException;
use PagSeguroTransactionSearchResult;
use PagSeguroTransactionSearchService;
use TiagoGouvea\PLib;

class PagSeguroUtil
{

    static $erro;

    public static function getUrlPagamento(Inscricao $inscricao, $urlRetorno = null)
    {
        return self::getUrlPagSeguro($inscricao->evento(), $inscricao->pessoa(), $inscricao, $urlRetorno);
    }

    public static function getUrlPagSeguro(Evento $evento, Pessoa $pessoa, Inscricao $inscricao, $urlRetorno = null)
    {
        // Preparar pagamento PagSeguro
        $valor = number_format($inscricao->valor_inscricao, 2, '.', '');
        $reference = $inscricao->id;
        if ($reference == null || $reference == 0)
            throw new Exception("Referencia null");
        $nome = utf8_decode(trim($pessoa->nome));
        $email = trim($pessoa->email);
        $celular = ltrim(PLib::only_numbers($pessoa->celular), '0');
        if (strlen($celular) == 8)
            $ddd = 32;
        else {
            $ddd = substr($celular, 0, 2);
            $celular = substr($celular, 2);
        }

        if ($urlRetorno == null)
            $urlRetorno = get_permalink() . "?inscricao=1&id_integracao=" . $evento->id_integracao_pagseguro;

//        var_dump($ddd);
//        var_dump($celular);
//        die();

        // Incluir PagSeguro
        include_once PLUGINPATH . '/vendor/PagSeguro/PagSeguroLibrary.php';

        // Criar requisição PagSeguro
        $paymentRequest = new PagSeguroPaymentRequest();
        $paymentRequest->setCurrency("BRL");
        $paymentRequest->setRedirectURL($urlRetorno);
        $paymentRequest->addItem('0001', 'Inscrição para ' . $evento->titulo, 1, $valor);
        $paymentRequest->setReference($reference);
        $paymentRequest->setSender($nome, $email, $ddd, $celular);
        $paymentRequest->setRedirectUrl($urlRetorno);

        if ($reference == null || $reference < 1) {
            throw new Exception("Erro na inscrição. Falta de referencia para pagamento. Inscricao: " . $inscricao->id);
        }
        try {
            // Obter integração
            $integracao = $evento->getIntegracaoPagSeguro();
            if ($integracao == null) {
                enviarEmail(TGO_EVENTO_ADMIN, 'Evento sem gateway informado','O evento ' . $evento->titulo . ' está recebendo dados de pagseguro, porém, se integração definida.');
            }

            // Criar credenticias
            $credentials = new PagSeguroAccountCredentials($integracao->client, $integracao->token);
            $urlPagSeguro = $paymentRequest->register($credentials);
            return $urlPagSeguro;
        } catch (PagSeguroServiceException $e) {
            // "Desvendar" erros do pagseguro..
            if (strpos($e->getMessage(), 'senderPhone') != false) {
                self::$erro = "O telefone informado ($ddd)$celular não foi aceito pelo PagSeguro. Clique em voltar e informe um novo número.";
            } else if (strpos($e->getMessage(), 'senderName') != false) {
                self::$erro = "O nome informado ($nome) não foi aceito pelo PagSeguro. Clique em voltar e informe corretamente.";
            } else if (strpos($e->getMessage(), 'senderEmail') != false) {
                self::$erro = "O email informado ($email) não foi aceito pelo PagSeguro. Clique em voltar e informe corretamente.";
            } else if (strpos($e->getMessage(), 'shipping') != false) {
                self::$erro = "O endereço informado não foi aceito pelo PagSeguro. Clique em voltar e informe corretamente.";
            } else if (strpos($e->getMessage(), 'senderAreaCode') != false) {
                self::$erro = "O DDD do telefone deve ter apenas dois dígitos. Clique em voltar e informe corretamente.";
            } else {
                self::$erro = "Ocorreu um erro desconhecido no PagSeguro, o desenvolvedor foi avisado por email para rápida correção. Tente novamente em algumas horas.";
                $evento->organizador()->enviarEmail(TGO_EVENTO_ADMIN, 'Erro com PagSeguro 1',
                    $e->getMessage() .
                    '<br><br><br>post:<br>' . print_r($_POST, true) .
                    '<br><br><br>pessoa:' . print_r($pessoa, true) .
                    '<br><br><br>inscrição:' . print_r($inscricao, true) .
                    '<br><br><br>exception:' . print_r($e, true) .
                    '<br><br><br>paymentRequest:' . print_r($paymentRequest, true));
            }
            if (self::$erro != null) {
                echo self::$erro;
            }
            throw new Exception($e);
            return false;
        }
    }

    public static function redirecionar($evento, $pessoa, $inscricao)
    {
        $url = self::getUrlPagSeguro($evento, $pessoa, $inscricao);
        header('Location: ' . $url);
        exit;
    }

    public static function getTransacoesDatas($integracao, $initialDate, $finalDate)
    {
        try {
            // Incluir PagSeguro
            include_once PLUGINPATH . '/vendor/PagSeguro/PagSeguroLibrary.php';
            // Obter a transação
            // Verificar situação
            $credentials = new PagSeguroAccountCredentials($integracao->client, $integracao->token);
            $transacoes = PagSeguroTransactionSearchService::searchByDate($credentials, 1, 1000, $initialDate, $finalDate);
        } catch (Exception $e) {
            echo "Exception em getTransacoesDatas.";
        }

        return $transacoes;
    }

    public static function getTransacaoNotificacao(Integracao $integracao, $notificationCode)
    {
        try {
            // Incluir PagSeguro
            include_once PLUGINPATH . '/vendor/PagSeguro/PagSeguroLibrary.php';
            // Obter a transação
            // Verificar situação
            $credentials = new PagSeguroAccountCredentials($integracao->client, $integracao->token);
            $transacao = \PagSeguroNotificationService::checkTransaction($credentials, $notificationCode); // PagSeguroTransactionSearchService::searchByCode($credentials, $notificationCode);
        } catch (PagSeguroServiceException $e) {
            throw new Exception($e->getMessage());
        }

        return $transacao;
    }

    /**
     * Retorna em string o status da transação
     * @param $status_gateway
     */
    public static function getStatusTituloString($status_gateway)
    {
        $status = array(
            1 => 'Aguardando pagamento',
            2 => 'Em análise',
            3 => 'Paga',
            4 => 'Disponível',
            5 => 'Em disputa',
            6 => 'Devolvida',
            7 => 'Cancelada'
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
            1 => 'O comprador iniciou a transação, mas até o momento o PagSeguro não recebeu nenhuma informação sobre o pagamento.',
            2 => 'O comprador optou por pagar com um cartão de crédito e o PagSeguro está analisando o risco da transação.',
            3 => 'A transação foi paga pelo comprador e o PagSeguro já recebeu uma confirmação da instituição financeira responsável pelo processamento.',
            4 => 'A transação foi paga e chegou ao final de seu prazo de liberação sem ter sido retornada e sem que haja nenhuma disputa aberta.',
            5 => 'O comprador, dentro do prazo de liberação da transação, abriu uma disputa.',
            6 => 'O valor da transação foi devolvido para o comprador.',
            7 => 'A transação foi cancelada sem ter sido finalizada.'
        );
        return $status[$status_gateway];
    }

    public static function getFormaPagamentoTituloString($forma_pagamento_gateway)
    {
        $status = array(
            1 => 'Cartão de crédito',
            2 => 'Boleto',
            3 => 'Débito Online (TEF)',
            4 => 'Saldo PagSeguro',
            5 => 'Oi Paggo'
        );
        return $status[$forma_pagamento_gateway];
    }

    public static function processarTransacoes($integracao, PagSeguroTransactionSearchResult $transacoes)
    {
        //PLib::var_dump(count($transacoes->getTransactions()));
        if (count($transacoes->getTransactions()) > 0) {
            $results = Inscricoes::getInstance()->processarTransacoes($integracao->servico, $transacoes->getTransactions());
            if (count($results['inscricoes']) > 0) {
                echo "<h2>Inscrições</h2>";
                echo "<table>
                        <thead>
                            <th>Codigo<br>Gateway</th>
                            <th>Ticket</th>
                            <th>Evento</th>
                            <th>Pessoa</th>
                            <th>Forma<br>Pagamento</th>
                            <th>Status<br>Transação</th>
                            <th>Ultima<br>Atualização</th>
                            <th>Pagamento</th>
                            <th>Valor<br>Pago</th>
                            <th>Sincronização<br>atual</th>
                        </thead>";
                /** @var $inscricao Inscricao */
                foreach ($results['inscricoes'] as $inscricao) {
                    echo "<tr>
                            <td>" . $inscricao->codigo_gateway . "</td>
                            <td>" . $inscricao->id . "</td>
                            <td><a href='admin.php?page=Eventos&action=view&id='" . $inscricao->id_evento . "'>" . $inscricao->evento()->titulo . "</a></td>
                            <td><a href='admin.php?page=Pessoas&action=view&id='" . $inscricao->id_pessoa . "'>" . $inscricao->pessoa()->primeiro_nome() . "</a></td>
                            <td>" . PagSeguroUtil::getFormaPagamentoTituloString($inscricao->forma_pagamento_gateway) . "</td>
                            <td>" . $inscricao->_status_gateway . "</td>
                            <td>" . PLib::date_relative($inscricao->data_atualizacao_gateway, true) . "</td>
                            <td>" . PLib::date_relative($inscricao->data_pagamento, true) . "</td>
                            <td>" . ($inscricao->valor_pago > 0 ? PLib::format_cash($inscricao->valor_pago) : '') . "</td>
                            <td>" . $inscricao->_observacao . "</td>
                          </tr>";
                    //                            var_dump($inscricao);
                }
                echo "</table>";
            }
            if (count($results['falhas']) > 0) {
                echo "<h2>Falhas</h2>";
                foreach ($results['falhas'] as $falha) {
                    echo $falha . '<br>';
                }
            }
        } else
            echo "Sem transações no período<br>";
    }


}

