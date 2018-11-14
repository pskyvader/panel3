<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\configuracion as configuracion_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \core\functions;
//use \core\image;

class configuracion extends base
{
    protected $url = array('configuracion');
    protected $metadata = array('title' => 'configuracion','modulo'=>'configuracion');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new configuracion_model);
    }
}
