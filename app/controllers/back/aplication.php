<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use core\functions;
use \app\models\administrador;
use \core\view;

class aplication
{
    private $url = array('home');
    private $metadata = array('title' => 'Home','modulo'=>'home');
    public function index()
    {
        $head = new head($this->metadata);
        $head->normal();
        
        $footer = new footer();
        $footer->normal();
    }
}
