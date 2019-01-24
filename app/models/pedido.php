<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
//use \core\app;
use \core\database;
use \core\functions;

/**
 * @class pedido
 * Esta tabla NO BORRA CACHE
 */
class pedido extends base_model
{
    public static $idname        = 'idpedido',
    $table                       = 'pedido';
    private static $delete_cache = false;

    public static function insert(array $data, bool $log = true)
    {
        if (!isset($data['fecha_creacion'])) {
            $data['fecha_creacion'] = date('Y-m-d H:i:s');
        }
        $fields     = table::getByname(static::$table);
        $insert     = database::create_data($fields, $data);
        $connection = database::instance();
        $row        = $connection->insert(static::$table, static::$idname, $insert, self::$delete_cache);
        if (is_int($row) && $row > 0) {
            $last_id = $row;
            if ($log) {
                log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            }
            return $last_id;
        } else {
            return $row;
        }
    }

    public static function update(array $set, bool $log = true)
    {
        $where = array(static::$idname => $set['id']);
        unset($set['id']);
        $connection = database::instance();
        $row        = $connection->update(static::$table, static::$idname, $set, $where, self::$delete_cache);
        if ($log) {
            log::insert_log(static::$table, static::$idname, __FUNCTION__, array_merge($set, $where));
        }
        if (is_bool($row) && $row) {
            $row = $where[static::$idname];
        }

        return $row;
    }

    public static function delete(int $id)
    {
        $where      = array(static::$idname => $id);
        $connection = database::instance();
        $row        = $connection->delete(static::$table, static::$idname, $where, self::$delete_cache);
        log::insert_log(static::$table, static::$idname, __FUNCTION__, $where);
        return $row;
    }

    public static function copy(int $id)
    {
        $row = static::getById($id);
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
        $row        = $connection->insert(static::$table, static::$idname, $insert, self::$delete_cache);
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
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            return $last_id;
        } else {
            return $row;
        }
    }

    public static function getByCookie(string $cookie, bool $estado_carro = true)
    {
        $where = array("cookie_pedido" => $cookie);
        if ($estado_carro) {
            $where['idpedidoestado'] = 1;
        }
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where);
        return (count($row) == 1) ? $row[0] : $row;
    }
    public static function getByIdusuario(int $idusuario, bool $estado_carro = true)
    {
        $where = array("idusuario" => $idusuario);
        if ($estado_carro) {
            $where['idpedidoestado'] = 1;
        }
        $condition  = array('order' => static::$idname . ' DESC');
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where, $condition);
        return ($estado_carro && count($row) > 0) ? $row[0] : $row;
    }
}
