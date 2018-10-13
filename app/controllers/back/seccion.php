<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seccion as seccion_model;

use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
use \core\functions;
//use \core\image;

class seccion extends base
{
    protected $url = array('seccion');
    protected $metadata = array('title' => 'seccion', 'modulo' => 'seccion');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new seccion_model);
    }
}
