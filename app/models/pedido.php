<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
//use \core\app;
use \core\database;
//use \core\functions;

class pedido extends base_model
{
    public static $idname = 'idpedido',
    $table = 'pedido';

    public static function insert($data, $log = true)
    {
        if(!isset($data['fecha_creacion'])){
            $data['fecha_creacion']=date('Y-m-d H:i:s');
        }
        $fields     = table::getByname(static::$table);
        $insert     = database::create_data($fields, $data);
        $connection = database::instance();
        $row        = $connection->insert(static::$table, static::$idname, $insert);
        if ($row) {
            $last_id = $connection->get_last_insert_id();
            if ($log) {
                log::insert_log(static::$table, static::$idname, __FUNCTION__, $row);
            }

            return $last_id;
        } else {
            return $row;
        }
    }
}