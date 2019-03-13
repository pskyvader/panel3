<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\mediopago as mediopago_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \app\models\table;
//use \core\functions;
//use \core\image;

class mediopago extends base
{
    protected $url = array('mediopago');
    protected $metadata = array('title' => 'Medios de pago','modulo'=>'mediopago');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new mediopago_model);
    }
}
