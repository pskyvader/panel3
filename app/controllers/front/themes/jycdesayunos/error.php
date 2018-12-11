<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \core\cache;
use \core\view;

class error
{
    protected $url = array('error');
    protected $metadata = array('title' => 'Error');
    protected $breadcrumb = array();
    public function __construct()
    {
        cache::set_cache(false);
    }
    public function index()
    {
        http_response_code(404);
        $this->metadata['class'] = (new \ReflectionClass($this))->getShortName();
        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        
        view::render('404');

        $footer = new footer();
        $footer->normal();
    }
}