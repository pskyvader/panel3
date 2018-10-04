<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\profile as profile_model;

class profile extends base
{
    protected $url = array('profile');
    protected $metadata = array('title' => 'Perfiles','modulo'=>'profile');
    public function __construct()
    {
        parent::__construct(new profile_model);
    }
}
