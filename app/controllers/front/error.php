<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \core\functions;
use \core\view;
use \app\models\seo;
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
        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        
        view::render('404');

        $footer = new footer();
        $footer->normal();
    }
}