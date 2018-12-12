<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\pedido as pedido_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \app\models\table;
//use \core\functions;
//use \core\image;

class pedido extends base
{
    protected $url = array('pedido');
    protected $metadata = array('title' => 'pedido','modulo'=>'pedido');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new pedido_model);
    }
}
