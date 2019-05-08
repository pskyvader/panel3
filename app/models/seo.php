<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");

use \core\app;
use \core\database;
use \core\functions;
use \core\image;

class seo extends base_model
{
    public static $idname = 'idseo',
    $table                = 'seo';

    public static function getAll(array $where = array(), array $condiciones = array(), string $select = "")
    {
        $connection = database::instance();
        if (!isset($where['estado']) && app::$_front) {
            $where['estado'] = true;
        }

        if (!isset($condiciones['order'])) {
            $condiciones['order'] = 'orden ASC';
        }

        if (isset($condiciones['palabra'])) {
            $fields                = table::getByname(static::$table);
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
        if (isset($return_total)) {
            return $row[0]['total'];
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
        $row        = $connection->get(static::$table, static::$idname, $where);
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

    public static function copy(int $id, bool $log = true)
    {
        $row = static::getById($id);
        if (isset($row['banner'])) {
            $banner_copy = $row['banner'];
            unset($row['banner']);
        }
        if (isset($row['foto'])) {
            $foto_copy = $row['foto'];
            unset($row['foto']);
        }
        if (isset($row['archivo'])) {
            unset($row['archivo']);
        }
        $fields     = table::getByname(static::$table);
        $insert     = database::create_data($fields, $row);
        $connection = database::instance();
        $row        = $connection->insert(static::$table, static::$idname, $insert);
        if (is_int($row) && $row > 0) {
            $last_id = $row;
            if (isset($foto_copy)) {
                $new_fotos = array();
                foreach ($foto_copy as $key => $foto) {
                    $copiar      = image::copy($foto, $last_id, $foto['folder'], $foto['subfolder'], $last_id, '');
                    $new_fotos[] = $copiar['file'][0];
                    image::regenerar($copiar['file'][0]);
                }
                $update = array('id' => $last_id, 'foto' => functions::encode_json($new_fotos));
                static::update($update);
            }
            if (isset($banner_copy)) {
                $new_banners = array();
                foreach ($banner_copy as $key => $banner) {
                    $copiar        = image::copy($banner, $last_id, $banner['folder'], $banner['subfolder'], $last_id, '');
                    $new_banners[] = $copiar['file'][0];
                    image::regenerar($copiar['file'][0]);
                }
                $update = array('id' => $last_id, 'banner' => functions::encode_json($new_banners));
                static::update($update);
            }
            if ($log) {
                log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            }
            return $last_id;
        } else {
            return $row;
        }
    }

}
