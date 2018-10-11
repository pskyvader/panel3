<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\functions;

class seccioncategoria extends base_model
{
    public static $idname = 'idseccioncategoria',
    $table = 'seccioncategoria';
}
