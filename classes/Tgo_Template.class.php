<?php
use TiagoGouvea\PLib;

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 02/08/14
 * Time: 13:46
 */
class Tgo_Template
{
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save'));
    }

    public static function register_post_type()
    {
        register_post_type('tgo_template', array(
                'labels' => array(
                    'name' => __('Template de Eventos'),
                    'singular_name' => 'Template de Eventos',
                    'add_new' => 'Novo',
                    'menu_name' => 'Campos',
                    'name_admin_bar' => 'Campos',
                    'add_new_item' => 'Incluir Template de Eventos',
                    'new_item' => 'Novo Template de Eventos',
                    'edit_item' => 'Editar Template de Eventos',
                    'view_item' => 'Ver Template de Eventos',
                    'all_items' => 'Todos Templates de Eventos',
                    'search_items' => 'Localizar Template de Eventos'
                ),
                'supports' => array(
                    'title'
                ),
                'public' => true,
                'publicly_queryable' => true,
                'show_in_menu' => false,
                'can_export' => true
            )
        );
    }

    /**
     * Adicionar meta box
     */
    public function add_meta_box($post_type)
    {
        // Campos visíveis
        add_meta_box(
            'tgo_template_box', // Id
            'Campos Visíveis no Cadastro do Evento', // Titulo
            array($this, 'render_meta_box'), // Callback de conteúdo
            'tgo_template', // PostType
            'normal', // Contexto
            'high' // Prioridade
        );

        // Valores Padrão
        add_meta_box(
            'tgo_template_valores_box', // Id
            'Valores Padrão', // Titulo
            array($this, 'render_valores_padrao_meta_box'), // Callback de conteúdo
            'tgo_template', // PostType
            'normal', // Contexto
            'high' // Prioridade
        );
    }

    /**
     * Render Meta Box content.
     * @param WP_Post $post The post object.
     */
    public function render_meta_box($post)
    {
        $postId = $post->ID;

        // Tabela com todos os campos, e checkboxes para marcar
        echo input_checkbox($postId, 'id_local', 'Local');
        echo input_checkbox($postId, 'id_organizador', 'Organizador');
        echo input_checkbox($postId, 'data_fim', 'Data Final','Em eventos de apenas um dia não é necessário informar data final');
        echo input_checkbox($postId, 'data_inicio_inscricoes', 'Inicio das Inscrições');
        echo input_checkbox($postId, 'data_fim_inscricoes', 'Fim das Inscrições');
        echo input_checkbox($postId, 'vagas', 'Vagas');
        echo input_checkbox($postId, 'pago', 'Tipo de Inscrição');
        echo input_checkbox($postId, 'descricao_1', 'Descrição Curtíssima');
        echo input_checkbox($postId, 'descricao_2', 'Descrição Breve');
        echo input_checkbox($postId, 'descricao_3', 'Descrição Texto Rico');
        echo input_checkbox($postId, 'publico_alvo', 'Público Alvo');
        echo input_checkbox($postId, 'material', 'Material Didático');
        echo input_checkbox($postId, 'certificado', 'Certificado');
        echo input_checkbox($postId, 'id_instrutor', 'Instrutor');
        echo input_checkbox($postId, 'duracao', 'Duração');
        echo input_checkbox($postId, 'horarios', 'Horários');
        echo input_checkbox($postId, 'topicos', 'Tópicos');
        echo input_checkbox($postId, 'faq', 'Perguntas Frequentes');
        echo input_checkbox($postId, 'area_aluno', 'Conteúdo de Área Restrita');
        echo input_checkbox($postId, 'beta', 'Beta');
        echo input_checkbox($postId, 'confirmacao', 'Confirmação da Inscrição');
        echo input_checkbox($postId, 'validacao_pessoa', 'Localizar Cadastro do Inscrito');
        echo input_checkbox($postId, 'campos_extras', 'Campos Extras');
        echo input_checkbox($postId, 'secoes_extras', 'Seções Extras');
        echo input_checkbox($postId, 'avaliacao', 'Formulário de Avaliação');
        if (TGO_EVENTO_EVENTO_PAI===true)
            echo input_checkbox($postId, 'evento_pai', 'Evento Pai');


    }

    /**
     * Render Meta Box content.
     * @param WP_Post $post The post object.
     */
    public function render_valores_padrao_meta_box($post)
    {
        $postId = $post->ID;

        // Organizador
        $organizadores = Organizadores::getInstance()->getAll();
        $organizadores = Plib::array_to_key_value(Plib::object_to_array($organizadores),'id','titulo');
        $organizadores['']="";
        echo input_select($postId, 'valor_id_organizador', 'Organizador:', $organizadores);

        // Confirmação
        echo input_select($postId, 'valor_confirmacao', 'Confirmação da Inscrição:', array(
                '' => '',
                'preinscricao' => 'Pré-inscrição',
                'imediata' => 'Imediata',
                'pagamento' => 'Após Confirmação do Pagamento',
                'posterior' => 'Posteriormente pelo Organizador')
        );

        // Tipo de inscrição
        //echo input_select($postId, 'pago', 'Tipo de Inscrição:', array(''=>'','pago' => 'Paga', 'gratuito' => 'Gratuita'));

        // Add an nonce field so we can check for it later.
        wp_nonce_field('myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce');
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

        /* Ok ok, vamos salvar agora */

        // Titulo
        meta_update($post_id, 'id_local');
        meta_update($post_id, 'vagas');
        meta_update($post_id, 'pago');
        meta_update($post_id, 'data_fim');
        meta_update($post_id, 'data_inicio_inscricoes');
        meta_update($post_id, 'data_fim_inscricoes');

        meta_update($post_id, 'descricao_1');
        meta_update($post_id, 'descricao_2');
        meta_update($post_id, 'descricao_3');
        meta_update($post_id, 'publico_alvo');
        meta_update($post_id, 'material');
        meta_update($post_id, 'certificado');
        meta_update($post_id, 'id_instrutor');
        meta_update($post_id, 'duracao');
        meta_update($post_id, 'topicos');
        meta_update($post_id, 'faq');
        meta_update($post_id, 'beta');
        meta_update($post_id, 'confirmacao');
        meta_update($post_id, 'validacao_pessoa');
        meta_update($post_id, 'campos_extras');
        meta_update($post_id, 'secoes_extras');

        // valores padrão
        meta_update($post_id, 'valor_id_organizador');
        meta_update($post_id, 'valor_confirmacao');
        meta_update($post_id, 'valor_pago');
    }
}