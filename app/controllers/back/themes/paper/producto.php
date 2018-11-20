<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\producto as producto_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \core\functions;
//use \core\image;

class producto extends base
{
    protected $url = array('producto');
    protected $metadata = array('title' => 'producto','modulo'=>'producto');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new producto_model);
    }
}
