<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 25/03/15
 * Time: 22:15
 */

class ControllerMensagens
{
    public static function dispatcher()
    {

        //if ($action == null)
        //    self::showList($evento);
//        if ($action == 'add-new' || $action == 'edit')
            self::showForm();
//        if ($action=='delete')
//            self::delete($evento);
//        if ($action=='view')
//            self::view($evento);
    }

    private static function showForm()
    {
        // Postando?
        if (count($_POST) > 0) {
            Mensagens::getInstance()->savePost($_POST);
        }

        require_once PLUGINPATH . '/view/mensagens/form.php';
    }



}