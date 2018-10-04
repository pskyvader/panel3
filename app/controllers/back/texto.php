<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\texto as texto_model;

class texto extends base
{
    protected $url = array('texto');
    protected $metadata = array('title' => 'texto', 'modulo' => 'texto');
    protected $breadcrumb = array();
    public function __construct() { parent::__construct(new texto_model); }
}
