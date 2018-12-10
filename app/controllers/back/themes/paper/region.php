<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\region as region_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \core\functions;
//use \core\image;

class region extends base
{
    protected $url = array('region');
    protected $metadata = array('title' => 'region','modulo'=>'region');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new region_model);
    }
}
