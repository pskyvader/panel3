<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seccioncategoria as seccioncategoria_model;
//use \app\models\administrador as administrador_model;
//use \app\models\modulo as modulo_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \core\functions;
//use \core\image;

class seccioncategoria extends base
{
    protected $url = array('seccioncategoria');
    protected $metadata = array('title' => 'seccioncategoria', 'modulo' => 'seccioncategoria');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new seccioncategoria_model);
    }
}
