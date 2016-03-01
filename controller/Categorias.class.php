<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 25/03/15
 * Time: 22:15
 */

class ControllerCategorias
{
    public static function dispatcher()
    {
        $action = $_GET['action'];

        $categoria=null;
        if (isset($_GET['id']))
           $categoria = Categorias::getInstance()->getById($_GET['id']);

        $evento=null;
        if (isset($_GET['id_evento']))
            $id_evento = $_GET['id_evento'];
        if (isset($_POST['id_evento']))
            $id_evento = $_POST['id_evento'];

        if ($id_evento!=null)
            $evento = Eventos::getInstance()->getById($id_evento);

        if ($action == null)
            self::showList($evento);
        if ($action == 'add-new' || $action == 'edit')
            self::showForm($action, $categoria, $evento);
        if ($action=='delete')
            self::delete($evento);
        if ($action=='view')
            self::view($evento);
    }

    public static function showList($evento)
    {
        require_once PLUGINPATH . '/view/categorias/list.php';
        $categorias = Categorias::getInstance()->getByEvento($evento->id);
        ListTableCategorias($evento, $categorias, "Categorias em ".$evento->titulo);
    }

    private static function delete($evento)
    {
    }

    private static function showForm($action, $categoria, $evento)
    {
        // Postando?
        if (count($_POST) > 0) {
            // Validar
            $categoria = Categorias::getInstance()->populate($_POST);

            // Salvar ou incluir?
            if ($_POST['id'] == null) {
                $categoria = Categorias::getInstance()->insert($categoria);
            } else {
                $categoria = Categorias::getInstance()->save($_POST['id'], $categoria);
            }
            if ($evento==null) $evento = Eventos::getInstance()->getById($categoria->id_evento);
            self::showList($evento);
        } else {
            require_once PLUGINPATH . '/view/categorias/form.php';
        }
    }


}