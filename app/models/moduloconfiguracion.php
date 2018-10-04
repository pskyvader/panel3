<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\functions;

class moduloconfiguracion extends base_model
{
    public static $idname = 'idmoduloconfiguracion',
    $table = 'moduloconfiguracion';

    public static function getAll($where = array(), $condiciones = array(), $select = "")
    {
        $connection = database::instance();
        if (!isset($where['estado']) && app::$_front) {
            $where['estado'] = true;
        }

        if (!isset($condiciones['order'])) {
            $condiciones['order'] = 'orden ASC';
        }

        if (isset($condiciones['palabra'])) {
            $condiciones['buscar'] = array(
                'titulo' => $condiciones['palabra']
            );
        }

        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        if ($select == '') {
            foreach ($row as $key => $value) {
                $row[$key]['mostrar']=functions::decode_json($row[$key]['mostrar']);
                $row[$key]['detalle']=functions::decode_json($row[$key]['detalle']);
            }
        }

        return $row;
    }

    public static function getById($id)
    {
        $where = array(static::$idname => $id);
        $connection = database::instance();
        $row = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            $row[0]['mostrar']=functions::decode_json($row[0]['mostrar']);
            $row[0]['detalle']=functions::decode_json($row[0]['detalle']);
        }
        return (count($row) == 1) ? $row[0] : $row;
    }

    public static function getByModulo($modulo)
    {
        $where = array('module' => $modulo);
        $connection = database::instance();
        $row = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            $row[0]['mostrar']=functions::decode_json($row[0]['mostrar']);
            $row[0]['detalle']=functions::decode_json($row[0]['detalle']);
        }
        return (count($row) == 1) ? $row[0] : $row;
    }
}
