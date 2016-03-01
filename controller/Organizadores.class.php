<?php

/**
 * User: TiagoGouvea
 * Date: 02/08/14
 * Time: 14:36
 */
class ControllerOrganizadores
{

    public static function dispatcher()
    {
        Organizadores::getInstance()->init();

        $id = $_GET['id'];
        if ($id!=null)
            $registro = Organizadores::getInstance()->getById($id);
        $action = $_GET['action'];

        if ($action == null)
            self::showList();
        if ($action == 'add-new' || $action == 'edit')
            self::showForm($action, $registro);
        if ($action=='delete')
            self::delete($registro);
        if ($action=='view')
            self::view($registro);
    }

    public static function showForm($action, $registro = null)
    {
        // Postando?
        if (count($_POST) > 0) {
            // Validar
            $registro = Organizadores::getInstance()->populate($_POST);

            // Salvar ou incluir?
            if ($_POST['id'] == null) {
                $registro = Organizadores::getInstance()->insert($registro);
            } else {
                $registro = Organizadores::getInstance()->save($_POST['id'], $registro);
                // Postando mensagem?
                Mensagens::getInstance()->savePost($_POST);
            }

            self::showList();
        } else {
            $organizador = $registro;
            require_once PLUGINPATH . '/view/organizadores/form.php';
        }
    }

    public static function showList()
    {
        require_once PLUGINPATH . '/view/organizadores/list.php';

        $organizadores = Organizadores::getInstance()->getAll();
        ListTableOrganizadores($organizadores,"Organizadores");
    }

    private static function delete($evento)
    {
        // Validar antes de excluir!
//        Organizadores::getInstance()->delete($evento->id);
//        self::showList();
    }

    private static function view($evento)
    {
        require_once PLUGINPATH . '/view/eventos/view.php';
    }
}