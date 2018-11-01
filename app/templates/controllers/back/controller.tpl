<?php
namespace app\controllers\back\themes\{theme};

defined("APPPATH") or die("Acceso denegado");
use \app\models\{name} as {name}_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \core\functions;
//use \core\image;

class {name} extends base
{
    protected $url = array('{name}');
    protected $metadata = array('title' => '{name}','modulo'=>'{name}');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new {name}_model);
    }
}
