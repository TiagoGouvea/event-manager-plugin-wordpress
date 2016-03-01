<?php
use TiagoGouvea\PLib;

/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 22/03/15
 * Time: 13:38
 */

class ControllerLocais {

    public static function dispatcher()
    {
        Locais::getInstance()->init();

        $id = $_GET['id'];
        $action = $_GET['action'];
        if ($action == null)
            self::showList();
        if ($action == 'add-new' || $action == 'edit')
            self::showForm($action, $id);
        if ($action=='delete')
            self::delete($id);
    }

    public static function showForm($action, $id = null)
    {
        if ($action == 'edit')
            $local = Locais::getInstance()->getById($id);

        // Postando?
        if (count($_POST) > 0) {
            // Validar
            $local = Locais::getInstance()->populate($_POST);

            // Salvar ou incluir?
            if ($_POST['id'] == null) {
                $local = Locais::getInstance()->insert($local);
            } else {
                $local = Locais::getInstance()->save($_POST['id'], $local);
            }
            self::showList();
        } else {
            require_once PLUGINPATH . '/view/locais/form.php';
        }
    }

    public static function showList()
    {
        require_once PLUGINPATH . '/view/locais/list.php';
        $locais = Locais::getInstance()->getAll();
        ListTableLocais($locais);
    }

    private static function delete($id)
    {
        Locais::getInstance()->delete($id);
        self::showList();
    }

} 