<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\database;

class profile extends base_model
{
    public static $idname = 'idprofile',
    $table = 'profile';

    public static function getByTipo($tipo)
    {
        $where = array('tipo' => $tipo, 'estado' => true);
        $condition = array('limit' => 1);
        $connection = database::instance();
        $row = $connection->get(static::$table, static::$idname, $where, $condition);
        return (count($row) == 1) ? $row[0] : $row;
    }

}
