<?php

class _Autoload
{
    static function load($className)
    {
        if (strpos($className, '\\') != false) {
            $path = explode('\\', $className);
            if (count($path) > 0) {
                $class = array_pop($path);
                if (count($path) == 1) {
                    $path = $path[0];
                    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $class . '.class.php';
                } else {
                    $path = str_replace("\\",DIRECTORY_SEPARATOR,$className);
                    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $path . '.class.php';
//                    var_dump($file);
//                    throw new Exception("Erro em autoload de " . $className);
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    } else {
                        $path = str_replace("\\",DIRECTORY_SEPARATOR,$className);
                        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $path . '.php';
//                    var_dump($file);
//                    throw new Exception("Erro em autoload de " . $className);
                        if (file_exists($file)) {
                            require_once $file;
                            return;
                        }
                    }
                }
//            var_dump($file);
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
                $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $class . DIRECTORY_SEPARATOR . $class . '.class.php';
//            var_dump($file);
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        } else {
            // Sem namespace - ex: ControllerInscricoes
            if (strpos($className, 'Controller') !== false) {
                $path = 'controller';
                $class = str_replace('Controller', '', $className);
                $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $class . '.class.php';
            } else {
                $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $className . '.class.php';
            }
//        var_dump($file);
            if (file_exists($file)) {
                require_once $file;
                return;
            }
            // Sem namespace - ex: ControllerInscricoes
            $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $className . '.class.php';
//            var_dump($file);
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
//    echo "autoload " . $className . " não encontrou classe<Br>";
    }
}

spl_autoload_register(array('_Autoload', 'load'));