<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\pedidodireccion as pedidodireccion_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \app\models\table;
//use \core\functions;
//use \core\image;

class pedidodireccion extends base
{
    protected $url = array('pedidodireccion');
    protected $metadata = array('title' => 'pedidodireccion','modulo'=>'pedidodireccion');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new pedidodireccion_model);
    }
}
