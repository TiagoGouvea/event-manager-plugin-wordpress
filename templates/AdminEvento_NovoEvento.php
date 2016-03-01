<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 02/08/14
 * Time: 14:41
 *
 * Template de inclusão para novo evento. Exibe apenas dois campos.
 */

// Campos visíveis
add_meta_box(
    'tgo_template_box', // Id
    'Template e Título', // Titulo
    'render_meta_box', // Callback de conteúdo
    'page', // PostType
    'normal', // Contexto
    'high' // Prioridade
);

?>
    <form method="post">
        <div class="wrap">
            <div id="icon-users" class="icon32"><br/></div>
            <h2>Novo Evento</h2>
            <?php do_meta_boxes('page', 'normal', null); ?>
        </div>
        <button class=’button-primary’ type=‘submit’ name=‘Save’>Criar Evento</button>
    </form>
<?php

function render_meta_box($post)
{
    $templates = Template::getTodosArray();
    $select = input_select_simples('id_template', "Template de Evento", $templates);
    echo label("template", "Template de Evento", $select);

    $input = input_texto_simples('post_title', 'Título do Evento', 30);
    echo label("titulo", "Título do Evento", $input);
}