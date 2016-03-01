<?php
use lib\PagSeguroUtil;
use TiagoGouvea\PLib;

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 15/04/15
 * Time: 14:53
 */
class Mensagens extends \TiagoGouvea\WPDataMapper\WPSimpleDAO
{
    private $organizador;
    private $indices;
    private $obterChave;

    function init()
    {
        parent::_init(
            "ev_mensagens",
            'Mensagem',
            "id");
    }

    /**
     * @return Mensagens
     */
    static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Obtem a mensagem selecionada, buscando em três fontes:
     * 1 - Nas configurações do evento
     * 2 - Nas configurações do organizador
     * 3 - Mensagem padrão definida por mim nesta classe
     * @param $indice
     * @param Evento $evento
     * @param Pessoa $pessoa
     * @param Inscricao $inscricao
     */
    function get($indice, Evento $evento = null, Pessoa $pessoa = null, Inscricao $inscricao = null, $nl2br = false)
    {
        $mensagem = null;

        // 1 - Obter do evento
        if ($evento != null) {
            $mensagem = $this->getMensagem($indice, "evento_" . $evento->id);

            // 2 - Obter do organizador
            if ($mensagem == null && $evento->id_organizador != null)
                $mensagem = $this->getMensagem($indice, "organizador_" . $evento->id_organizador);
        }

        // 3 - Obter padrão
        if ($mensagem == null) {
            $mensagem = self::getDefault($indice);
        }

        if (strpos($mensagem, "%") !== false)
            $mensagem = $this->substituirVariaveis($mensagem, $evento, $pessoa, $inscricao);

        if ($nl2br)
            $mensagem = nl2br($mensagem);
        return $mensagem;
    }

    function getUnico($indice, $trazerPadrao = true)
    {
        $mensagem = null;

        if ($this->obterChave != null) {
            $mensagemObj = $this->getByIndiceChave($indice, $this->obterChave);
            if ($mensagemObj != null) {
                $mensagem = $mensagemObj->mensagem;
            }
        } else
            throw new Exception("getUnico sem obterApenas setado");

        // 3 - Obter padrão - apenas quando config
        if ($this->obterChave == 'config' && $mensagem == null)
            $mensagem = self::getDefault($indice);

        return $mensagem;
    }

    function getMensagem($indice, $chave, $obterPadrao = false)
    {
        $mensagem = null;

        $mensagemObj = $this->getByIndiceChave($indice, $chave);
        if ($mensagemObj != null)
            $mensagem = $mensagemObj->mensagem;

        if ($mensagem == null && $obterPadrao)
            $mensagem = self::getDefault($indice);

        return $mensagem;
    }

    /**
     * @param $indice
     * @param $chave
     * @return Mensagem
     */
    private function getByIndiceChave($indice, $chave)
    {
        $sql = 'SELECT * FROM ev_mensagens WHERE indice="' . $indice . '" AND chave="' . $chave . '"';
//        \TiagoGouvea\PLib::var_dump($sql,"getBy");
        $record = $this->wpdb()->get_row($sql, ARRAY_A);
        if ($record)
            return $this->populate($record);
    }


    private function getIndices()
    {
        if ($this->indices == null)
            $this->indices = array(
                "wizard_fim_inscricao_confirmada",
                "wizard_fim_confirmacao_posterior",
                "wizard_fim_pre_inscricao",
                "wizard_fim_pagamento_confirmado",
                "wizard_fim_pagamento_aguardando_confirmacao",
                "wizard_fim_fila_espera",
                "email_realizacao_inscricao",
                "email_realizacao_inscricao_organizador",
                "email_confirmacao_inscricao",
                "email_confirmacao_inscricao_qrcode",
                "email_confirmacao_inscricao_organizador",
                "email_cancelamento_inscricao_tempo_boleto_gateway",
                "email_cancelamento_inscricao_tempo",
                "email_cancelamento_inscricao_vagas",
                "email_cancelamento_inscricao_gateway",
                "email_cancelamento_inscricao_gateway_desfeito",
                "email_cancelamento_inscricao_desfeita_gateway",
                "email_cancelamento_inscricao_generica",
                "email_confirme_agora",
                "email_lembrete_senha",
                "email_definido_meio_pagamento_organizador",
            );

        return $this->indices;
    }

