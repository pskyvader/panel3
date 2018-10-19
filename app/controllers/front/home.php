<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \app\models\banner as banner_model;
use \app\models\seo;
use \core\functions;
use \core\image;
use \core\view;

class home extends base
{
    public function __construct()
    {
        parent::__construct(seo::getById(1));
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
        view::set('title','Categorias destacadas');
        view::render('title');

        $footer = new footer();
        $footer->normal();
    }
}
