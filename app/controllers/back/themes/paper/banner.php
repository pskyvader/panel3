<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\banner as banner_model;

class banner extends base
{
    protected $url = array('banner');
    protected $metadata = array('title' => 'banner','modulo'=>'banner');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new banner_model);
    }
}
