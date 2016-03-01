<?php
use TiagoGouvea\PLib;


/**
 * The Class.
 */
class Tgo_Evento
{
    /* @var $evento Evento */
    private $evento;
    private $post_id;

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save'));
    }

    public static function register_post_type()
    {
        //make sure new rules will be generated for custom post type
        //die();
        register_post_type('tgo_evento', array(
                'labels' => array(
                    'name' => __('Eventos'),
                    'singular_name' => __('Evento'),
                    'add_new' => __('Novo Evento')
                ),
                'supports' => array('title',
//                    'editor',
                    'thumbnail',
                    'excerpt',
                    'tags'
                ),
                'publicly_queryable' => true,
                'query_var' => true,
                'rewrite' => array('slug' => '', 'with_front' => false),
                'public' => true,
                'capability_type' => 'post',
                'has_archive' => true,
                'show_ui' => true,
                'show_in_menu' => false,
                'menu_position' => 5,
                'can_export' => true,
                'taxonomies' => array('tgo_evento_tipo', 'category', 'post_tag')
            )
        );

        //add_action('admin_init', 'flush_rewrite_rules');

        //Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name' => _x('Tipos de Eventos', 'taxonomy general name'),
            'singular_name' => _x('Tipo de Evento', 'taxonomy singular name'),
            'search_items' => __('Procurar'),
            'all_items' => __('Todos Tipos de Eventos'),
            'parent_item' => __('Tipo de Evento Acima'),
            'parent_item_colon' => __('Tipo de Evento Acima:'),
            'edit_item' => __('Editar Tipo de Evento'),
            'update_item' => __('Atualizar Tipo de Evento'),
            'add_new_item' => __('Adicionar Tipo de Evento'),
            'new_item_name' => __('Novo Tipo de Evento'),
            'menu_name' => __('Tipo de Evento'),
        );

        register_taxonomy('tgo_evento_tipo', array('tgo_evento'), array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => false,
        ));

        // add to our plugin init function
