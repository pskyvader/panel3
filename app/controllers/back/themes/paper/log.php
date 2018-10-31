<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\log as log_model;

class log extends base
{
    protected $url = array('log');
    protected $metadata = array('title' => 'log','modulo'=>'log');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new log_model);
    }
}
