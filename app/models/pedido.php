<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
//use \core\app;
use \core\database;
//use \core\functions;

class pedido extends base_model
{
    public static $idname = 'idpedido',
    $table                = 'pedido';

    public static function insert(array $data, bool $log = true)
    {
        if (!isset($data['fecha_creacion'])) {
            $data['fecha_creacion'] = date('Y-m-d H:i:s');
        }
        $fields     = table::getByname(static::$table);
        $insert     = database::create_data($fields, $data);
        $connection = database::instance();
        $row        = $connection->insert(static::$table, static::$idname, $insert);
        if (is_int($row) && $row>0) {
            $last_id = $row;
            if ($log) {
                log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            }
            return $last_id;
        } else {
            return $row;
        }
    }
    public static function getByCookie(string $cookie, bool $estado_carro = true)
    {
        $where = array("cookie_pedido" => $cookie);
        if ($estado_carro) {
            $where['idpedidoestado'] = 1;
        }
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where);
        return (count($row) == 1) ? $row[0] : $row;
    }
    public static function getByIdusuario(int $idusuario, bool $estado_carro = true)
    {
        $where = array("idusuario" => $idusuario);
        if ($estado_carro) {
            $where['idpedidoestado'] = 1;
        }
        $condition  = array('order' => static::$idname . ' DESC');
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where, $condition);
        return ($estado_carro && count($row) > 0) ? $row[0] : $row;
    }
}