//        global $wp_rewrite;
//        $structure = '%categoria%/%tgo_evento%';
//        $wp_rewrite->add_rewrite_tag("%tgo_evento%", '([^/]+)', "tgo_evento=");
//        $wp_rewrite->add_permastruct('tgo_evento', $structure, false);
//
        add_filter('post_type_link', array(__CLASS__, 'post_type_link'), 10, 2);
    }


    public static function post_type_link($link, $post)
    {
        if ($post->post_type != 'tgo_evento')
            return $link;

        //var_Dump($link); die();


        $category = get_the_terms($post->ID, 'tgo_evento_tipo');
        if ($category != null && is_array($category) && isset($category[0])) {
            $link = str_replace('tgo_evento_tipo', $category[0]->slug, $link);
        } else {
            $link = str_replace('tgo_evento_tipo', '', $link);
        }

        //$link = str_replace('tgo_evento', '', $link);
        //$link = str_replace('//', '/', $link);

        //var_dump($category[0]);
        //var_dump($link);die();
        return $link;
    }

    public function teste($arg)
    {
        // echo section intro text here
        echo '<p>id: ' . $arg['id'] . '</p>';             // id: eg_setting_section
        echo '<p>title: ' . $arg['title'] . '</p>';       // title: Example settings section in reading
        echo '<p>callback: ' . $arg['callback'] . '</p>'; // callback: eg_setting_section_callback_function
    }


    /**
     * Adds the meta box container.
     */
    public function add_meta_box($post_type)
    {
        // Descrição Curtíssima
        add_meta_box(
            'tgo_evento_descricao1', // Id
            'Descrições do Evento', // Titulo
            array($this, 'render_meta_box_descricoes'), // Callback de conteúdo
            'tgo_evento', // PostType
            'advanced', // Contexto
            'high' // Prioridade
        );

//        // Release
//        add_meta_box(
//            'tgo_evento_release_box', // Id
//            'Release do Evento', // Titulo
//            array($this, 'render_meta_box_release'), // Callback de conteúdo
//            'tgo_evento', // PostType
//            'normal', // Contexto
//            'low' // Prioridade
//        );
    }


    public function render_meta_box_descricoes($post)
    {
        $postId = $post->ID;
        $this->post_id = $postId;

        if ($postId != null) {
            $this->evento = Eventos::getInstance()->getById($postId,false);
        }
        ?>
        <div id="tabs">
            <ul class="tabs">
                <li><a href="#tabs-descricao">Descrição</a></li>
                <li><a href="#tabs-realizacao">Realização</a></li>
                <li><a href="#tabs-inscricao">Inscrição</a></li>
                <?php if ($this->templateExibir('campos_extras')): ?>
                    <li><a href="#tabs-dados">Dados Formulário</a></li>
                <?php endif; ?>
                <?php if ($this->evento->pago == 'pago'): ?>
                    <li><a href="#tabs-treinamento">Treinamento</a></li>
                <?php endif; ?>
                <!--                <li><a href="#tabs-mensagens">Mensagens</a></li>-->
                <?php if ($this->templateExibir('secoes_extras')): ?>
                    <li><a href="#tabs-sessoesextras">Sessões Extras</a></li>
                <?php endif; ?>
            </ul>
            <div id="tabs-descricao">
                <?php
                if (TGO_EVENTO_EVENTO_PAI === true) {
                    echo "<div class=fields>";
                    echo "<h3>Herdar Configurações</h3>";
                    $eventos = Eventos::getInstance()->getEventosOrfaos($postId);
                    $eventos =PLib::object_to_array($eventos);
                    $eventos = Plib::array_to_key_value($eventos,'id','titulo');
                    echo input_select($postId, 'id_evento_pai', 'Evento Pai:',
                        $eventos, null, get_post_meta($postId,'id_evento_pai',true), 'class=ajustes_visuais');
                    echo "</div>";
                }
                echo "<div class=fields>";
                echo "<h3>Chamadas Curtas</h3>";
                if ($this->templateExibir('descricao_1'))
                    echo input_texto($postId, 'descricao_1', 'Descrição Curtíssima', 100);

                if ($this->templateExibir('descricao_2'))
                    echo input_textarea($postId, 'descricao_2', 'Descrição Breve', 3);
                echo "</div>";

                echo "<div class=fields>";
                echo "<h3>Apresentação completa</h3>";
                if ($this->templateExibir('descricao_3')) {
                    $value = get_post_meta($postId, 'descricao_3', true);
                    wp_editor($value, 'descricao_3', array('textarea_rows' => get_option('default_post_edit_rows', 20)));
                }
                echo "</div>";
                ?>
            </div>
            <div id="tabs-realizacao">
                <?php

                // Organizador
                $organizadores = Plib::object_to_array(Organizadores::getInstance()->getAll());
                $organizadores = Plib::array_to_key_value($organizadores, 'id', 'titulo');
                if (count($organizadores) > 1) {
                    echo "<div class=fields>";
                    echo "<h3>Organização</h3>";
                    if ($this->templateExibir('id_organizador')) {
                        echo input_select($postId, 'id_organizador', 'Organizador:', $organizadores, null, $this->templateValorPadrao('id_organizador'));
                    } else {
                        if ($this->templateValorPadrao('id_organizador') != null)
                            echo input_hidden($postId, 'id_organizador', $this->templateValorPadrao('id_organizador'));
                        else {
                            $organizadores = array_keys($organizadores);
                            echo input_hidden($postId, 'id_organizador', $organizadores[0]);
                        }
                    }
                    echo "</div>";
                }

                // Instrutor
                if ($this->templateExibir('id_instrutor')) {
                    $instrutores = Instrutores::getInstance()->getTodosArray();
                    echo input_select($postId, 'id_instrutor', 'Instrutor/Coordenador:', $instrutores);
                }

                echo "<div class=fields>";
                echo "<h3>Data e Local</h3>";
                echo input_data($postId, 'data', 'Data do Evento:');
                echo input_hora($postId, 'hora', 'Hora de Inicio:');
                if ($this->templateExibir('data_fim'))
                    echo input_data($postId, 'data_fim', 'Data Término:');
                echo input_hora($postId, 'hora_fim', 'Hora Término:');

                if ($this->templateExibir('id_local')) {
                    Locais::getInstance()->init();
                    $locais = Plib::object_to_array(Locais::getInstance()->getAll());
                    $locais = Plib::array_to_key_value($locais, 'id', 'titulo');
                    echo input_select($postId, 'id_local', 'Local:', $locais);
                }



                echo "</div>";

                echo "<div class=fields>";
                echo "<h3>Medidores de Conversão</h3>";
                echo input_texto($postId, 'fb_conversion_track', 'Facebook Conversion Track:', 20);
                echo input_texto($postId, 'tw_conversion_track', 'Twitter Conversion Track:', 20);
                echo "</div>";
                ?>
            </div>
            <div id="tabs-inscricao">
                <?php

                // Data Inicio Inscrições
                echo "<div class=fields>";
                echo "<h3>Inscrições</h3>";
                echo input_data($postId, 'data_inicio_inscricoes', 'Inicio das Inscrições:');
                echo input_data($postId, 'data_fim_inscricoes', 'Fim das Inscrições:');

                // Pago ou Gratuito
                if ($this->templateExibir('pago'))
                    echo input_select($postId, 'pago', 'Tipo de Inscrição:',
                        array('pago' => 'Paga', 'gratuito' => 'Gratuita'), null, null, 'class=ajustes_visuais');
                else if ($this->templateValorPadrao('pago') != null)
                    echo input_hidden($postId, 'pago', $this->templateValorPadrao('pago'));

                if ($this->templateExibir('confirmacao')) {
                    echo input_select($postId, 'confirmacao', 'Confirmação da Inscrição:', array(
                        'preinscricao' => 'Pré-inscrição', 'imediata' => 'Imediata',
                        'pagamento' => 'Após Confirmação do Pagamento',
                        'posterior' => 'Posteriormente pelo Organizador'),
                        null
                    );
                } else if ($this->templateValorPadrao('confirmacao') != null) {
                    echo input_hidden($postId, 'confirmacao', $this->templateValorPadrao('confirmacao'));
                }
                if ($this->templateExibir('validacao_pessoa'))
                    echo input_select($postId, 'validacao_pessoa', 'Localizar Cadastro do Inscrito:',
                        array('email' => 'Email',
                            'cpf' => 'CPF'),null,'email'
                    );
                echo "</div>";

                // Vagas
                if ($this->templateExibir('vagas')) {
                    echo "<div class=fields>";
                    echo "<h3>Vagas</h3>";
                    echo input_numero($postId, 'vagas', 'Vagas Disponíveis:');
                    echo input_checkbox($postId, 'fila_espera', 'Fila de Espera');
                    echo "</div>";
                }


                // Formas de pagamento
                echo "<div id=formas_pagamento class=fields>";
                echo "<h3>Pagamento</h3>";

                // Integração PagSeguro
                if (Integracoes::getInstance()->hasByServico('PagSeguro')) {
                    echo input_checkbox($postId, 'pago_pagseguro', 'PagSeguro');
                    $integracoes = Plib::object_to_array(Integracoes::getInstance()->getByServico('PagSeguro'));
                    $integracoes = Plib::array_to_key_value($integracoes, 'id', 'titulo');
                    echo input_select($postId, 'id_integracao_pagseguro', 'Integração PagSeguro:', $integracoes);
                }

                // Integração Cielo
                if (Integracoes::getInstance()->hasByServico('Cielo')) {
                    echo input_checkbox($postId, 'pago_cielo', 'Cielo');
                    $integracoes = Plib::object_to_array(Integracoes::getInstance()->getByServico('Cielo'));
                    $integracoes = Plib::array_to_key_value($integracoes, 'id', 'titulo');
                    echo input_select($postId, 'id_integracao_cielo', 'Integração Cielo:', $integracoes);
                }

                // Local de pagamento
                echo input_checkbox($postId, 'pago_dinheiro', 'Dinheiro');
                $locais = Plib::object_to_array(Locais::getInstance()->getAll());
                $locais = Plib::array_to_key_value($locais, 'id', 'titulo');
                echo input_select($postId, 'id_local_pagamento', 'Local pagamenho Dinheiro:', $locais);

                echo input_checkbox($postId, 'pago_deposito', 'Depósito Bancário');

                echo "</div>";


                // Beta
                if ($this->templateExibir('beta'))
                    echo input_checkbox($postId, 'beta', 'Beta:');

                // Confirmação da Inscrição:
                //var_dump($this->evento->confirmacao);
                //                $meta = get_post_meta($postId, 'confirmacao', true);
                //                echo "'$meta'<br>";
                //die();

                ?>
            </div>
            <div id="tabs-dados">
                <?php
                // Campo chave de validação da pessoa

                // Campos Extra
                if ($this->templateExibir('campos_extras')) {
                    echo "<div class=fields>";
                    echo "<h3>Campos Extras</h3>";
                    echo input_textarea($postId, 'campos_extras', 'Campos Extras:', 10, "Utilizar:<br>nome_unico/Titulo do campo<br>Exemplo:<br>empresa/Empresa em que trabalha");
                    echo "</div>";
                }

                if ($this->templateExibir('avaliacao')) {
                    $questionarios = Plib::object_to_array(Questionarios::getInstance()->getAll());
                    $questionarios = Plib::array_to_key_value($questionarios, 'id', 'titulo');
                    echo input_select($postId, 'id_questionario', 'Questionário de Feedback:', $questionarios);

                }
                ?>
            </div>
            <!--            <div id="tabs-mensagens">-->
            <!--                --><?php
            //                if ($this->evento){
            //                    // Incluir formulário parcial de mensagens
            //                    Mensagens::getInstance()->setEvento($this->evento->id);
            //                    require_once PLUGINPATH. '/view/mensagens/partial_mensagens.php';
            //                }
            //
            ?>
            <!--            </div>-->
            <?php if ($this->evento->pago == 'pago'): ?>
                <div id="tabs-treinamento">
                    <?php
                    echo "<div class=fields>";
                    echo "<h3>Treinamento</h3>";
                    // Publico Alvo
                    if ($this->templateExibir('publico_alvo'))
                        echo input_textarea($postId, 'publico_alvo', 'Público Alvo', 2);

                    // Material Didático
                    if ($this->templateExibir('material'))
                        echo input_textarea($postId, 'material', 'Material Didático oferecido:', 2);

                    // Certificado
                    if ($this->templateExibir('certificado'))
                        echo input_textarea($postId, 'certificado', 'Certificado:', 1);

                    // Duração
                    if ($this->templateExibir('duracao'))
                        echo input_textarea($postId, 'duracao', 'Duração:', 1);
                    if ($this->templateExibir('horarios'))
                        echo input_textarea($postId, 'horarios', 'Horários:', 1);
                      if ($this->templateExibir('requisitos'))
                        echo input_textarea($postId, 'requisitos', 'Pré-Requisitos:', 1);


                    // Valor
                    //                if ($this->templateExibir('valor'))
                    //                    echo input_numero($postId, 'valor', 'Valor:');
                    echo "</div>";

                    echo "<div class=fields>";
                    echo "<h3>Conteúdo</h3>";
                    // Tópicos
                    if ($this->templateExibir('topicos'))
                        echo input_textarea($postId, 'topicos', 'Tópicos:', 10, "Utilizar um tópico por linha. Identar com 3 espaços.<br>[v] Video, [a] Arquivo");

                    // FAQ
                    if ($this->templateExibir('faq'))
                        echo input_textarea($postId, 'faq', 'Perguntas Frequentes.:', 10, "Utilizar:<br>- Pergunta<Br>Resposta");
                    echo "</div>";
                    ?>
                </div>
            <?php endif; ?>
            <?php if ($this->templateExibir('secoes_extras')): ?>
                <div id="tabs-sessoesextras">
                    <?php
                    $postId = $post->ID;
                    // Cada secão
                    if ($this->evento) {
                        $secoes = $this->evento->getSecoesExtras();
                        if ($secoes != null) {
                            foreach ($secoes as $secao => $titulo) {
                                //                var_dump($secoes);
                                echo "<label for='secao_$secao'><div><p>$titulo</p></div></label>";
                                $value = get_post_meta($postId, 'secao_' . $secao, true);
                                wp_editor($value, 'secao_' . $secao, array('textarea_rows' => get_option('default_post_edit_rows', 10)));
                            }
                        }
                    }

                    // Secões
                    echo input_textarea($postId, 'secoes_extras', 'Secoes Extras:', 7, "Utilizar:<br>nome_unico/Titulo da seção");
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <script>
            function ajustesVisuais(){
                console.log(this);
                console.log();
                if (jQuery("#pago").val() == "pago") {
                    jQuery("#formas_pagamento").css("display","block");
                } else {
                    jQuery("#formas_pagamento").css("display","none");
                }
            };

            jQuery(function ($) {
                $(document ).ready(function() {
                    $("#tabs").tabs();
                    $(".ajustes_visuais").on("change", function () {
                        ajustesVisuais();
                    });
                    ajustesVisuais();
                });
            });


        </script>

        <?php
    }


    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_release($post)
    {
        $postId = $post->ID;

        $value = get_post_meta($postId, 'release', true);
        wp_editor($value, 'release', array('textarea_rows' => get_option('default_post_edit_rows', 15)));
    }


    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save($post_id)
    {
        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        // Check the user's permissions.
        if ('page' == $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id))
                return $post_id;
        } else {

            if (!current_user_can('edit_post', $post_id))
                return $post_id;
        }

        if ($post_id != null)
            $this->evento = Eventos::getInstance()->getById($post_id);

        /* OK, its safe for us to save the data now. */
        meta_update($post_id, 'id_evento_pai');
        meta_update($post_id, 'descricao_1', false);
        // Descricao2
        meta_update($post_id, 'descricao_2', false);
        // Descricao3
        meta_update($post_id, 'descricao_3', false);

        // Publico Alvo
        meta_update($post_id, 'publico_alvo');
        // Instrutor
        meta_update($post_id, 'id_instrutor', false);
        // Local
        meta_update($post_id, 'id_local', false);
        // Organizador
        if (get_option('singleOrganizer', true) && get_option('idSingleOrganizer', null) != null)
            update_post_meta($post_id, 'id_organizador', get_option('idSingleOrganizer'));
        else
            meta_update($post_id, 'id_organizador', false);
        // Topicos
        meta_update($post_id, 'topicos', false);
        // FAQ
        meta_update($post_id, 'faq', false);
        // Data
        meta_update($post_id, 'data');
        // Hora
        meta_update($post_id, 'hora');
        // Data
        meta_update($post_id, 'data_fim');
        // Hora
        meta_update($post_id, 'hora_fim');
        // dataInicioInscricoes
        meta_update($post_id, 'data_inicio_inscricoes');
        // data_fimInscricoes
        meta_update($post_id, 'data_fim_inscricoes');
        // vagas
        meta_update($post_id, 'vagas');
        // pago
        meta_update($post_id, 'pago');
        meta_update($post_id, 'pago_dinheiro');
        meta_update($post_id, 'pago_pagseguro');
        meta_update($post_id, 'pago_cielo');
        meta_update($post_id, 'pago_deposito');
        meta_update($post_id, 'fb_conversion_track');
        // Integração de Pagamento
        meta_update($post_id, 'id_integracao_pagseguro', false);
        meta_update($post_id, 'id_integracao_cielo', false);
        // Local pagamento
        meta_update($post_id, 'id_local_pagamento', false);
        // Fila de espera
        meta_update($post_id, 'fila_espera');
        // beta
        meta_update($post_id, 'beta');
        // Campos extras
        meta_update($post_id, 'campos_extras', false);
        // Id Questionário
        meta_update($post_id, 'id_questionario', false);

        // Duração
        meta_update($post_id, 'duracao');
        meta_update($post_id, 'horarios');
        meta_update($post_id, 'requisitos');
        // Valor
        meta_update($post_id, 'valor');
        // Material Didático
        meta_update($post_id, 'material');
        // Certificado
        meta_update($post_id, 'certificado');
        // confirmacao
        meta_update($post_id, 'confirmacao');
        // validacaoPessoa
        meta_update($post_id, 'validacao_pessoa', false, 'email');
        //die();
        // Release
        meta_update($post_id, 'release', false);

        // Campos extras
        meta_update($post_id, 'secoes_extras', false);

        // Cada seção
        if ($this->evento) {
            $secoes = $this->evento->getSecoesExtras();
            if ($secoes != null) {
                foreach ($secoes as $secao => $titulo) {
                    meta_update($post_id, 'secao_' . $secao, false);
                }
            }
        }

        // Mensagens
        if ($post_id) {
            Mensagens::getInstance()->savePost($_POST);
        }

//        echo "<br>FIM DE POSTANDO<br>";
    }

    public function get_eventos()
    {
        $args = array('post_type' => 'tgo_evento');
        $eventos = new WP_Query($args);
        return $eventos;
    }


    public function templateExibir($campo)
    {
        if ($this->post_id == null) return true;
        $id_template = get_post_meta($this->post_id, 'id_template', true);
//        var_dump($id_template);
        //update_post_meta($this->post_id, 'id_template', "11");
        if ($id_template == null)
            return true;

        $valor = get_post_meta($id_template, $campo, true);
        if ($valor == null) $valor = false;
//        echo "<Br>$id_template $campo = $valor";

        return $valor;
    }

    public function templateValorPadrao($campo)
    {
        if ($this->post_id == null) return true;

        $id_template = get_post_meta($this->post_id, 'id_template', true);
        if ($id_template == null) return true;

        $valor = get_post_meta($id_template, 'valor_' . $campo, true);
        if ($valor == null) $valor == false;

        return $valor;
    }

}