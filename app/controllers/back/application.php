<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\logo as logo_model;
use \core\app;
use \core\functions;
use \core\image;
use \core\view;

class application
{
    private $url = array('home');
    private $metadata = array('title' => 'Home', 'modulo' => 'home');
    public function index()
    {
        if (!administrador_model::verificar_sesion()) {
            $this->url = array('login', 'index', 'home');
        }
        $head = new head($this->metadata);
        $head->normal();
        $config = app::getConfig();
        $logo = logo_model::getById(7);
        view::set('color_primario', $config['color_primario']);
        view::set('color_secundario', $config['color_secundario']);
        view::set('logo', image::generar_url($logo['foto'][0], 'icono600'));
        view::set('path', functions::generar_url($this->url));
        view::render('application');
        $footer = new footer();
        $footer->normal();
    }
}
