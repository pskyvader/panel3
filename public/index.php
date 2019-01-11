<?php
//directorio del proyecto
define("PROJECTPATH", dirname(__DIR__));

//directorio app
define("APPPATH", PROJECTPATH . '/app');

//autoload con namespaces
function autoload_classes($class_name)
{
    $filename = PROJECTPATH . '/' . str_replace('\\', '/', $class_name) . '.php';
    if (is_file($filename)) {
        include_once $filename;
    } else {
        $lowerfile = strtolower($filename);
        $include='';
        foreach (glob(dirname($filename) . '/*') as $file) {
            if (strtolower($file) == $lowerfile) {
                $include=$file;
                break;
            }
        }

        if (is_file($include)) {
            include_once $include;
        } else {
            var_dump($filename);
        }
    }
}
//registramos el autoload autoload_classes
spl_autoload_register('autoload_classes');

//instanciamos la app
$app = new \core\app(true); //true is front, false is back
//lanzamos la app
$app->render();
