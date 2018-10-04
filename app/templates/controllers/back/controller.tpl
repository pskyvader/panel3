<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\{name} as {name}_model;

class {name} extends base
{
    protected $url = array('{name}');
    protected $metadata = array('title' => '{name}','modulo'=>'{name}');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new {name}_model);
    }
}
