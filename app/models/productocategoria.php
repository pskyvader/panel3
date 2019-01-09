<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\image;
use \core\functions;

class productocategoria extends base_model
{
    public static $idname = 'idproductocategoria',
    $table                = 'productocategoria';

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
        $row        = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            if (isset($row[0]['foto'])) {
                $row[0]['foto'] = functions::decode_json($row[0]['foto']);
            }
            $row[0]['idpadre'] = functions::decode_json($row[0]['idpadre']);
        }
        return (count($row) == 1) ? $row[0] : $row;
    }

    public static function copy(int $id)
    {
        $row = static::getById($id);
        if (isset($row['foto'])) {
            $foto_copy=$row['foto'];
            unset($row['foto']);
        }
        $row['idpadre'] = functions::encode_json($row['idpadre']);
        $fields         = table::getByname(static::$table);
        $insert         = database::create_data($fields, $row);
        $connection     = database::instance();
        $row            = $connection->insert(static::$table, static::$idname, $insert);
        if (is_int($row) && $row>0) {
            $last_id = $row;
            if(isset($foto_copy)){
                $new_fotos=array();
                foreach ($foto_copy as $key => $foto) {
                    $copiar = image::copy($foto, $last_id, $foto['folder'], $foto['subfolder'], $last_id, '');
                    $new_fotos[]=$copiar['file'][0];
                    image::regenerar($copiar['file'][0]);
                }
                $update=array('id'=>$last_id,'foto'=>functions::encode_json($new_fotos));
                static::update($update);
            }
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            return $last_id;
        } else {
            return $row;
        }
    }
}
