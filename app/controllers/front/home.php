<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \app\models\banner as banner_model;
use \app\models\seo;
use \core\functions;
use \core\view;

class home
{
    protected $url = array('home');
    protected $metadata = array('title' => 'home');
    protected $breadcrumb = array();
    public function __construct()
    {
        $seo = seo::getById(1);
        $this->url = array($seo['url']);
        $this->metadata['title'] = $seo['titulo'];
        $this->metadata['keywords_text'] = $seo['keywords'];
        $this->metadata['description_text'] = $seo['metadescripcion'];
    }
    public function index()
    {
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);

        $row_banner = banner_model::getAll(array('tipo' => 1));
        $banner = new banner();
        $banner->normal($row_banner);

        view::render('home');

        $footer = new footer();
        $footer->normal();
    }
}
