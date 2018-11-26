<?php
namespace app\controllers\front\themes\jycdesayunos;

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
        $banner     = new banner();
        $banner->normal($row_banner);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);

        $about = seccion_model::getById(9);
        view::set('title', $about['titulo']);
        view::set('subtitle', $about['subtitulo']);
        view::set('text', $about['resumen']);
        $seo=seo::getById(7);
        view::set('url', functions::url_seccion(array($seo['url'], 'detail'), $about));
        view::set('image', image::generar_url(image::portada($about['foto']), ''));
        view::render('home-text');

        $footer = new footer();
        $footer->normal();
    }
}
