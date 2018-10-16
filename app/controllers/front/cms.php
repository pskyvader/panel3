<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \app\models\banner as banner_model;
use \app\models\seo;
use \core\functions;
use \core\view;

class cms
{
    protected $url = array('cms');
    protected $metadata = array('title' => 'CMS');
    protected $breadcrumb = array();
    public function __construct()
    {
        $seo = seo::getById(2);
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

        view::render('cms');

        $footer = new footer();
        $footer->normal();
    }
}
