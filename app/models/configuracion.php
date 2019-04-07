<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
//use \core\app;
use \core\database;

//use \core\functions;

class configuracion extends base_model
{
    public static $idname = 'idconfiguracion',
    $table                = 'configuracion';

    public static function getByVariable(string $variable, $default = null)
    {
        $where      = array('variable' => $variable);
        $condicion  = array('limit' => 1);
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where, $condicion);
        if (count($row) == 1) {
            return $row[0]['valor'];
        } else {
            if ($default == null) {
                return false;
            } else {
                static::setByVariable($variable, $default);
                return $default;
            }
        }
    }

    public static function setByVariable(string $variable, string $valor)
    {
        $where      = array('variable' => $variable);
        $condicion  = array('limit' => 1);
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where, $condicion);

        if (count($row) == 0) {
            $row = self::insert(array('variable' => $variable, 'valor' => $valor));
        } else {
            $row = self::update(array('variable' => $variable, 'valor' => $valor, 'id' => $row[0][0]));
        }
        return $row;
    }
}
