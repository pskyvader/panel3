<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\functions;
use \core\image;
use \core\view;

class table extends base_model
{
    public static $idname = 'idtable',
    $table                = 'table',
    $data                 = array(
        'tablename' => array('titulo' => 'tablename', 'tipo' => 'char(255)'),
        'idname'    => array('titulo' => 'idname', 'tipo' => 'char(255)'),
        'fields'    => array('titulo' => 'fields', 'tipo' => 'longtext'),
        'truncate'  => array('titulo' => 'truncate', 'tipo' => 'tinyint(1)'),
    );

    protected static function get_idname()
    {
        return static::$idname;
    }

    protected static function get_table()
    {
        return static::$table;
    }

    public static function getAll(array $where = array(), array $condiciones = array(), string $select = "")
    {
        $connection = database::instance();
        if ($select == 'total') {
            $return_total = true;
        }
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        foreach ($row as $key => $value) {
            if (isset($row[$key]['fields'])) {
                $row[$key]['fields'] = functions::decode_json($row[$key]['fields']);
            }
        }

        if (isset($return_total)) {
            return count($row);
        }
        return $row;
    }

    public static function getById(int $id)
    {
        $where      = array(static::$idname => $id);
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            $row[0]['fields'] = functions::decode_json($row[0]['fields']);
        }
        return (count($row) == 1) ? $row[0] : $row;
    }

    public static function getByname(string $name)
    {
        if ($name == static::$table) {
            return static::$data;
        }
        $where      = array('tablename' => $name);
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            $row[0]['fields'] = functions::decode_json($row[0]['fields']);
            $fields           = array();
            foreach ($row[0]['fields'] as $key => $field) {
                $fields[$field['titulo']] = $field;
            }
            return $fields;
        } else {
            throw new \Exception("No existe el modelo para la tabla {$name}", 1);
        }
    }

    public static function copy(int $id)
    {
        $row           = static::getById($id);
        $row['fields'] = functions::encode_json($row['fields']);
        $fields        = table::getByname(static::$table);
        $insert        = database::create_data($fields, $row);
        $connection    = database::instance();
        $row           = $connection->insert(static::$table, static::$idname, $insert);
        if ($row) {
            $last_id = $connection->get_last_insert_id();
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            return $last_id;
        } else {
            return $row;
        }
    }

    public static function validate(int $id, bool $log = true)
    {
        $respuesta = array('exito' => true, 'mensaje' => array());
        $row       = static::getById($id);
        $idname    = $row['idname'];
        $tablename = $row['tablename'];
        $fields    = $row['fields'];
        array_unshift($fields, array('titulo' => $idname, 'tipo' => 'int(11)', 'primary' => true));

        $check = array();
        foreach ($fields as $key => $field) {
            if (!isset($check[$field['titulo']])) {
                $check[$field['titulo']] = $field;

                if (!isset($fields[$key]['primary'])) {
                    $fields[$key]['primary'] = false;
                }
            } else {
                $respuesta['mensaje'] = 'Campo <b>"' . $field['titulo'] . '"</b> Duplicado, Corregir.';
                $respuesta['exito']   = false;
                return $respuesta;
            }
        }

        $existe     = static::table_exists($tablename);
        $connection = database::instance();

        if ($existe) {
            $respuesta['mensaje'][] = 'Tabla <b>"' . $tablename . '"</b> existe';

            $prefix = $connection->get_prefix();
            $connection->set_prefix('');

            $table       = 'information_schema.columns';
            $where       = array('table_name' => $prefix . $tablename);
            $condiciones = array();
            $select      = 'COLUMN_NAME,COLUMN_TYPE';
            $row         = $connection->get($table, static::$idname, $where, $condiciones, $select);

            $connection->set_prefix($prefix);

            $columns = array();
            foreach ($row as $key => $column) {
                $columns[$column['COLUMN_NAME']] = $column;
            }

            foreach ($fields as $key => $field) {
                $field['after'] = ($key > 0) ? $fields[$key - 1]['titulo'] : '';

                if (isset($columns[$field['titulo']])) {
                    if ($columns[$field['titulo']]['COLUMN_TYPE'] == $field['tipo']) {
                        $respuesta['mensaje'][] = 'Columna <b>"' . $field['titulo'] . '"</b> correcta';
                    } else {
                        $respuesta['mensaje'][] = 'Columna <b>"' . $field['titulo'] . '"</b> incorrecta, Modificada';
                        $respuesta['exito']     = $connection->modify($tablename, $field['titulo'], $field['tipo']);
                        if (!$respuesta['exito']) {
                            $respuesta['mensaje'][] = 'ERROR AL MODIFICAR campo ' . $field['titulo'];
                            return $respuesta;
                        }
                    }
                } else {
                    $respuesta['mensaje'][] = 'Columna <b>"' . $field['titulo'] . '"</b> No existe, Creada';
                    $respuesta['exito']     = $connection->add($tablename, $field['titulo'], $field['tipo'], $field['after'], $field['primary']);
                    if (!$respuesta['exito']) {
                        $respuesta['mensaje'][] = 'ERROR AL AGREGAR CAMPO ' . $field['titulo'];
                        return $respuesta;
                    }
                }
            }
        } else {
            $respuesta['mensaje'][] = 'Tabla <b>"' . $tablename . '"</b> No existe, Creada';
            $respuesta['exito']     = $connection->create($tablename, $fields);
            if (!$respuesta['exito']) {
                $respuesta['mensaje'][] = 'ERROR AL CREAR TABLA ' . $tablename;
            }
        }
        if ($log) {
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $row);
        }

        return $respuesta;
    }

    public static function table_exists(string $tablename)
    {
        $config     = app::getConfig();
        $connection = database::instance();
        $prefix     = $connection->get_prefix();
        $connection->set_prefix('');
        $table       = 'information_schema.tables';
        $where       = array('table_schema' => $config["database"], 'table_name' => $prefix . $tablename);
        $condiciones = array();
        $select      = 'count(*) as count';
        $row         = $connection->get($table, static::$idname, $where, $condiciones, $select);
        $connection->set_prefix($prefix);
        return ($row[0]['count'] == 1);
    }

    public static function generar($id)
    {
        $config    = app::getConfig();
        $respuesta = array('exito' => true, 'mensaje' => array());
        $row       = static::getById($id);
        $idname    = $row['idname'];
        $tablename = $row['tablename'];
        $dir       = app::get_dir(true);
        $destino   = $dir . app::NAMESPACE_BACK . $config['theme_back'] . '\\' . $tablename . '.php';
        if (file_exists($destino)) {
            $respuesta['mensaje'][] = 'Controlador ' . $tablename . ' ya existe';
        } else {
            $controller_url         = $dir . 'app\templates\controllers\back\controller.tpl';
            $controller_template    = view::render_template(array('name' => $tablename, 'theme' => $config['theme_back']), file_get_contents($controller_url));
            $respuesta['mensaje'][] = 'Controlador ' . $tablename . ' no existe, creado';
            file_put_contents($destino, $controller_template);
        }

        $destino = $dir . 'app\models\\' . $tablename . '.php';
        if (file_exists($destino)) {
            $respuesta['mensaje'][] = 'Modelo ' . $tablename . ' ya existe';
        } else {
            $model_url              = $dir . 'app\templates\models\back\model.tpl';
            $model_template         = view::render_template(array('class' => $tablename, 'table' => $tablename, 'idname' => $idname), file_get_contents($model_url));
            $respuesta['mensaje'][] = 'Modelo ' . $tablename . ' no existe, creado';
            file_put_contents($destino, $model_template);
        }
        log::insert_log(static::$table, static::$idname, __FUNCTION__, $row);
        return $respuesta;
    }

    public static function truncate(array $tables)
    {
        $respuesta = array('exito' => true, 'mensaje' => array());
        foreach ($tables as $key => $table) {
            $respuesta['mensaje'][] = 'Tabla ' . $table . ' vaciada';
        }
        $connection         = database::instance();
        $respuesta['exito'] = $connection->truncate($tables);
        if ($respuesta['exito']) {
            foreach ($tables as $key => $table) {
                image::delete($table);
            }
        } else {
            $respuesta['mensaje'] = 'Error al vaciar tablas';
        }
        return $respuesta;
    }
}
