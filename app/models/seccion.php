<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\functions;

class seccion extends base_model
{
    public static $idname = 'idseccion',
    $table = 'seccion';
    public static function getAll($where = array(), $condiciones = array(), $select = "")
    {
        $connection = database::instance();
        if (!isset($where['estado']) && app::$_front) {
            $where['estado'] = true;
        }
        if(isset($where['idseccioncategoria'])){
            $idseccioncategoria=$where['idseccioncategoria'];
            unset($where['idseccioncategoria']);
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
                $row[$key]['idseccioncategoria'] = functions::decode_json($row[$key]['idseccioncategoria']);
                if(isset($idseccioncategoria) && !in_array($idseccioncategoria,$row[$key]['idseccioncategoria'])){
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
            $row[0]['idseccioncategoria'] = functions::decode_json($row[0]['idseccioncategoria']);
        }
        return (count($row) == 1) ? $row[0] : $row;
    }
}
