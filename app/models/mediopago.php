<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
//use \core\app;
use \core\database;
//use \core\functions;

class mediopago extends base_model
{
    public static $idname = 'idmediopago',
    $table = 'mediopago';
    public static function getById(int $id)
    {
        $where = array(static::$idname => $id);
        /*if (app::$_front) {
            $fields = table::getByname(static::$table);
            if (isset($fields['estado'])) {
                $where['estado'] = true;
            }
        }*/
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where);
        return (count($row) == 1) ? $row[0] : $row;
    }
}