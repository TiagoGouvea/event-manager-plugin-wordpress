<?php

function input_text($value, $name, $title, $size, $description = null)
{
    $return = "<input type='text' name=$name id=$name value='$value' size=$size>";
    $return = label($name, $title, $return, $description);
    return $return;
}


//*** Old codes ***//


function input_data($postId, $nome, $titulo, $ajuda = null)
{
    if ($postId != null) $meta = get_post_meta($postId, $nome, true);
    $return = "<input type='date' name=$nome id=$nome value='$meta'>";
    $return = label($nome, $titulo, $return, $ajuda);

    return $return;
}

function input_hora($postId, $nome, $titulo, $ajuda = null)
{
    if ($postId != null) $meta = get_post_meta($postId, $nome, true);
    $return = "<input type='time' name=$nome id=$nome value='$meta'>";
    $return = label($nome, $titulo, $return, $ajuda);

    return $return;
}

function input_numero($postId, $nome, $titulo, $ajuda = null)
{
    if ($postId != null) $meta = get_post_meta($postId, $nome, true);
    $return = "<input type='number' name=$nome id=$nome value='$meta' step=1 min=1>";
    $return = label($nome, $titulo, $return, $ajuda);

    return $return;
}

function input_select($postId, $nome, $titulo, $valores, $ajuda = null, $meta=null, $tagExtra=null)
{
    if ($postId != null && $meta==null) $meta = get_post_meta($postId, $nome, true);

//    echo "$nome: '$meta'<br>";
    $return = input_select_simples($nome, $titulo, $valores, $meta,$tagExtra);
    $return = label($nome, $titulo, $return, $ajuda);

    return $return;
}

function input_checkbox($postId, $nome, $titulo, $ajuda = null, $tagExtra=null)
{
    if ($postId != null) $meta = get_post_meta($postId, $nome, true);
    if ($meta == 1) $meta = "checked";
    $return = "<input type='checkbox' name=$nome id=$nome value='1' $meta $tagExtra>";
    $return = label($nome, $titulo, $return, $ajuda);
    return $return;
}

function input_checkbox_padrao($nome, $titulo, $conteudo=null, $ajuda = null)
{
    if ($conteudo == 1) $conteudo = "checked";
    $return = "<input type='checkbox' name=$nome id=$nome value='1' $conteudo>";
    $return = label($nome, $titulo, $return, $ajuda);

    return $return;
}

function input_radio_padrao($nome, $valor, $titulo , $ajuda = null, $tagExtra=null, $id=null)
{
//    $return = label($nome, $titulo, null, $ajuda);
    $return = "<input type='radio' name=$nome id=".($id?$id:$nome)." value='$valor' $tagExtra>$titulo";
    return $return;
}


function input_select_simples($nome, $titulo, $valores, $selecionado=null, $tagExtra=null)
{
    if (count($valores)==0) return;
    $return = "<select name=$nome id=$nome placeholder='$titulo' $tagExtra>";
    $return .= "<option value=''></option>";
    foreach ($valores as $chave => $valor) {
        $selected = ($selecionado!=null && $chave == $selecionado ? "selected" : "");
        $return .= "<option value='$chave' $selected>$valor</option>";
    }
    $return .= "</select>";

    return $return;
}

function input_texto($postId, $nome, $titulo, $tamanho, $ajuda = null)
{
    if ($postId != null) $meta = get_post_meta($postId, $nome, true);
    $return = "<input type='text' name=$nome id=$nome value='$meta' style='width:$tamanho%'>";
    $return = label($nome, $titulo, $return, $ajuda);

    return $return;
}

function input_texto_padrao($nome, $titulo, $tamanho, $conteudo, $ajuda = null)
{
    $return = "<input type='text' name=$nome id=$nome value='$conteudo' size=$tamanho>";
    $return = label($nome, $titulo, $return, $ajuda);

    return $return;
}

function input_textarea($postId, $nome, $titulo, $rows, $ajuda = null)
{
    if ($postId != null) $meta = get_post_meta($postId, $nome, true);
    $return = input_textarea_simples($nome,$rows,$meta);
    $return = label($nome, $titulo, $return, $ajuda);

    return $return;
}

