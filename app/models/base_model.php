<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \app\interfaces\crud;
use \core\app;
use \core\database;
use \core\functions;

class base_model implements crud
{
    public static $idname = '',
    $table                = '';

    public static function getAll($where = array(), $condiciones = array(), $select = "")
    {
        $connection = database::instance();
        $fields     = table::getByname(static::$table);
        if (!isset($where['estado']) && app::$_front && isset($fields['estado'])) {
            $where['estado'] = true;
        }

        if (isset($where['idpadre'])) {
            $idpadre = $where['idpadre'];
            unset($where['idpadre']);
            if (isset($condiciones['limit'])) {
                $limit  = $condiciones['limit'];
                $limit2 = 0;
                unset($condiciones['limit']);
            }
            if (isset($condiciones['limit2'])) {
                if (!isset($limit)) {
                    $limit = 0;
                }

                $limit2 = $condiciones['limit2'];
                unset($condiciones['limit2']);
            }
        }


        if (!isset($condiciones['order']) && isset($fields['order'])) {
            $condiciones['order'] = 'orden ASC';
        }

        if (isset($condiciones['palabra'])) {
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
        if ($select == 'total') {
            $return_total = true;
            if (isset($idpadre)) {
                $select = '';
            }
        }
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        foreach ($row as $key => $value) {
            if (isset($row[$key]['idpadre'])) {
                $row[$key]['idpadre'] = functions::decode_json($row[$key]['idpadre']);
                if (isset($idpadre) && !in_array($idpadre, $row[$key]['idpadre'])) {
                    unset($row[$key]);
                }
            }
            if (isset($row[$key]['foto'])) {
                $row[$key]['foto'] = functions::decode_json($row[$key]['foto']);
            }
            if (isset($row[$key]['archivo'])) {
                $row[$key]['archivo'] = functions::decode_json($row[$key]['archivo']);
            }
        }
        if (isset($idpadre)) {
            $row = array_values($row);
        }

        if (isset($limit)) {
            $row = array_slice($row, $limit2, $limit);
        }
        if (isset($return_total)) {
            return count($row);
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
        $row        = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            if (isset($row[0]['foto'])) {
                $row[0]['foto'] = functions::decode_json($row[0]['foto']);
            }
            if (isset($row[0]['archivo'])) {
                $row[0]['archivo'] = functions::decode_json($row[0]['archivo']);
            }
        }
        return (count($row) == 1) ? $row[0] : $row;
    }

    public static function insert($data, $log = true)
    {
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

    public static function update($set, $log = true)
    {
        $where = array(static::$idname => $set['id']);
        unset($set['id']);
        $connection = database::instance();
        $row        = $connection->update(static::$table, static::$idname, $set, $where);
        if ($log) {
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $row);
        }

        return $row;
    }

    public static function delete($id)
    {
        $where      = array(static::$idname => $id);
        $connection = database::instance();
        $row        = $connection->delete(static::$table, static::$idname, $where);
        log::insert_log(static::$table, static::$idname, __FUNCTION__, $where);
        return $row;
    }
    public static function copy($id)
    {
        $row = static::getById($id);
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
