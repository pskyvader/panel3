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
    public static function getAll($where = array(), $condiciones = array(), $select = "")
    {
        $connection = database::instance();
        if (!isset($where['estado']) && app::$_front) {
            $where['estado'] = true;
        }

        if(isset($where['idpadre'])){
            $idpadre=$where['idpadre'];
            unset($where['idpadre']);
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

        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        if ($select == '') {
            foreach ($row as $key => $value) {
                $row[$key]['idpadre'] = functions::decode_json($row[$key]['idpadre']);
                if(isset($idpadre) && !in_array($idpadre,$row[$key]['idpadre'])){
                    unset($row[$key]);
                }
                
                if (isset($row[$key]) && isset($row[$key]['foto'])) {
                    $row[$key]['foto'] = functions::decode_json($row[$key]['foto']);
                }
            }
        }
        return $row;
    }

    public static function getById($id)
    {
        $where = array(static::$idname => $id);
        if (app::$_front) {
            $fields = table::getByname(static::$table);
            if (isset($fields['estado'])) {
                $where['estado'] = true;
            }

        }
        $connection = database::instance();
        $row = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            if (isset($row[0]['foto'])) {
                $row[0]['foto'] = functions::decode_json($row[0]['foto']);
            }
            $row[0]['idpadre'] = functions::decode_json($row[0]['idpadre']);
        }
        return (count($row) == 1) ? $row[0] : $row;
    }
    
    public static function copy($id)
    {
        $row = static::getById($id);
        if (isset($row['foto'])) {
            unset($row['foto']);
        }
        $row['idpadre']=functions::encode_json($row['idpadre']);
        $fields = table::getByname(static::$table);
        $insert = database::create_data($fields, $row);
        $connection = database::instance();
        $row = $connection->insert(static::$table, static::$idname, $insert);
        if ($row) {
            $last_id = $connection->get_last_insert_id();
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            return $last_id;
        } else {
            return $row;
        }
    }
}