    public function setOrganizador($id_organizador)
    {
        $this->obterChave = 'organizador_' . $id_organizador;
    }

    public function setEvento($id_evento)
    {
        $this->obterChave = 'evento_' . $id_evento;
    }

    public function setConfig()
    {
        $this->obterChave = 'config';
    }

    public function getChave()
    {
        return $this->obterChave;
    }

    public function savePost($post)
    {
        $chave = $post['chave'];
        /** @var $wpdb wpdb */
//        \TiagoGouvea\PLib::var_dump($post,"Post em savePost");
        foreach ($this->getIndices() as $indice) {
            $excluir = false;
            // Se está sendo postado, registrar valor
            if (isset($post[$indice])) {
                $mensagemPost = $post[$indice];
                $mensagemBanco = $this->getDefault($indice);
                // É diferente do padrão?
                $salvar = preg_replace("/[^A-Za-z0-9?!]/", '', $mensagemPost) !=
                    preg_replace("/[^A-Za-z0-9?!]/", '', $mensagemBanco);
//                var_dump($salvar);
                if ($salvar) {
//                    echo "<pre>";
//                    var_dump($mensagemPost);
//                    var_dump($this->getDefault($indice));
//                    echo "</pre>";
                    $mensagem = Mensagens::getInstance()->getByIndiceChave($indice, $chave);
                    if ($mensagem == null) {
                        $mensagem = new Mensagem();
                        $mensagem->chave = $chave;
                        $mensagem->indice = $indice;
                        $mensagem->mensagem = $mensagemPost;
                        Mensagens::insert($mensagem);
                    } else {
                        $mensagem->mensagem = $mensagemPost;
                        Mensagens::save($mensagem->id, $mensagem);
                    }
                } else {
                    $excluir = true;
                }
            } else {
                // Se não está sendo postado, e existir, apagar
                $excluir = true;
            }

            // Ele sempre exclui... pensar melhor depois nisso
            if ($excluir)
                $this->wpdb()->delete('ev_mensagens', array('indice' => $indice, 'chave' => $chave));
        }
    }


    public function substituirVariaveis($mensagem, Evento $evento = null, Pessoa $pessoa = null, Inscricao $inscricao = null)
    {
        // Evento
        if ($evento != null) {
            $mensagem = str_replace('%evento_titulo%', $evento->titulo, $mensagem);
            $mensagem = str_replace('%evento_data_hora%', mb_strtolower(PLib::date_relative($evento->data . " " . $evento->hora, true, false)), $mensagem);
            if (function_exists('get_permalink'))
                $mensagem = str_replace('%link_evento%', get_permalink($evento->id), $mensagem);
            // Evento Local
            if ($evento->id_local != null) {
                $mensagem = str_replace('%evento_local%', $evento->local()->titulo, $mensagem);
                $mensagem = str_replace('%evento_local_endereco%', $evento->getLocal()->endereco, $mensagem);
                $mensagem = str_replace('%evento_local_telefone%', $evento->getLocal()->telefone, $mensagem);
            }
        }

        // Pessoa
        if ($pessoa != null) {
            $mensagem = str_replace('%pessoa_nome%', mb_convert_case(mb_strtolower($pessoa->nome), MB_CASE_TITLE, "UTF-8"), $mensagem);
            $mensagem = str_replace('%pessoa_primeiro_nome%', $pessoa->primeiro_nome(), $mensagem);
            $mensagem = str_replace('%pessoa_celular%', $pessoa->celular, $mensagem);
            $mensagem = str_replace('%pessoa_email%', $pessoa->email, $mensagem);
            $mensagem = str_replace('%pessoa_password%', $pessoa->getPassword(), $mensagem);
            if (strpos($mensagem, 'pessoa_extras') !== false)
                $mensagem = str_replace('%pessoa_extras%', $pessoa->getExtrasExibicao(), $mensagem);
            if (strpos($mensagem, 'pessoa_extras_social') !== false)
                $mensagem = str_replace('%pessoa_extras_social%', $pessoa->getExtrasExibicao(null,true,false), $mensagem);
        }

        // Inscrição
        if ($inscricao != null) {
            $mensagem = str_replace('%id_inscricao%', $inscricao->id, $mensagem);
            $mensagem = str_replace('%ticket%', $inscricao->id * 13, $mensagem);
            $mensagem = str_replace('%data_confirmacao%', $inscricao->data_confirmacao, $mensagem);
            $mensagem = str_replace('%valor_inscricao%', PLib::format_cash($inscricao->valor_inscricao), $mensagem);
            $mensagem = str_replace('%valor_pago%', PLib::format_cash($inscricao->valor_pago), $mensagem);
            $mensagem = str_replace('%forma_pagamento%', PagSeguroUtil::getFormaPagamentoTituloString($inscricao->forma_pagamento_gateway), $mensagem);
            $mensagem = str_replace('%meio_pagamento%', $inscricao->meio_pagamento , $mensagem);
            if (function_exists('get_permalink')) {
                $mensagem = str_replace('%link_pagamento%', $inscricao->getLinkPagamento(), $mensagem);
                $mensagem = str_replace('%link_avaliacao%', $inscricao->getLinkAvaliacao(), $mensagem);
                $mensagem = str_replace('%link_inscrito%', $inscricao->getLinkPagamento(), $mensagem);
                $mensagem = str_replace('%link_certificado%', $inscricao->getLinkCertificado(), $mensagem);
            }
            $mensagem = str_replace('%link_qrcode%', $inscricao->getLinkQrCode(), $mensagem);
            $mensagem = str_replace('%resumo_inscricoes%','Inscritos: '.$inscricao->evento()->qtdInscritos().' - Confirmados: '.$inscricao->evento()->qtdConfirmados(),$mensagem);
        }

        return $mensagem;
    }


