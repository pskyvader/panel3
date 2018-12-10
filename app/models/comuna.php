<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\functions;

class comuna extends base_model
{
    public static $idname = 'idcomuna',
    $table = 'comuna';
}