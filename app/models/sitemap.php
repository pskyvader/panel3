<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
//use \core\app;
use \core\database;
//use \core\functions;

class sitemap extends base_model
{
    public static $idname = 'idsitemap',
    $table = 'sitemap';
    public static function getAll(array $where = array(), array $condiciones = array(), string $select = "")
    {
        $connection = database::instance();
        if($select=='total'){
            $return_total=true;
        }
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        if(isset($return_total)){
            return count($row);
        }
        return $row;
    }

    public static function truncate()
    {
        $respuesta = array('exito' => true, 'mensaje' => array());
        
        $connection = database::instance();
        $respuesta['exito'] = $connection->truncate(array(static::$table));
        if ($respuesta['exito']) {
        } else {
            $respuesta['mensaje'] = 'Error al vaciar tablas';
        }
        return $respuesta;
    }
}