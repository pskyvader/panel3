<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \core\functions;
use \core\view;
use \app\models\administrador as administrador_model;

class error
{
    private $url = array('error');
    private $metadata = array('title' => 'Error','modulo'=>'error');
    public function index()
    {
        if (!administrador_model::verificar_sesion()) {
            $this->url = array('login', 'index', 'home');
            functions::url_redirect($this->url);
        }
        
        $head = new head($this->metadata);
        $head->normal();
        
        $header = new header();
        $header->normal();
        $aside = new aside();
        $aside->normal();
        view::render('404');


        $footer = new footer();
        $footer->normal();
    }
}
