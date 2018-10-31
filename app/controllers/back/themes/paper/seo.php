<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seo as seo_model;

class seo extends base
{
    protected $url = array('seo');
    protected $metadata = array('title' => 'SEO','modulo'=>'seo');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new seo_model);
    }
}
