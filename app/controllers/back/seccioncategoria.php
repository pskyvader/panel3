<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seccioncategoria as seccioncategoria_model;

class seccioncategoria extends base
{
    protected $url = array('seccioncategoria');
    protected $metadata = array('title' => 'seccioncategoria','modulo'=>'seccioncategoria');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new seccioncategoria_model);
    }
}
