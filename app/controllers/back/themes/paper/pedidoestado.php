<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\pedidoestado as pedidoestado_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \app\models\table;
//use \core\functions;
//use \core\image;

class pedidoestado extends base
{
    protected $url = array('pedidoestado');
    protected $metadata = array('title' => 'pedidoestado','modulo'=>'pedidoestado');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new pedidoestado_model);
    }
}
