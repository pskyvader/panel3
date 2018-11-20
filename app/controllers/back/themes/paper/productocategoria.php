<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\productocategoria as productocategoria_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \core\functions;
//use \core\image;

class productocategoria extends base
{
    protected $url = array('productocategoria');
    protected $metadata = array('title' => 'productocategoria','modulo'=>'productocategoria');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new productocategoria_model);
    }
}
