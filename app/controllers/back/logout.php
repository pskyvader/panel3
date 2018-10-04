<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use core\functions;
use \app\models\administrador;

class logout
{
    private $url = array('login','index');
    public function index($url=array())
    {
        administrador::logout();
        functions::url_redirect($this->url);
    }
}
