<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\functions;

class texto extends base_model
{
    public static $idname = 'idtexto',
    $table = 'texto';

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
            $fields = table::getByname(static::$table);
            $condiciones['buscar'] = array();
            if(isset($fields['titulo'])) $condiciones['buscar']['titulo']=$condiciones['palabra'];
            if(isset($fields['keywords'])) $condiciones['buscar']['keywords']=$condiciones['palabra'];
            if(isset($fields['descripcion'])) $condiciones['buscar']['descripcion']=$condiciones['palabra'];
            if(isset($fields['metadescripcion'])) $condiciones['buscar']['metadescripcion']=$condiciones['palabra'];        
        }

        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        if ($select == '') {
            foreach ($row as $key => $value) {
                $row[$key]['mapa'] = functions::decode_json($row[$key]['mapa']);
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
            $row[0]['mapa'] = functions::decode_json($row[0]['mapa']);
        }
        return (count($row) == 1) ? $row[0] : $row;
    }
}
