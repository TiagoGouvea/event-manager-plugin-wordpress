<?php

class ControllerIntegracoes
{

    public static function dispatcher()
    {

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
            $integracao = Integracoes::getInstance()->getById($id);

        // Postando?
        if (count($_POST) > 0) {
            // Validar
            $integracao = Integracoes::getInstance()->populate($_POST);

            // Salvar ou incluir?
            if ($_POST['id'] == null) {
                $integracao = Integracoes::getInstance()->insert($integracao);
            } else {
                $integracao = Integracoes::getInstance()->save($_POST['id'], $integracao);
            }
            self::showList();
        } else {
            require_once PLUGINPATH . '/view/integracoes/form.php';
        }
    }

    public static function showList()
    {
        require_once PLUGINPATH . '/view/integracoes/list.php';
        $integracoes = Integracoes::getInstance()->getAll();
        tt_render_list_page($integracoes, "Integrações");
    }

    private static function delete($id)
    {
        Integracoes::getInstance()->delete($id);
        self::showList();
    }


}