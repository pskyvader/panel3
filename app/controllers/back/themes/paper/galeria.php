<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\galeria as galeria_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \core\functions;
//use \core\image;

class galeria extends base
{
    protected $url = array('galeria');
    protected $metadata = array('title' => 'galeria','modulo'=>'galeria');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new galeria_model);
    }
}
