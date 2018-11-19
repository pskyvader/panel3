<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \core\app;
use \core\functions;
use \core\view;

class update extends base
{
    protected $url = array('update');
    protected $metadata = array('title' => 'update', 'modulo' => 'update');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(null);
    }
    public function index()
    {
        if (!administrador_model::verificar_sesion()) {
            $this->url = array('login', 'index', 'update');
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        $aside = new aside();
        $aside->normal();

        view::render('update');

        $footer = new footer();
        $footer->normal();
    }
}
