<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\database;
use \core\app;
use \core\functions;

class log extends base_model
{
    public static $idname = 'idlog',
    $table = 'log';

    public static function getAll($where = array(), $condiciones = array(), $select = "")
    {
        if (!isset($condiciones['order'])) {
            $condiciones['order'] = 'fecha DESC';
        }
        if (isset($condiciones['palabra'])) {
            $condiciones['buscar'] = array(
                'tabla' => $condiciones['palabra'],
                'accion' => $condiciones['palabra'],
                'administrador' => $condiciones['palabra'],
            );
        }

        $connection = database::instance();
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        return $row;
    }

    public static function insert_log($tabla, $idname, $funcion, $row)
    {
        if ($tabla != static::$table) {
            $administrador = $_SESSION['nombre' . functions::url_amigable(app::$_title)].' ('.$_SESSION['email' . functions::url_amigable(app::$_title)].')';

            $accion = 'metodo: ' . $funcion;
            if (isset($row['titulo'])) {
                $accion .= ', titulo: ' . $row['titulo'];
            } elseif (isset($row['nombre'])) {
                $accion .= ', nombre: ' . $row['nombre'];
            }
            if (isset($row[$idname])) {
                $accion .= ', ID: ' . $row[$idname];
            } elseif (isset($row['id'])) {
                $accion .= ', ID: ' . $row['id'];
            }

            $data = array(
                'administrador' => $administrador,
                'tabla' => $tabla,
                'accion' => $accion,
                'fecha' => date('Y-m-d H:i:s'),
            );
            static::insert($data);
        }
    }

}