function input_textarea_simples($nome, $rows, $conteudo, $tagExtra=null)
{
    $return = "<textarea name='$nome' id='$nome' rows='$rows' style='width:100%' $tagExtra>$conteudo</textarea>";
    return $return;
}

function input_hidden($postId,$nome,$conteudo){
    if ($postId != null && $conteudo==null) $conteudo = get_post_meta($postId, $nome, true);
    $return = "<input type='hidden' name=$nome id=$nome value='$conteudo'>";
    return $return;
}


function input_texto_simples($nome, $titulo, $tamanho, $conteudo = null, $tagExtra = null)
{
    $return = "<input type='text' name=$nome id=$nome value='$conteudo' size=$tamanho placeholder='$titulo' $tagExtra>";
    return $return;
}

function input_file($nome, $titulo,$ajuda)
{
    $return = "<input type='file' name=$nome id=$nome>";
    $return = label($nome, $titulo, $return,$ajuda);

    return $return;
}

function label($nome, $titulo, $input, $ajuda = null)
{
    if (strpos($input, 'checkbox')) {
        $return = '<div><p>' . $input . $titulo . '</p></div>';
    } else {
        $return = '<label id=label_'.$nome.' for="' . $nome . '">' . ($titulo?'<p>'.$titulo.'</p>': '').  $input;
    }
    if ($ajuda)
        $return .= '<p class="howto">' . nl2br($ajuda) . '</p>';
    $return .= '</label><br>';
    return $return;
}

function meta_update($post_id, $nome, $sanitize = true,$valorPadrao=null)
{
    if (isset($_POST[$nome])) {
        //echo "<pre>";
        //if ($nome=='camposExtras') var_dump($_POST[camposExtras]);
//        if ($sanitize)
//            $data = sanitize_text_field($_POST['_'.$nome]);
//        else
            $data = $_POST[$nome];
//        echo "$nome: $data<br>";
        //if ($nome=='camposExtras') echo "DATA: ";var_dump($data);
    } else {
        $data=$valorPadrao;
    }
    update_post_meta($post_id, $nome, $data);
}


/**
 * The Class.
 */
class Cpp
{

    public function __construct($name, $singular, $plural, $args = array(), $labels = array())
    {
        // Set some important variables
        $this->post_type = $name;
        $this->post_type_name = $plural;
        $this->post_type_singular_name = $singular;
        $this->post_type_args = $args;
        $this->post_type_labels = $labels;

        // Add action to register the post type, if the post type does not already exist
        if (!post_type_exists($this->post_type_name)) {
            add_action('init', array(&$this, 'register_post_type'));
        }

        // Listen for the save post hook
        $this->save();
    }

    public function add_meta_box($title, $fields = array(), $context = 'normal', $priority = 'high')
    {
        if (!empty($title)) {
            // We need to know the Post Type name again
            $post_type_name = $this->post_type_name;

            // Meta variables
            $box_id = strtolower(str_replace(' ', '_', $title));
            $box_title = ucwords(str_replace('_', ' ', $title));
            $box_context = $context;
            $box_priority = $priority;

            // Make the fields global
            global $custom_fields;
            $custom_fields[$title] = $fields;

            /* More code coming */
            add_action('admin_init', function () use ($box_id, $box_title, $post_type_name, $box_context, $box_priority, $fields) {
                    add_meta_box(
                        $box_id, $box_title, function ($post, $data) {
                            global $post;

                            // Nonce field for some validation
                            wp_nonce_field(plugin_basename(__FILE__), 'custom_post_type');

                            // Get all inputs from $data
                            $custom_fields = $data['args'][0];

                            // Get the saved values
                            $meta = get_post_custom($post->ID);

                            // Check the array and loop through it
                            if (!empty($custom_fields)) {
                                /* Loop through $custom_fields */
                                foreach ($custom_fields as $label => $type) {
                                    $field_id_name = strtolower(str_replace(' ', '_', $data['id'])) . '_' . strtolower(str_replace(' ', '_', $label));

                                    echo '<label for="' . $field_id_name . '">' . $label . '</label><input type="text" name="custom_meta[' . $field_id_name . ']" id="' . $field_id_name . '" value="' . $meta[$field_id_name][0] . '" />';
                                }
                            }
                        }, $post_type_name, $box_context, $box_priority, array($fields)
                    );
                }
            );
        }
    }

