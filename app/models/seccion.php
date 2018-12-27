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
    public static function getAll(array $where = array(), array $condiciones = array(), string $select = "")
    {
        $connection = database::instance();
        if (!isset($where['estado']) && app::$_front) {
            $where['estado'] = true;
        }
        if (isset($where['idseccioncategoria'])) {
            $idseccioncategoria = $where['idseccioncategoria'];
            unset($where['idseccioncategoria']);
            if(isset($condiciones['limit'])){
                $limit=$condiciones['limit'];
                $limit2=0;
                unset($condiciones['limit']);
            }
            if(isset($condiciones['limit2'])){
                if(!isset($limit)) $limit=0;
                $limit2=$condiciones['limit2'];
                unset($condiciones['limit2']);
            }
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
            if(isset($idseccioncategoria)){
                $select='';
            }
        }
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
            foreach ($row as $key => $value) {
                if(isset($row[$key]['idseccioncategoria'])){
                    $row[$key]['idseccioncategoria'] = functions::decode_json($row[$key]['idseccioncategoria']);
                    if (isset($idseccioncategoria) && !in_array($idseccioncategoria, $row[$key]['idseccioncategoria'])) {
                        unset($row[$key]);
                    }
                }
                if (isset($row[$key]) && isset($row[$key]['foto'])) {
                    $row[$key]['foto'] = functions::decode_json($row[$key]['foto']);
                }
                if (isset($row[$key]) && isset($row[$key]['archivo'])) {
                    $row[$key]['archivo'] = functions::decode_json($row[$key]['archivo']);
                }
        }
        if (isset($idseccioncategoria)) {
            $row = array_values($row);
        }
        if(isset($limit)){
            if($limit2==0){
                $row=array_slice($row,$limit2,$limit);
            }else{
                $row=array_slice($row,$limit,$limit2);
            }
        }
        if(isset($return_total)){
            return count($row);
        }
        return $row;
    }

    public static function getById(int $id)
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
            $row[0]['idseccioncategoria'] = functions::decode_json($row[0]['idseccioncategoria']);
            if (isset($idseccioncategoria) && !in_array($idseccioncategoria, $row[0]['idseccioncategoria'])) {
                unset($row[0]);
            }
            if (isset($row[0]) && isset($row[0]['foto'])) {
                $row[0]['foto'] = functions::decode_json($row[0]['foto']);
            }
            if (isset($row[0]) && isset($row[0]['archivo'])) {
                $row[0]['archivo'] = functions::decode_json($row[0]['archivo']);
            }
        }
        return (count($row) == 1) ? $row[0] : $row;
    }

    public static function copy(int $id)
    {
        $row = static::getById($id);
        if (isset($row['foto'])) {
            unset($row['foto']);
        }
        if (isset($row['archivo'])) {
            unset($row['archivo']);
        }
        $row['idseccioncategoria']=functions::encode_json($row['idseccioncategoria']);
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
