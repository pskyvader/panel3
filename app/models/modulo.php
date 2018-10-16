<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\functions;

class modulo extends base_model
{
    public static $idname = 'idmodulo',
    $table = 'modulo';
    
    public static function getAll($where = array(), $condiciones = array(), $select = "")
    {
        $connection = database::instance();
        /*if (!isset($where['estado']) && app::$_front) {
            $where['estado'] = true;
        }*/

        if (!isset($condiciones['order'])) {
            $condiciones['order'] = 'orden ASC';
        }

        if (isset($condiciones['palabra'])) {
            $condiciones['buscar'] = array(
                'titulo' => $condiciones['palabra'],
            );
        }
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        if ($select == '') {
            foreach ($row as $key => $value) {
                $row[$key]['menu']=functions::decode_json($row[$key]['menu']);
                $row[$key]['mostrar']=functions::decode_json($row[$key]['mostrar']);
                $row[$key]['detalle']=functions::decode_json($row[$key]['detalle']);
                $row[$key]['recortes']=functions::decode_json($row[$key]['recortes']);
                $row[$key]['estado']=functions::decode_json($row[$key]['estado']);
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
            $row[0]['menu']=functions::decode_json($row[0]['menu']);
            $row[0]['mostrar']=functions::decode_json($row[0]['mostrar']);
            $row[0]['detalle']=functions::decode_json($row[0]['detalle']);
            $row[0]['recortes']=functions::decode_json($row[0]['recortes']);
            $row[0]['estado']=functions::decode_json($row[0]['estado']);
        }
        return (count($row) == 1) ? $row[0] : $row;
    }

    public static function copy($id)
    {
        $row = static::getById($id);
        $row['menu']=functions::encode_json($row['menu']);
        $row['mostrar']=functions::encode_json($row['mostrar']);
        $row['detalle']=functions::encode_json($row['detalle']);
        $row['recortes']=functions::encode_json($row['recortes']);
        $row['estado']=functions::encode_json($row['estado']);
        $fields = table::getByname(static::$table);
        $insert = database::create_data($fields, $row);
        $connection = database::instance();
        $row = $connection->insert(static::$table, static::$idname, $insert);
        if ($row) {
            $last_id=$connection->get_last_insert_id();
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            return $last_id;
        } else {
            return $row;
        }
    }

}
