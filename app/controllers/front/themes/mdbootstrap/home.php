<?php
namespace app\controllers\front\themes\mdbootstrap;

defined("APPPATH") or die("Acceso denegado");
use \app\models\banner as banner_model;
use \app\models\seccion as seccion_model;
use \app\models\seo;
use \core\functions;
use \core\image;
use \core\view;

class home extends base
{
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo']);
    }
    public function index()
    {
        $this->meta($this->seo);
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();


        $row_banner = banner_model::getAll(array('tipo' => 1));
        $banner = new banner();
        $banner->normal($row_banner);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);
        
        $var = array('tipo'=>1,'destacado'=>true);

        $row = seccion_model::getAll($var);

        if (count($row) > 0) {
            $seo=seo::getById(2);
            $this->url = array($seo['url']);
            view::set('title','Servicios destacados');
            view::render('title');
            $secciones = $this->lista($row, 'sub','lista');
            view::set('list', $secciones);
            view::render('grid-3');
        }

        $footer = new footer();
        $footer->normal();
    }
}
