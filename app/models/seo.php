<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");

use \core\app;
use \core\database;
use \core\functions;

class seo extends base_model
{
    public static $idname = 'idseo',
    $table = 'seo';

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
            if (isset($fields['titulo'])) {
                $condiciones['buscar']['titulo'] = $condiciones['palabra'];
            }

            if (isset($fields['keywords'])) {
                $condiciones['buscar']['keywords'] = $condiciones['palabra'];
            }

            if (isset($fields['descripcion'])) {
                $condiciones['buscar']['descripcion'] = $condiciones['palabra'];
            }

            if (isset($fields['metadescripcion'])) {
                $condiciones['buscar']['metadescripcion'] = $condiciones['palabra'];
            }

        }

        if($select=='total'){
            $return_total=true;
        }
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        if ($select == '') {
            foreach ($row as $key => $value) {
                if (isset($row[$key]['foto'])) {
                    $row[$key]['foto'] = functions::decode_json($row[$key]['foto']);
                }
                if (isset($row[$key]['banner'])) {
                    $row[$key]['banner'] = functions::decode_json($row[$key]['banner']);
                }
            }
        }
        if(isset($return_total)){
            return count($row);
        }
        return $row;
    }

    public static function getById($id)
    {
        $where = array(static::$idname => $id);
        if (app::$_front) {
            $fields = table::getByname(static::$table);
            if(isset($fields['estado'])) $where['estado'] = true;
        }
        $connection = database::instance();
        $row = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            if (isset($row[0]['foto'])) {
                $row[0]['foto'] = functions::decode_json($row[0]['foto']);
            }
            if (isset($row[0]['banner'])) {
                $row[0]['banner'] = functions::decode_json($row[0]['banner']);
            }
        }
        return (count($row) == 1) ? $row[0] : $row;
    }

    public static function copy($id)
    {
        $row = static::getById($id);
        if (isset($row['banner'])) {
            unset($row['banner']);
        }
        if (isset($row['foto'])) {
            unset($row['foto']);
        }
        if (isset($row['archivo'])) {
            unset($row['archivo']);
        }
        $fields     = table::getByname(static::$table);
        $insert     = database::create_data($fields, $row);
        $connection = database::instance();
        $row        = $connection->insert(static::$table, static::$idname, $insert);
        if ($row) {
            $last_id = $connection->get_last_insert_id();
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            return $last_id;
        } else {
            return $row;
        }
    }

}