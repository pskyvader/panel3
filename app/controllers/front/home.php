<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \core\functions;
use \core\view;
use \app\models\seo;

class home
{
    protected $url = array('log');
    protected $metadata = array('title' => 'log');
    protected $breadcrumb = array();
    public function __construct()
    {
        $seo=seo::getById(1);
        $this->url=array($seo['url']);
        $this->metadata['title']=$seo['titulo'];
    }
    public function index()
    {
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        
        $breadcrumb = new breadcrumb();
        $breadcrumb->normal($this->breadcrumb);

        view::render('home');

        $footer = new footer();
        $footer->normal();
    }
}