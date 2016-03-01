<?php
use lib\Gamification;
use TiagoGouvea\PHPGamification\Model\Badge;
use TiagoGouvea\PHPGamification\Model\UserScore;
use TiagoGouvea\PLib;

/**
 *
 * Rotas que não necessitam de token:
 * /autenticar/
 *
 */
class ControllerApi
{
    public static function dispatcher()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_STRICT);

        $app = new \Slim\Slim();
        \Slim\Slim::registerAutoloader();
        $app->contentType('application/json; charset=utf-8');
        $app->config('debug', true);
//        echo "<pre>";
        if (WP_DEBUG)
            $root = strtolower($app->request()->getRootUri()) . '/api';
        else
            $root = "/api";
//        var_dump($root);
//            $root = "/api";
//            $root = strtolower($app->request()->getRootUri()) . '/api';
//        else
//        $root = "/api";
//        var_dump($root);
//        $root = null;
//        $root = var_dump($app->request()->getRootUri());
//        die();
//        });


//        var_dump($app->request->headers);

        $app->hook('slim.before.dispatch', function () use ($app) {

            // Conferir se é url que exige autenticação
            $req = $_SERVER['REQUEST_URI'];
            if (strpos($req, '/autenticar/') !== false) {
                return;
            }

            $headers = self::request_headers();
//            var_dump($headers);die();
            $app = \Slim\Slim::getInstance();
            $erro = null;

            $api_user_id = $headers['X-API-USER-ID'];
            $api_token = $headers['X-API-TOKEN'];

            if ($api_user_id == null || $api_token == null) {
                $erro = "É preciso informar X-API-USER-ID e X-API-TOKEN no header.";
            } else {
                // Sanitizar user_id
                /* @var $pessoa Pessoa */
                $pessoa = Pessoas::getInstance()->getById($api_user_id);
                // Sanitizar token
                if ($pessoa == null) {
                    $erro = 'Não te encontramos em nossa base de dados...';
                } else if (md5($pessoa->id.'_'.$pessoa->password) != $api_token) {
                    $erro = 'Password inválido';
                }
            }

            if ($erro) {
                $app->response->headers['X-Authenticated'] = 'False';
                $app->halt('401', '{"error":{"text": "' . $erro . '" }}');
            }
        });

        $app->group($root, function () use ($app) {

            // '/'
            $app->get('/', function () use ($app) {
                echo json_encode(array("bem vindo", "a API"));
            });

            // Autenticate
            $app->post('/autenticar/', function () use ($app) {
                // Obter pessoa, e usuário
                $email = $_POST['email'];
                $password = $_POST['password'];
                if ($email == '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(array('Erro' => 'Informe um endereço de email válido.'));
                    return;
                }

                /* @var $pessoa Pessoa */
                $pessoa = Pessoas::getInstance()->getByEmail($email);
                if ($pessoa == null) {
                    echo json_encode(array('Erro' => 'Não te encontramos em nossa base de dados...'));
                    return;
                }

                // Validar senha, antes de mais nada
                // @TODO Buscar user do wordpress que seja esta pessoa e verificar se está ativo, senão, é pessoa normal

                // Aqui, só pode ser um participante do grupo ou inscrito em algum evento
                $nPessoa = ControllerApi::decoratePessoa($pessoa);
                $return = array('pessoa' => $nPessoa);
                $return = PLib::object_to_array($return);
//                    $return = PLib::array_utf8_encode($return);
                echo json_encode($return, JSON_NUMERIC_CHECK);
                return;
            });

            $app->group('/gamification', function () use ($app) {
                $app->get('/ranking', function () use ($app) {
                    $ranking = Gamification::getInstance()->getUsersPointsRanking(300);
//                    var_dump($ranking);
                    $nRanking = ControllerApi::decorateRanking($ranking);
                    echo json_encode($nRanking);
                });
                $app->get('/user/:id', function ($id) use ($app) {
                    $pessoa = ControllerApi::getPessoa($id, true);
                    if ($pessoa) {
                        /* @var $gamification Gamification */
                        $gamification = Gamification::getInstance();
                        $gamification->setUserId($id);

                        $nPessoa = ControllerApi::decorateUserGamification($pessoa);

                        $badges = $gamification->getBadges();
                        $nBadges = ControllerApi::decorateBadges($badges);

                        $return = array('user' => $nPessoa, 'badge' => $nBadges);
                        $return = PLib::object_to_array($return);
                        echo json_encode($return, JSON_NUMERIC_CHECK);
                    }
                });
            });

// '/'
            $app->get('/notificacao/', function () use ($app) {
                die("notificação");
            });

// Retorna uma inscrição
            $app->get('/inscricao/:idInscricao/simples/', function ($idInscricao) use ($app) {
                if (strlen($idInscricao) > 10) {
                    $transactionId = $idInscricao;
                    $inscricao = Inscricoes::getInstance()->callbackPagamento(1, $transactionId);
                    if (get_class($inscricao) != 'Inscricao') {
                        $erro = 'Transação não identificada..';
                    }
                } else {
                    /* @var $inscricao Inscricao */
                    $inscricao = Inscricoes::getInstance()->getById($idInscricao);
                    if ($inscricao == null) {
                        $erro = 'Inscrição não localizada (' . $idInscricao . ')';
                    }
                }

                if ($erro) {
                    echo json_encode(array('erro' => $erro));
                } else {
                    if ($erro == null) {
                        if ($inscricao->confirmado == true)
                            $mensagem = "wizard_fim_pagamento_confirmado";
                        else {
                            $mensagem = "wizard_fim_pagamento_aguardando_confirmacao";
                        }
                        $mensagem = Mensagens::getInstance()->get($mensagem, $inscricao->evento(), $inscricao->pessoa(), $inscricao, true);

                        $nInscricao = array(
                            'confirmado' => $inscricao->confirmado,
                            'mensagem' => $mensagem);
                        $return = array('inscricao' => $nInscricao);
                        $nInscrito = PLib::object_to_array($return);
                        echo json_encode($nInscrito, JSON_NUMERIC_CHECK);
                    }
                }
            });

// Redireciona para URL de pagamento no gateway - Por enquanto só funciona pro devfest
            $app->get('/inscricao/:idInscricao/pagar/', function ($idInscricao) use ($app) {
                /* @var $inscricao Inscricao */
                $inscricao = Inscricoes::getInstance()->getById($idInscricao);
                if ($inscricao == null) {
                    echo json_encode(array('erro' => 'Inscrição não localizada (' . $idInscricao . ')'));
                    return;
                }

                $urlRetorno = BASE_URL . '/confirmacao?ticket=' . $inscricao->id * 333;
                $url = \lib\PagSeguroUtil::getUrlPagamento($inscricao, $urlRetorno);
                $return = array('url' => $url);
                $return = PLib::object_to_array($return);
                echo json_encode($return, JSON_NUMERIC_CHECK);
            });

// Retorna dados de uma pessoa
            $app->get('/pessoa/:idPessoa/', function ($idPessoa) use ($app) {
                $pessoa = ControllerApi::getPessoa($idPessoa, true);
                if ($pessoa) {
                    $nPessoa = ControllerApi::decoratePessoa($pessoa);
                    $return = array('pessoa' => $nPessoa);
                    //                $return = PLib::array_utf8_encode($return);
                    $return = PLib::object_to_array($return);
                    echo json_encode($return, JSON_NUMERIC_CHECK);
                }
            });

// Retorna as inscrições de uma pessoa
            $app->get('/pessoa/:idPessoa/inscricoes/', function ($idPessoa) use ($app) {
                $pessoa = ControllerApi::getPessoa($idPessoa, true);
                if ($pessoa) {
                    // Obter inscrições da pessoa
                    $inscricoes = Inscricoes::getInstance()->getByPessoa($pessoa->id);
                    $nInscricoes = array();
                    if ($inscricoes == null) {
                        echo json_encode(array('mensagem' => 'Sem inscrições desta pessoa'));
                        return;
                    }
                    foreach ($inscricoes as $inscricao) {
                        $nInscricoes[] = ControllerApi::decorateInscricaoTeceiros($inscricao);
                    }
                    $return = array('inscricoes' => $nInscricoes);
                    $return = PLib::object_to_array($return);
                    echo json_encode($return, JSON_NUMERIC_CHECK);
                }
            });

            // Realiza uma inscrição
            $app->post('/inscrever/:idEvento/', function ($idEvento) use ($app) {
                $idPessoa = $_POST['id_pessoa'];
                $nome = trim(ucfirst($_POST['nome']));
                $email = trim(strtolower($_POST['email']));
                $cpf = trim($_POST['cpf']);
                $celular = PLib::only_numbers($_POST['celular']);
                if (strpos($celular, '55') === 0)
                    $celular = substr($celular, 2);
                /* @var $evento Evento */
                $evento = Eventos::getInstance()->getById($idEvento);
                if ($evento == null) {
                    echo json_encode(array('erro' => 'Evento não localizado na base de dados (' . $idEvento . ')'));
                    return;
                }

                $validacao = PLib::coalesce($evento->validacao_pessoa, 'email');

                if ($idPessoa == null && ($nome == null || $celular == null || $email == null || ($$validacao == null))) {
                    echo json_encode(array('erro' => 'Envie todos os dados (' . $nome . $email . $celular . ').' . print_r($_POST, true)));
                    return;
                }
                if ($idPessoa == null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(array('erro' => 'Email inválido'));
                    return;
                }
                if ($idPessoa == null && strlen($celular) < 8) {
                    echo json_encode(array('erro' => 'Celular inválido'));
                    return;
                }

                // Mesmo sem id de pessoa, tentar encontrar pessoa por email
                if ($idPessoa == null) {
                    $pessoa = Pessoas::getInstance()->getByMd5($email);
                    if ($pessoa != null)
                        $idPessoa = $pessoa->id;
                }
//                $validacao='cpf';
//                if ($validacao=='cpf' && $cpf!=null && !PLib::validade_cpf($cpf)){
//                    echo json_encode(array('erro' => 'CPF inválido ('.$cpf.')'));
//                    return;
//                }

                // Incluir pessoa
                /* @var $pessoa Pessoa */
                if ($idPessoa == null) {
                    /* @var $pessoa Pessoa */
                    $pessoa = new Pessoa();
                    $pessoa->nome = $nome;
                    $pessoa->email = $email;
                    $pessoa->celular = $celular;
                    if ($validacao == 'cpf')
                        $pessoa->cpf = PLib::str_mask(PLib::only_numbers($cpf), '###.###.###-##');
//                    PLib::var_dump($pessoa);
                    $pessoa = Pessoas::getInstance()->insert($pessoa);
                } else {
                    $pessoa = Pessoas::getInstance()->getById($idPessoa);
                    if ($nome != null && strlen($nome) > 2)
                        $pessoa->nome = $nome;
                    if ($celular != null && strlen($celular) > 2)
                        $pessoa->celular = $celular;
                    $pessoa->save();
                }
                // Extras? Pegar do post.... xeu pensar - ter como limitar e tal.. como tornar mais seguro?
                $extras = array();
                foreach ($_POST as $key => $value) {
                    if ($key == 'id_pessoa' || $key == 'nome' || $key == 'cpf' || $key == 'celular' || $key == 'email') continue;
                    $extra = array('titulo' => $key, 'valor' => $value);
                    $extras[$key] = $extra;
                }
                if (count($extras) > 0) {
                    $pessoa->setExtras($extras);
                    $pessoa->save();
                }
                //PLib::var_dump($pessoa->getExtrasArray());
                //PLib::var_dump($pessoa);die();
                $idPessoa = $pessoa->id;

                // Verificar se já está inscrita.... hum, tem algumas situações aqui
                $inscricao = Inscricoes::getInstance()->getByEventoPessoaConfirmado($idEvento, $idPessoa);
                if ($inscricao != null) {
                    echo json_encode(array('erro' => "Você já está inscrito e confirmado para este evento!"));
                    return;
                }

                // Já salvar registro de inscrição
                $inscricao = Inscricoes::getInstance()->certificarInscricao($evento, $pessoa, $evento->confirmacao == "preinscricao");

                $nInscrito = ControllerApi::decorateInscricao($inscricao);

                $return = array('inscricao' => $nInscrito);
                $return = PLib::object_to_array($return);
                echo json_encode($return, JSON_NUMERIC_CHECK);
            });


// Return "current" events
            $app->get('/eventos/', function () use ($app) {
                $eventos = Eventos::getInstance()->getAll();
                if ($eventos == null) {
                    echo json_encode(array('erro' => 'Nenhum evento registrado....'));
                    return;
                }
                $events = array();
                /* @var $evento Evento */
                foreach ($eventos as $evento) {
                    if ($evento->preInscricao()) continue;
                    $nEvento = ControllerApi::decorateEvento($evento, false);
                    $events[] = $nEvento;
                }
                $events = PLib::object_to_array($events);
                echo json_encode($events, JSON_NUMERIC_CHECK);
            });

// Return "current" events
            $app->get('/eventos/futuros/', function () use ($app) {
                $eventos = Eventos::getInstance()->getAcontecendoFuturos();
                if ($eventos) {
                    $events = array();
                    /* @var $evento Evento */
                    foreach ($eventos as $evento) {
                        $nEvento = ControllerApi::decorateEvento($evento, false);
                        $events[] = $nEvento;
                    }
                    $events = PLib::object_to_array($events);
                    echo json_encode($events, JSON_NUMERIC_CHECK);
                }
            });

// Return "current" events
            $app->get('/eventos/atual/', function () use ($app) {
                $evento = ControllerApi::getEventoAtual();
                if ($evento == null) {
                    echo json_encode(array('erro' => 'Não existe um evento atual'));
                    return;
                }

                $evento = ControllerApi::decorateEvento($evento, false);
                $events = PLib::object_to_array($evento);
                echo json_encode($events, JSON_NUMERIC_CHECK);
            });

// Return "current" events
            $app->get('/eventos/terceiros/', function () use ($app) {
                // Obter eventos de outros sites de eventos
//                $sites = array(
//                );
//                $eventosArray = array();
//                foreach ($sites as $site) {
//                    //  Initiate curl
//                    $ch = curl_init();
//                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                    curl_setopt($ch, CURLOPT_URL, $site . '/api/eventos/futuros/');
//                    $result = curl_exec($ch);
//                    curl_close($ch);
////                    var_dump($result);
//                    if ($result) {
//                        $eventosJson = json_decode($result, true);
////                        var_dump($eventosJson);
//                        if ($eventosJson) {
//                            foreach ($eventosJson as $evento) {
////                                $evento = Eventos::getInstance()->populate($evento);
//                                $eventosArray[] = $evento;
//                            }
//                        } else {
//                            echo "nune " . $site;
//                        }
//                    } else {
////                        echo "none ".$site;
//                    }
//                }
//                // Ordernar
////                var_dump($eventosArray);
//                usort($eventosArray, 'ControllerApi::ordem');
////                Eventos::getInstance()->ordenar($eventosArray,true);
////                $eventosReturn=array();
////                foreach($eventosArray as $evento) {
////                    $eventosReturn[]= ControllerApi::decorateEvento($evento, false);
////                }
//
//                $eventosReturn = PLib::object_to_array($eventosArray);
//                echo json_encode($eventosReturn, JSON_NUMERIC_CHECK);
            });

// Retorna um evento e seus dados
            $app->get('/evento/:idEvento/', function ($idEvento) use ($app) {
                $evento = Eventos::getInstance()->getById($idEvento);
                if ($evento == null) {
                    echo json_encode(array('erro' => 'Evento não localizado na base de dados (' . $idEvento . ')'));
                    return;
                }
                $nEvento = ControllerApi::decorateEvento($evento, true);
                $events = PLib::object_to_array($nEvento);
                echo json_encode($events, JSON_NUMERIC_CHECK);
            });

// Retorna os inscritos no evento
            $app->get('/inscritos/:idEvento/', function ($idEvento) use ($app) {
                $inscritos = Inscricoes::getInstance()->getByEvento($idEvento, null, "ev_pessoas.nome");
//                var_dump($inscritos);
                $registrations = array();
                /* @var $inscrito Inscricao */
                foreach ($inscritos as $inscrito) {
                    $nInscrito = ControllerApi::decorateInscricao($inscrito);
                    $registrations[] = $nInscrito;
                }
                $registrations = PLib::object_to_array($registrations);
                echo json_encode($registrations, JSON_NUMERIC_CHECK);
            });

// Dá presença a um inscrito
            $app->post('/inscricao/self_chekin/', function () use ($app) {
                /* @var $inscricao Inscricao */
                $inscricao = null;
                $identificacao = $_POST['identificacao'];
                // email - ou id_evento-id_inscricao-id_pessoa
                if (substr_count($identificacao, '-') === 2) {
                    $id_evento = substr($identificacao, 0, strpos($identificacao, '-'));
                    $id_inscricao = substr($identificacao, strpos($identificacao, '-') + 1);
                    $id_pessoa = substr($id_inscricao, strpos($identificacao, '-') + 1);
                    $id_inscricao = substr($id_inscricao, 0, strpos($identificacao, '-'));
//                    var_dump($id_evento);
//                    var_dump($id_inscricao);
//                    var_dump($id_pessoa);
                    $inscricao = Inscricoes::getInstance()->getById($id_inscricao);
                    if ($inscricao == null) {
                        echo json_encode(array('erro' => 'Inscrição não localizada...'));
                        return;
                    } else if ($inscricao->id_pessoa != $id_pessoa) {
                        echo json_encode(array('erro' => 'As informações não batem! Inscrição X Pessoa'));
                        return;
                    } else if ($inscricao->id_evento != $id_evento) {
                        echo json_encode(array('erro' => 'As informações não batem! Inscrição X Evento'));
                        return;
                    }
                } else if (filter_var($identificacao, FILTER_VALIDATE_EMAIL) !== false) {
//                    $evento =
                    $evento = ControllerApi::getEventoAtual();
                    if ($evento == null) {
                        echo json_encode(array('erro' => 'Não existe um evento atual'));
                        return;
                    }
                    $pessoa = Pessoas::getInstance()->getByEmail(strtolower(trim($identificacao)));
                    if ($pessoa == null) {
                        echo json_encode(array('erro' => 'Não te encontramos em nossa base de dados (' . $identificacao . ')'));
                        return;
                    }
                    $inscricao = Inscricoes::getInstance()->getByEventoPessoa($evento->id, $pessoa->id);
                    if ($inscricao == null) {
                        echo json_encode(array('erro' => 'Você não se inscreveu pra este evento!'));
                        return;
                    }
                } else {
                    echo json_encode(array('erro' => 'Do que você está falando afinal?'));
                    return;
                }

                // Fazer - retornar
                $inscricao->presenca();
                $nInscrito = ControllerApi::decorateInscricao($inscricao, true);
                $return = PLib::object_to_array($nInscrito);
                echo json_encode($return, JSON_NUMERIC_CHECK);
            });


// Setar dados da inscrição - por enquanto apenas categoria
            $app->post('/inscricao/:idInscricao/', function ($idInscricao) use ($app) {
                /* @var $inscricao Inscricao */
                $inscricao = Inscricoes::getInstance()->getById($idInscricao);
                if ($inscricao == null) {
                    echo json_encode(array('erro' => 'Inscrição não localizada na base de dados (' . $idInscricao . ')'));
                    return;
                }

                // Categoria - Como bloquear mudar a categoria? Depois de que momento não pode mudar mais?
                $id_categoria = $_POST['id_categoria'];
                $inscricao->setCategoria($id_categoria);
                $inscricao->save();

                $return = array('inscricao' => $inscricao);
                $return = PLib::object_to_array($return);
                echo json_encode($return, JSON_NUMERIC_CHECK);
            });


// Retorna uma inscrição
            $app->get('/inscricao/:idInscricao/', function ($idInscricao) use ($app) {
                /* @var $inscricao Inscricao, e o evento referente */
                $inscricao = Inscricoes::getInstance()->getById($idInscricao);
                if ($inscricao == null) {
                    echo json_encode(array('erro' => 'Inscrição não localizada na base de dados (' . $idInscricao . ')'));
                    return;
                }
                $nInscrito = ControllerApi::decorateInscricao($inscricao, true);
                $nInscrito = PLib::object_to_array($nInscrito);
                echo json_encode($nInscrito, JSON_NUMERIC_CHECK);
            });

// Confirma um inscrito
            $app->post('/inscricao/:idInscricao/confirmar/', function ($idInscricao) use ($app) {
                /* @var $inscricao Inscricao */
                $inscricao = Inscricoes::getInstance()->getById($idInscricao);
                if ($inscricao == null) {
                    echo json_encode(array('erro' => 'Inscrição não localizada na base de dados (' . $idInscricao . ')'));
                    return;
                }
                // Se evento pago, verificar se informações vieram no post
                if ($inscricao->valor_inscricao > 0 && $_POST['forma_pagamento'] == null) {
                    $return = array('erro' => 'É preciso informar a forma de pagamento da insrição.');
                } else if ($inscricao->valor_inscricao > 0 && $_POST['id_pessoa_confirmacao'] == null) {
                    $return = array('erro' => 'É preciso informar a pessoa que está confirmando o pagamento.');
                } else if ($_POST['id_pessoa_confirmacao'] == null) {
                    $return = array('erro' => 'É preciso informar a pessoa que está confirmando a inscrição.');
                } else if (Pessoas::getInstance()->getById($_POST['id_pessoa_confirmacao'])->id_user == null) {
                    $return = array('erro' => 'O usuário confirmando precisa ser um admin.');
                } else {
                    $inscricao->id_pessoa_confirmacao = $_POST['id_pessoa_confirmacao'];
                    if ($inscricao->confirmado != 1)
                        $inscricao = $inscricao->confirmar($_POST['forma_pagamento'], $inscricao->valor_inscricao);
                    $nInscrito = ControllerApi::decorateInscricao($inscricao, true);
                    $nEvento = ControllerApi::decorateEvento($inscricao->evento(), true);
                    $return = array('evento' => $nEvento, 'inscricao' => $nInscrito);
                }
                $return = PLib::object_to_array($return);
                echo json_encode($return, JSON_NUMERIC_CHECK);
            });

// Dá presença a um inscrito
            $app->get('/inscricao/:idInscricao/presente/', function ($idInscricao) use ($app) {
                /* @var $inscricao Inscricao */
                $inscricao = Inscricoes::getInstance()->getById($idInscricao);
                if ($inscricao == null) {
                    echo json_encode(array('erro' => 'Inscrição não localizada na base de dados (' . $idInscricao . ')'));
                    return;
                }
                $inscricao = $inscricao->presenca();
                $nInscrito = ControllerApi::decorateInscricao($inscricao, true);
                $nEvento = ControllerApi::decorateEvento($inscricao->evento(), true);
                $return = array('evento' => $nEvento, 'inscricao' => $nInscrito);
                $return = PLib::object_to_array($return);
                echo json_encode($return, JSON_NUMERIC_CHECK);
            });

            // Atualiza dados de uma pessoa
            $app->post('/pessoa/:identificacao/', function ($identificacao) use ($app) {
                /* @var $pessoa Pessoa */
                $identificacao = urldecode($identificacao);
                $pessoa = Pessoas::getInstance()->getByMd5($identificacao);
                if ($pessoa == null) {
                    echo json_encode(array('erro' => 'Pessoa nao localizada na base de dados (' . $identificacao . ')'));
                    return;
                }
                // Extras?
                $extras = $_POST['extras'];
                if ($extras) {
                    foreach ($extras as $chave => $valor) {
                        $pessoa->setExtra($chave, ucfirst($chave), $valor);
                    }
                    $pessoa->save();
                }
                $pessoa = Pessoas::getInstance()->getByMd5($identificacao);
                $nPessoa = ControllerApi::decoratePessoa($pessoa);
                $return = array('pessoa' => $nPessoa);
                $return = PLib::object_to_array($return);
                echo json_encode($return, JSON_NUMERIC_CHECK);
            });


            // Obter todas pessoas
            $app->get('/pessoas/todas/', function () use ($app) {
                /* @var $pessoas Pessoa[] */
                $pessoas = Pessoas::getInstance()->getAll();
                $return = array();
                foreach ($pessoas as $pessoa) {
                    if ($pessoa->getCountConfirmados())
                        $return[] = ControllerApi::decoratePessoa($pessoa);
                }
                $return = array('pessoas' => $return);
                $return = PLib::object_to_array($return);
                echo json_encode($return, JSON_NUMERIC_CHECK);
            });

//            $app->get('/reset/', function () use ($app) {
//                // Zera todos os dados do site...
//                if (DB_NAME == 'emjf_wpjf' || DB_NAME == 'emjf') {
//                    /* @var $wpdb wpdb */
//                    $sql = "UPDATE ev_inscricoes SET confirmado=NULL, data_pagamento=NULL, data_confirmacao=NULL, valor_pago=NULL, taxa_cobranca=NULL, valor_liquido=NULL,codigo_pagamento=NULL, formapagamento_pagseguro=NULL,presente=NULL,status_gateway=NULL,forma_pagamento_gateway=NULL,codigo_gateway=NULL,data_atualizacao_gateway=NULL,vencido=NULL,data_vencido=NULL,data_cancelamento=NULL,forma_pagamento=NULL,id_situacao=NULL";
//                    $return = array('sucesso' => 'Dados resetados');
//                    echo json_encode($return, JSON_NUMERIC_CHECK);
//                } else {
//                    $return = array('erro' => 'Ops.. aqui não pode!');
//                    echo json_encode($return, JSON_NUMERIC_CHECK);
//                }
//            });

        });

        $app->run();


    }

    public
    static function decorateInscricaoTeceiros(Inscricao $inscrito)
    {
        $nInscrito = array();
        $nInscrito[id] = $inscrito->id;
        $nInscrito[id_evento] = $inscrito->id_evento;
        $nInscrito[titulo_evento] = $inscrito->evento()->titulo;
        $nInscrito[titulo_organizador_evento] = $inscrito->evento()->organizador()->titulo;
        $nInscrito[data_evento] = $inscrito->evento()->data;
        $nInscrito[data_inscricao] = $inscrito->data_inscricao;
        $nInscrito[confirmado] = $inscrito->confirmado;
        $nInscrito[data_confirmacao] = $inscrito->data_confirmacao;
        $nInscrito[presente] = Plib::coalesce($inscrito->presente, 0);
        $nInscrito[vencido] = $inscrito->vencido == 1;

        return $nInscrito;
    }

    public static function decorateInscricao(Inscricao $inscrito, $todosDetalhes = false)
    {
        $nInscrito = array();
        $nInscrito[id] = $inscrito->id;
        $nInscrito[id_pessoa] = $inscrito->id_pessoa;
        $nInscrito[nome] = $inscrito->pessoa()->nome;
        $nInscrito[id_preco] = $inscrito->id_preco;
        $nInscrito[titulo_preco] = $inscrito->preco()->titulo;
        $nInscrito[valor_inscricao] = $inscrito->valor_inscricao;
        $nInscrito[id_evento] = $inscrito->id_evento;
        $nInscrito[data_inscricao] = $inscrito->data_inscricao;
        $nInscrito[pre_inscricao] = $inscrito->pre_inscricao;
        $nInscrito[confirmado] = $inscrito->confirmado;
        $nInscrito[data_atualizacao_gateway] = $inscrito->data_atualizacao_gateway;
        $nInscrito[forma_pagamento_gateway] = $inscrito->forma_pagamento_gateway;
        $nInscrito[titulo_forma_pagamento_gateway] = $inscrito->titulo_forma_pagamento_gateway();
        $nInscrito[status_gateway] = $inscrito->status_gateway;
        $nInscrito[titulo_status_gateway] = $inscrito->titulo_status_gateway();
        $nInscrito[data_pagamento] = $inscrito->data_pagamento;
        $nInscrito[data_confirmacao] = $inscrito->data_confirmacao;
        $nInscrito[valor_pago] = $inscrito->valor_pago;
        $nInscrito[taxa_cobranca] = $inscrito->taxa_cobranca;
        $nInscrito[valor_liquido] = $inscrito->valor_liquido;
        $nInscrito[presente] = Plib::coalesce($inscrito->presente, 0);
        $nInscrito[fila_espera] = $inscrito->id_situacao == 10;
        $nInscrito[vencido] = $inscrito->vencido == 1;
        $nInscrito[id_pessoa_confirmacao] = $inscrito->id_pessoa_confirmacao;
        $nInscrito[nome_pessoa_confirmacao] = $inscrito->pessoa_confirmacao()->nome;

        if ($todosDetalhes)
            $nInscrito[pessoa] = ControllerApi::decoratePessoa($inscrito->pessoa());

        return $nInscrito;
    }

    public static function decorateEvento(Evento $evento, $todosDetalhes = false)
    {
        $nEvento = array();
        $nEvento[id] = $evento->id;
        $nEvento[titulo] = $evento->titulo;
        $nEvento[data] = $evento->data;
        $nEvento[pago] = $evento->pago == 'pago';
        $nEvento[realizado] = $evento->realizado();
        $nEvento[acontecendo] = $evento->acontecendo();
        $nEvento[no_futuro] = $evento->noFuturo();
        $nEvento[aconteceu_em_dois_dias] = $evento->aconteceuEmDoisDias();
        $nEvento[comeca_em_duas_horas] = $evento->comecaEmDuasHoras();

        $nEvento[vagas] = PLib::coalesce($evento->vagas, 0);
        $nEvento[qtd_inscritos] = $evento->qtdInscritos();
        $nEvento[qtd_confirmados] = $evento->qtdConfirmados();

        if ($todosDetalhes) {
            $nEvento[vagas_disponiveis] = $evento->vagasDisponiveis();
            $nEvento[qtd_pre_inscritos] = $evento->qtdPreInscritos();
            $nEvento[qtd_nao_confirmados] = $evento->qtdNaoConfirmados();
            $nEvento[qtd_cancelados] = $evento->qtdCancelados();
            $nEvento[qtd_inscritos_novos] = $evento->qtdInscritosNovos();
            $nEvento[qtd_presentes] = $evento->qtdPresentes();
            $nEvento[qtd_fila_espera] = $evento->qtdFilaEspera();
            $nEvento[permite_inscricao] = $evento->inscricaoAberta();
            $nEvento[permite_confirmacao] = $evento->permiteConfirmar();
            $nEvento[permite_presenca] = $evento->permitirPresenca();
            $nEvento[validacao_pessoa] = PLib::coalesce($evento->validacao_pessoa, 'email');
            $nEvento[fila_espera] = PLib::coalesce($evento->fila_espera, 0) == 1;
            $nEvento[precos] = ControllerApi::decoratePrecos($evento->getPrecos());
            $nEvento[valor_pago] = $evento->getValorPago();
        }
        return $nEvento;
    }

    public static function decoratePessoa(Pessoa $pessoa)
    {
        $nPessoa = array();
        $nPessoa['id'] = $pessoa->id;
        $nPessoa['organizador'] = $pessoa->id_user != null;
        $nPessoa['nome'] = $pessoa->nome;
        $nPessoa['cpf'] = $pessoa->cpf;
        $nPessoa['email'] = $pessoa->email;
        $nPessoa['celular'] = $pessoa->celular;
        $nPessoa['extras'] = $pessoa->getExtrasArray(false);
        return $nPessoa;
    }

    public static function decorateUserGamification(Pessoa $pessoa)
    {
        $nPessoa = array();
        $nPessoa['id'] = $pessoa->id;
        $nPessoa['name'] = $pessoa->nome;
        $nPessoa['picture'] = $pessoa->getPictureUrl();
        return $nPessoa;
    }

    public static function decoratePrecos($precos)
    {
        if ($precos && count($precos) > 0)
            foreach ($precos as $k => $preco) {
                $precos[$k] = ControllerApi::decoratePreco($preco);
            }
        return $precos;
    }

    public static function decoratePreco(Preco $preco)
    {
        $nPreco = array();
        $nPreco['id'] = $preco->id;
        $nPreco['titulo'] = $preco->titulo;
        $nPreco['valor'] = $preco->valor;
        $nPreco['_qtd_confirmados'] = $preco->getQtdConfirmados();
        $nPreco['valor_pago'] = $preco->getValorPago();
        return $nPreco;
    }


    private static function decorateRanking($ranking)
    {
        $return = array();
        /* @var $rank UserScore */
        foreach ($ranking as $rank) {
//            var_dump($rank);
            /* @var $pessoa Pessoa */
            $pessoa = Pessoas::getInstance()->getById($rank->getIdUser());

            $level = Gamification::getInstance()->getLevel($rank->getIdLevel());
//            var_dump($pessoa->id_user);
            if ($pessoa->id_user > 0) continue;
            $r = array();
            $r['idUser'] = $rank->getIdUser();
            $r['idLevel'] = $rank->getIdLevel();
            $r['points'] = $rank->getPoints();
//            $r['levelTitle'] = $rank->getLevel()->getTitle();
            $r['userName'] = $pessoa->primeiro_segundo_nome();
            $r['userPicture'] = $pessoa->getPictureUrl();
            $r['levelName'] = $level->getTitle();
            $return[] = $r;
        }
        return $return;
    }


    private static function decorateBadges($badges)
    {
        $return = array();
        /* @var $badges Badge[] */
        foreach ($badges as $badge) {
            $r['id'] = $badge->getId();
            $r['title'] = $badge->getTitle();
            $r['description'] = $badge->getDescription();
            $r['picture'] = 'http://emjuizdefora.com/gdgjf/img/gamification/' . $badge->getAlias() . '.png';
            $return[] = $r;
        }
        return $return;
    }

    public static function ordem($a, $b)
    {
        // Se pré-inscrição, por ultimo
//        if ($a[''->preInscricao() && !$b->preInscricao()) return 1;
//        if (!$a->preInscricao() && $b->preInscricao()) return -1;

        if ($a[data] == null && $b[data] != null) return 1;
        if ($a[data] != null && $b[data] == null) return -1;

        if (strtotime($a[data]) == strtotime($b[data])) {
            return 0;
        }
        return (strtotime($a[data]) < strtotime($b[data])) ? -1 : 1;
    }

    public static function getPessoa($idPessoa, $falharSeNaoEncontrada = true)
    {
        /* @var $pessoa Pessoa */
        if (ctype_digit($idPessoa)) {
            $pessoa = Pessoas::getInstance()->getById($idPessoa);
        } else {
            $idPessoa = urldecode($idPessoa);
            if (filter_var($idPessoa, FILTER_VALIDATE_EMAIL) || PLib::validade_cpf($idPessoa) || PLib::is_valid_md5($idPessoa))
                $pessoa = Pessoas::getInstance()->getByMd5($idPessoa);
            else {
                echo json_encode(array('erro' => 'Valor não aceito'));
                die();
            }
        }
        if ($pessoa == null && $falharSeNaoEncontrada) {
            echo json_encode(array('erro' => 'Pessoa não localizada na base de dados (' . $idPessoa . ')'));
        } else {
            return $pessoa;
        }
    }

    public static function getEventoAtual()
    {
        $eventos = Eventos::getInstance()->getAcontecendoFuturos(true);
        if ($eventos) {
            return $eventos[0];
        }
        return null;
    }

    public static function request_headers()
    {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do string manipulations to restore the original letter case
                $rx_matches = explode('_', $arh_key);
                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return ($arh);
    }


    /*
     * *
     * Endpoints para site devfest
     * - consultar pessoa
     * - inscrever - post
     * - informar pagamento?
     * + sempre retornar json no formato chave: valor(es)
     * {{URL}} = http://sudeste.devfest.com.br/wp/api/
    * Workflow Api Standalone devfest
    *
    * Primeira etapa
    *      input apenas para informar email e botão de avançar
    *      Ao clicar no botão requisitar:
    *      [GET] em {{URL}}/pessoa/endereco@email.da.pessoa.com.br/
     *      Será pesquisada a pessoa e talvez retornará os dados pessoais
    *      Se retornar "erro" é porque a pessoa não exite, se retornar "pessoa" é porque temos os dados dela, salvar localmente
    *      * dar um loading enquanto obtem os dados
    * Segunda etapa (dados pessoais)
     *      inputs com demais dados pessoais e alguns extras:
     *      email (já informado, enviar novamente), nome, celular [mascara?], deficiencia_fisica [checkbox], deficiencia_fisica_qual [input], restricao_alimentar [checkbox], restricao_alimentar_qual [input], trilha_mais_desejada [web e mobile]
     *      Se a pessoa foi retornada na etapa um, enviar no post também id_pessoa contendo o id da pessoa retornada
    *      Ao avançar postar:
     *      [POST] em {{URL}}/inscrever/4/
     *      Se retornar "erro", a melhor forma é exibir a mensagem de erro direto pro usuário (nesta etapa), pois será algo ligado aos dados..
     *      Se retornar "inscricao" obtivemos sucesso ao inscrever a pessoa, salvar o objeto localmente
    * Terceira etapa (categoria)
    *      Escolher se é apenas palestras, ou palestras e hackathon, ver valor total e clicar para pagar. A pessoa terá que escolher dentre as 3 opções. Quanto mais visual for isso, melhor. Deverá ser enviado no post o campo id_categoria As opções:
     *      Palestras - R$70,00 [id_categoria=1]
    *      Palestras + Hackathon - R$100,00 [id_categoria=2]
    *      Hackathon - R$50,00 [id_categoria=3]
    *      Ao avançar postar:
     *      [POST] {{URL}}/inscricao/{{id_inscricao}}/
     *      Se retornar "inscricao" deu certo, salvar localmente
    *      Se retornar "erro"... hum, será um grande problema
    * Quarta etapa (pagamento)
    *      Pagar!
     *      URL de retorno >
     *      Arquivo de retorno
    *
     * Inscrição - lote I - 100 ingressos - R$70
    * Inscrição - lote Il - 120 ingressos - R$85
    * Inscrição - lote III - 80 ingressos - R$100
    *
     * O que falta:
     * Incluir ticket de desconto na etapa de pagamento (API deve confirmar)
     * Customizar os emails de confirmação e tal
    */
}