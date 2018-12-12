<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\pedidoproducto as pedidoproducto_model;
//use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
//use \app\models\table;
//use \core\functions;
//use \core\image;

class pedidoproducto extends base
{
    protected $url = array('pedidoproducto');
    protected $metadata = array('title' => 'pedidoproducto','modulo'=>'pedidoproducto');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new pedidoproducto_model);
    }
}