    private function getDefault($indice)
    {
        $mensagem = null;

        if ($indice == "wizard_fim_inscricao_confirmada") {
            $mensagem = "Sua inscrição foi realizada com sucesso e já está confirmada!";
            if (TGO_EVENTO_QRCODE===true){
                $mensagem.="<br><br>Para acelerar seu check-in, salve o QRCode abaixo em seu celular (ou tire uma foto dele) e apresente-o ao dispositivo na entrada do evento.

                <img src='%link_qrcode%'>
                ";
            }
        }


        if ($indice == "wizard_fim_confirmacao_posterior")
            $mensagem = "Sua inscrição <b>ainda não foi confirmada</b>. O organizador irá confirmar as inscrições posteriomente.

                         Aguarde a chegada do email de confirmação.";

        if ($indice == "wizard_fim_fila_espera")
            $mensagem = "As vagas para o evento já se esgoram. Registramos suas informações e lhe incluímos na fila de espera.

                        Caso novas vagas abram entraremos em contato por email.";

        if ($indice == "wizard_fim_pre_inscricao")
            $mensagem = "Você está <b>pré-inscrito</b> no \"%evento_titulo%\"!

                         <b>Atenção:</b> sua inscrição ainda <b>não</b> está confirmada. No dia em que abrirmos as inscrições você será avisado por email para poder concluir sua inscrição e realizar o pagamento.

                         Portanto, aguarde nosso email.

                         Até mais!";

        if ($indice == "wizard_fim_pagamento_confirmado")
            $mensagem = "Seu pagamento foi recebido e sua inscrição confirmada com sucesso!

                         Você receberá logo mais um email com todos os detalhes.

                         Obrigado por participar!";

        if ($indice == "wizard_fim_pagamento_aguardando_confirmacao")
            $mensagem = "Seu pagamento ainda não foi confirmado.

                         Assim que recebermos a confirmação do pagamento, confirmaremos sua inscrição e lhe enviaremos um email com todos os detalhes.";

        if ($indice == "email_confirmacao_inscricao")
            $mensagem = "Olá %pessoa_nome%,

                        Sua inscrição para \"%evento_titulo%\" acaba de ser confirmada. Obrigado por participar!

                        O evento acontecerá no %evento_local%, %evento_data_hora%.

                        Endereço completo:
                        %evento_local_endereco%

                        Em caso de duvidas, entre em contato conosco.

                        Abraços";

        if ($indice == "email_realizacao_inscricao_pagar")
            $mensagem = "Olá %pessoa_nome%,

                        Sua inscrição para \"%evento_titulo%\" foi registrada com sucesso, mas ainda não foi confirmada.

                        Assim que o pagamento for realizado confirmaremos sua inscrição.

                        Em caso de duvidas, entre em contato conosco.

                        Abraços";

        if ($indice=="email_realizacao_inscricao_organizador"){
            $mensagem = "Dados da inscrição realizada (não confirmada ainda)

                        <b>Pessoa</b>
                        Nome: %pessoa_nome%
                        Email: %pessoa_email%
                        Celular: %pessoa_celular%

                        %pessoa_extras_social%

                        <b>Inscrição</b>
                        Ticket: %id_inscricao%
                        Data Confirmação: %data_confirmacao%
                        Valor Inscrição: %valor_inscricao%

                        <b>Informações Extras do inscrito</b>
                        %pessoa_extras%

                        <b>Resumo das Inscrições para \"%evento_titulo%\"</b>
                        %resumo_inscricoes%

                        Abraços";
        }

        if ($indice == "email_confirmacao_inscricao_qrcode")
            $mensagem = "Olá %pessoa_nome%,

                        Sua inscrição para \"%evento_titulo%\" acaba de ser confirmada. Obrigado por participar!

                        Realize seu chekin rapidamente usando o QRCode abaixo. Se preferir faça o <a href='%link_qrcode%'>download do QRCode</a>  (ou tire uma foto dele), salve-o na galeria de seu celular e apresente-o para o dispositivo na entrada.

                        <img src='%link_qrcode%'>

                        O evento acontecerá no %evento_local%, %evento_data_hora%.

                        Endereço completo:
                        %evento_local_endereco%

                        Em caso de duvidas, entre em contato conosco.

                        Abraços";

        if ($indice == "email_confirmacao_inscricao_organizador")
            $mensagem = "Dados da inscrição confirmada

                        <b>Pessoa</b>
                        Nome: %pessoa_nome%
                        Email: %pessoa_email%
                        Celular: %pessoa_celular%

                        %pessoa_extras_social%

                        <b>Inscrição</b>
                        Ticket: %id_inscricao%
                        Data Confirmação: %data_confirmacao%
                        Valor Pago: %valor_pago%
                        Forma Pagamento: %forma_pagamento%

                        <b>Informações Extras do inscrito</b>
                        %pessoa_extras%

                         <b>Resumo das Inscrições para \"%evento_titulo%\"</b>
                        %resumo_inscricoes%

                        Abraços";

        if ($indice == "email_cancelamento_inscricao_desfeita_gateway")
            $mensagem = "Olá %pessoa_nome%,

                         Recebemos do PagSeguro a informação que seu pagamento foi cancelado pela operadora do cartão de crédito, portanto tivemos que cancelar sua inscrição.

                         Entre em contato com o PagSeguro ou com sua operadora de cartão de crédito para solucionar este problema e se <a href='%link_evento%'>inscreva novamente</a> quando desejar.

                         Se preferir, realize o pagamento por outra forma de pagamento.

                         Abraços";

        if ($indice == "email_cancelamento_inscricao_gateway")
            $mensagem = "Olá %pessoa_nome%,

                         Recebemos do PagSeguro a informação que não foi possível concluir seu pagamento. Recomendamos que entre em contato com o <a href='http://pagseguro.uol.com.br'>PagSeguro</a> ou com sua operadora de cartão de crédito para buscar uma solução.

                         Cancelamos esta inscrição, mas <a href='%link_evento%'>inscreva-se novamente</a> quando desejar. Não perca a oportunidade de participar!

                         Se preferir, realize o pagamento por outra forma de pagamento.

                         Abraços";

        if ($indice == "email_cancelamento_inscricao_gateway_desfeito")
            $mensagem = "Olá %pessoa_nome%,

                         O valor referente a sua inscrição em \"%evento_titulo%\" foi devolvido ao <a href='http://pagseguro.uol.com.br'>PagSeguro</a>.

                         Cancelamos esta inscrição, mas esperamos poder receber você em outras oportunidades futuras!

                         Abraços";

        if ($indice == "email_cancelamento_inscricao_tempo_boleto_gateway")
            $mensagem = "Olá %pessoa_nome%,

                         Ainda não recebemos a confirmação do seu pagamento da inscrição para \"%evento_titulo%\", portanto tivemos que liberar sua vaga.

                         <a href='%link_evento%'>Inscreva-se novamente</a> quando desejar. Não perca a oportunidade de participar!

                         Se preferir pague com <a href='%link_pagamento%'>cartão de crédito</a> e seja confirmado ainda hoje.

                         Em caso de duvidas entre em contato conosco respondendo este email.

                         Abraços";

        if ($indice == "email_cancelamento_inscricao_vagas")
            $mensagem = "Olá %pessoa_primeiro_nome%,

                         Tivemos um grande número de inscritos para o evento \"%evento_titulo%\" e não tivemos como confirmar todos os inscritos.

                         Esperamos que entenda a situação.

                         Mas, não fique triste, você receberá emails de todos os próximos eventos que iremos realizar e poderá participar em ocasiões futuras.

                         Abraços";

        if ($indice == "email_cancelamento_inscricao_tempo")
            $mensagem = "Olá %pessoa_nome%,

                         Ainda não recebemos o pagamento de sua inscrição para \"%evento_titulo%\", portanto liberamos sua vaga para que outros interessados possam participar.

                         <a href='%link_evento%'>Inscreva-se novamente</a> quando desejar. Não perca a oportunidade de participar! Se preferir pague com cartão de crédito e seja confirmado ainda hoje.

                         Em caso de duvidas entre em contato conosco respondendo este email.

                         Abraços";

        if ($indice == "email_cancelamento_inscricao_gateway_organizador")
            $mensagem = "Dados da inscrição cancelada (desfeita) por falha no pagamento:

                        Inscrito: %pessoa_nome%
                        Ticket: %id_inscricao%
                        Evento: %evento_titulo%

                        Abraços";

        if ($indice == "email_cancelamento_inscricao_generica")
            $mensagem = "Olá %pessoa_primeiro_nome%,

                         Enviamos este email para informar que sua inscrição foi cancelada.

                         Em caso de duvidas entre em contato conosco.

                         Abraços";

        if ($indice == "email_confirme_agora")
            $mensagem = "Olá %pessoa_nome%,

                         Ainda não recebemos a confirmação de pagamento de sua inscrição para \"%evento_titulo%\" e não podemos segurar sua vaga por muito tempo...

                         Se você já realizou o pagamento, entre em contato conosco. Caso contrário, realize o pagamento <a href='%link_pagamento%'>agora</a> e garanta já sua vaga!

                         Se preferir pague com <a href='%link_pagamento%'>cartão de crédito</a> e seja confirmado ainda hoje.

                         Abraços";

        if ($indice == "email_lembrete_senha")
            $mensagem = "Olá %pessoa_nome%,

                         Segue seu lembrete de senha.

                         <b>Login:</b> %pessoa_email%
                         <b>Senha:</b> %pessoa_password%

                         Caso ainda tenha dificuldades no acesso fale conosco.

                         Abraços";

        if ($indice == "email_definido_meio_pagamento_organizador")
            $mensagem = "O inscrito definiu o meio de pagamento

                        <b>Inscrição</b>
                        Ticket: %id_inscricao%
                        Nome: %pessoa_nome%
                        Email: %pessoa_email%
                        Celular: %pessoa_celular%
                        Valor Inscricao: %valor_inscricao%
                        Meio de Pagamento Selecionado: %meio_pagamento%

                        Abraços";


        if ($mensagem == null) {
            throw new Exception("Mensagem não existente: " . $indice);
        }

        $mensagem = preg_replace('/ +/', ' ', $mensagem);
        $mensagem = preg_replace('/\t+/', '', $mensagem);

        return $mensagem;
    }

}