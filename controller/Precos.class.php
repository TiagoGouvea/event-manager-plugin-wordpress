<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 25/03/15
 * Time: 22:15
 */

class ControllerPrecos
{
    public static function dispatcher()
    {
        $action = $_GET['action'];

        $preco=null;
        if (isset($_GET['id']))
           $preco = Precos::getInstance()->getById($_GET['id']);

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
            self::showForm($action, $preco, $evento);
        if ($action=='delete')
            self::delete($evento);
        if ($action=='view')
            self::view($evento);
    }

    public static function showList($evento)
    {
        require_once PLUGINPATH . '/view/precos/list.php';
        $registros = Precos::getInstance()->getByEvento($evento->id);
        ListTablePrecos($evento, $registros, "Preços em ".$evento->titulo);
    }

    private static function delete($evento)
    {
    }

    private static function showForm($action, $preco, $evento)
    {
        // Postando?
        if (count($_POST) > 0) {
            // Validar
            $preco = Precos::getInstance()->populate($_POST);

            // Salvar ou incluir?
            if ($_POST['id'] == null) {
                $preco = Precos::getInstance()->insert($preco);
            } else {
                $preco = Precos::getInstance()->save($_POST['id'], $preco);
            }
            if ($evento==null) $evento = Precos::getInstance()->getById($preco->id_evento);
            self::showList($evento);
        } else {
            require_once PLUGINPATH . '/view/precos/form.php';
        }
    }


}