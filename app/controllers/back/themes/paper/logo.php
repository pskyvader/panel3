<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\logo as logo_model;

class logo extends base
{
    protected $url = array('logo');
    protected $metadata = array('title' => 'logo','modulo'=>'logo');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new logo_model);
    }
}