    public function add_taxonomy($name, $args = array(), $labels = array())
    {
        if (!empty($name)) {
            // We need to know the post type name, so the new taxonomy can be attached to it.
            $post_type_name = $this->post_type_name;

            // Taxonomy properties
            $taxonomy_name = strtolower(str_replace(' ', '_', $name));
            $taxonomy_labels = $labels;
            $taxonomy_args = $args;

            /* More code coming */
            //Capitilize the words and make it plural
            $name = ucwords(str_replace('_', ' ', $name));
            $plural = $name . 's';

// Default labels, overwrite them with the given labels.
            $labels = array_merge(
            // Default
                array(
                    'name' => _x($plural, 'taxonomy general name'),
                    'singular_name' => _x($name, 'taxonomy singular name'),
                    'search_items' => __('Search ' . $plural),
                    'all_items' => __('All ' . $plural),
                    'parent_item' => __('Parent ' . $name),
                    'parent_item_colon' => __('Parent ' . $name . ':'),
                    'edit_item' => __('Edit ' . $name),
                    'update_item' => __('Update ' . $name),
                    'add_new_item' => __('Add New ' . $name),
                    'new_item_name' => __('New ' . $name . ' Name'),
                    'menu_name' => __($name),
                ),
                // Given labels
                $taxonomy_labels
            );

// Default arguments, overwritten with the given arguments
            $args = array_merge(
            // Default
                array(
                    'label' => $plural,
                    'labels' => $labels,
                    'public' => true,
                    'show_ui' => true,
                    'show_in_nav_menus' => true,
                    '_builtin' => false,
                ),
                // Given
                $taxonomy_args
            );

// Add the taxonomy to the post type
            add_action('init', function () use ($taxonomy_name, $post_type_name, $args) {
                    register_taxonomy($taxonomy_name, $post_type_name, $args);
                }
            );
        }
    }

    public function register_post_type()
    {
        $post_type = $this->post_type;
        $plural = $this->post_type_singular_name;
        $singular = $this->post_type_singular_name;

        $labels = array_merge(
        // Default
            array('name' => $post_type,
                'singular_name' => $singular,
                'add_new' => 'Incluir ', strtolower($singular),
                'add_new_item' => 'Incluir ' . $singular,
                'edit_item' => 'Editar ' . $singular,
                'new_item' => 'Novo ' . $singular,
                'all_items' => 'Todos ' . $plural,
                'view_item' => 'Visualizar ' . $singular,
                'search_items' => 'Localizar ' . $plural,
                'not_found' => strtolower($plural) . ' não encontrado',
                'not_found_in_trash' => strtolower($plural) . ' não encontrado na lixeira',
                'parent_item_colon' => '',
                'menu_name' => $plural
            ),
            // Given labels
            $this->post_type_labels
        );

        $args = array_merge(
            array(
                'label' => $plural,
                'labels' => $labels,
                'public' => true,
                'show_ui' => true,
                'rewrite' => array('slug' => 'evento'),
                'can_export' => true,
                'taxonomies' => array('post_tag'),
                'has_archive' => true,
                'menu_position' => 5,
                'supports' => array('title',
                    'editor',
                    'thumbnail',
                    'excerpt'
                ),
                'show_in_nav_menus' => true,
                '_builtin' => false,
            ),
            // Given args
            $this->post_type_args
        );

        // Register the post type
        register_post_type($post_type, $args);
    }

    public function save()
    {
        // Need the post type name again
        $post_type = $this->post_type;

        add_action('save_post', function () use ($post_type) {
                // Deny the WordPress autosave function
                if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                    return;

                if (!wp_verify_nonce($_POST['custom_post_type'], plugin_basename(__FILE__)))
                    return;

                global $post;

                if (isset($_POST) && isset($post->ID) && get_post_type($post->ID) == $post_type) {
                    global $custom_fields;

                    // Loop through each meta box
                    foreach ($custom_fields as $title => $fields) {
                        // Loop through all fields
                        foreach ($fields as $label => $type) {
                            $field_id_name = strtolower(str_replace(' ', '_', $title)) . '_' . strtolower(str_replace(' ', '_', $label));

                            update_post_meta($post->ID, $field_id_name, $_POST['custom_meta'][$field_id_name]);
                        }
                    }
                }
            }
        );
    }

}
