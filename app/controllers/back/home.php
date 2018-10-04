<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use core\functions;
use \app\models\administrador as administrador_model;
use \core\view;

class home
{
    private $url = array('home');
    private $metadata = array('title' => 'Home','modulo'=>'home');
    public function index()
    {
        if (!administrador_model::verificar_sesion()) {
            $this->url = array('login', 'index', 'home');
        }
        functions::url_redirect($this->url);
        
        $head = new head($this->metadata);
        $head->normal();
        
        $header = new header();
        $header->normal();
        $aside = new aside();
        $aside->normal();


        $breadcrumb=array(
            array('url'=>functions::generar_url($this->url),'title'=>$this->metadata['title'],'active'=>'active')
        );
        view::set('breadcrumb',$breadcrumb);
        view::set('title','Home');
        view::render('home');


        $footer = new footer();
        $footer->normal();
    }
}
