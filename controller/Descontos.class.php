<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 25/03/15
 * Time: 22:15
 */

class ControllerDescontos
{
    public static function dispatcher()
    {
        $action = $_GET['action'];

        $desconto=null;
        if (isset($_GET['id']))
           $desconto = Descontos::getInstance()->getById($_GET['id']);

        $evento=null;
        if (isset($_GET['id_evento']))
            $id_evento = $_GET['id_evento'];
        if (isset($_POST['id_evento']))
            $id_evento = $_POST['id_evento'];

        if ($id_evento!=null)
            $evento = Eventos::getInstance()->getById($id_evento);

        if ($action == null && $evento!=null)
            self::showListEvento($evento);
        if ($action == null && $evento==null)
            self::showListGeral();
        if ($action == 'add-new' || $action == 'edit')
            self::showForm($action, $desconto, $evento);
        if ($action=='delete')
            self::delete($evento);
        if ($action=='view')
            self::view($evento);
    }

    public static function showListEvento($evento)
    {
        require_once PLUGINPATH . '/view/descontos/list_evento.php';
        $registros = Descontos::getInstance()->getBy('id_evento',$evento->id,false);
        ListTableDescontosEvento($evento, $registros, "Descontos em ".$evento->titulo);
    }

    private static function delete($evento)
    {
    }

    private static function showForm($action, $desconto, $evento)
    {
        // Postando?
        if (count($_POST) > 0) {
            // Validar
            $desconto = Descontos::getInstance()->populate($_POST);

            // Salvar ou incluir?
            if ($_POST['id'] == null) {
                $desconto = Descontos::getInstance()->insert($desconto);
            } else {
                $desconto = Descontos::getInstance()->save($_POST['id'], $desconto);
            }
            if ($evento==null)
                $evento = Descontos::getInstance()->getById($desconto->id_evento);
            if ($evento!=null)
                self::showListEvento($evento);
            else
            self::showListGeral();
        } else {
            require_once PLUGINPATH . '/view/descontos/form.php';
        }
    }

    private static function showListGeral()
    {
        require_once PLUGINPATH . '/view/descontos/list.php';
        $registros = Descontos::getInstance()->where('id_evento is null');
        ListTableDescontos($registros, "Tickets de Desconto");
    }


}