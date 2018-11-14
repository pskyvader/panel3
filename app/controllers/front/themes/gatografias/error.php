<?php
namespace app\controllers\front\themes\gatografias;

defined("APPPATH") or die("Acceso denegado");
use \core\image;
use \core\view;
use \app\models\banner as banner_model;

class error
{
    protected $url = array('error');
    protected $metadata = array('title' => 'Error');
    protected $breadcrumb = array();
    public function __construct()
    {
    }
    public function index()
    {
        http_response_code(404);
        $this->metadata['class'] = (new \ReflectionClass($this))->getShortName();
        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        $banner=banner_model::getById(1);
        view::set('url',image::generar_url(image::portada($banner['foto']),''));
        
        view::render('404');

        $footer = new footer();
        $footer->normal();
    }
}