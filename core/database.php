<?php
namespace core;

defined("APPPATH") or die("Acceso denegado");

use \core\app;
use \core\cache;
use \core\functions;

/**
 * @class Database
 */
class database
{

    /**
     * @desc nombre del usuario de la base de datos
     * @var $_dbUser
     * @access private
     */
    private $_dbUser;

    /**
     * @desc password de la base de datos
     * @var $_dbPassword
     * @access private
     */
    private $_dbPassword;

    /**
     * @desc nombre del host
     * @var $_dbHost
     * @access private
     */
    private $_dbHost;

    /**
     * @desc nombre de la base de datos
     * @var $_dbName
     * @access protected
     */
    protected $_dbName;

    /**
     * @desc conexión a la base de datos
     * @var $_connection
     * @access private
     */
    private $_connection;

    /**
     * @desc instancia de la base de datos
     * @var $_instance
     * @access private
     */
    private static $_instance;

    /**
     * @desc prefijo
     * @var $_prefix
     * @access private
     */
    private static $_prefix;

    //public $llamadas=array();

    /**
     * [__construct]
     */
    private function __construct()
    {
        try {
            //load from config/config.ini
            $config            = app::getConfig();
            $this->_dbHost     = $config["host"];
            $this->_dbUser     = $config["user"];
            $this->_dbPassword = $config["password"];
            $this->_dbName     = $config["database"];
            self::$_prefix     = $config["prefix"] . "_";
            $this->conect();
        } catch (\PDOException $e) {
            throw new \Exception("Error {$e->getMessage()}", 1);
            die();
        }
    }

    private function conect()
    {
        $this->_connection = new \PDO('mysql:host=' . $this->_dbHost . '; dbname=' . $this->_dbName, $this->_dbUser, $this->_dbPassword);
        $this->_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->_connection->exec("SET CHARACTER SET utf8");
    }

    /**
     * [prepare]
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    private function prepare($sql)
    {
        return $this->_connection->prepare($sql);
    }
    //Procesar consulta, sql=consulta,return=devolver resultados o solo true o false si se ejecuto la operacion
    private function consulta($sql, $return, $delete_cache = true)
    {
        /*foreach ($this->llamadas as $key => $ll) {
        if($ll['consulta']==$sql){
        return $ll['resultado'];
        }
        }*/
        //$ll=array('consulta'=>$sql);
        //$t=microtime(true)*1000;
        try {
            $query = $this->prepare($sql);
            $query->execute();
            if ($return) {
                $rows = $query->fetchAll();
            } else {
                if ($delete_cache) {
                    cache::delete_cache();
                }
            }

        } catch (\PDOException $e) {
            if (error_reporting()) {
                echo "Consulta: " . $sql . "<br>";
                print "Error!: " . $e->getMessage();
            }
        }

        if (!isset($rows)) {
            if ($return) {
                $rows = array();
            } else {
                $rows = true;
            }
        }
        //$ll['resultado']=$rows;
        // $ll['tiempo']=(microtime(true)*1000)-$t;
        //$this->llamadas[]=$ll;

        return $rows;
    }

    public function get_last_insert_id()
    { //ultimo elemento insertado
        return (int) $this->_connection->lastInsertId();
    }

    public function get($table, $idname, $where, $condiciones = array(), $select = "")
    {
        if ($select == "") {
            $select = "*";
        } elseif ($select == 'total') {
            $select = $idname;
        }

        $sql = "SELECT " . $select . " FROM " . self::$_prefix . $table;
        $sql .= " WHERE (TRUE";
        foreach ($where as $key => $value) {
            $sql .= " AND " . $key . "='" . $value . "'";
        }
        $sql .= ") ";

        if (isset($condiciones['buscar']) && is_array($condiciones['buscar'])) {
            $sql .= " AND (";
            $count = 0;
            foreach ($condiciones['buscar'] as $key => $value) {
                $count++;
                $sql .= $key . " LIKE '%" . $value . "%'";
                $sql .= ($count < count($condiciones['buscar'])) ? " OR " : "";
            }
            $sql .= ") ";
        }

        if (isset($condiciones['order'])) {
            $sql .= " ORDER BY " . $condiciones['order'];
        }

        if (isset($condiciones['group'])) {
            $sql .= " GROUP BY " . $condiciones['group'];
        }

        if (isset($condiciones['limit'])) {
            $sql .= " LIMIT " . $condiciones['limit'];
            if (isset($condiciones['limit2'])) {
                $sql .= " , " . $condiciones['limit2'];
            }
        }
        $row = $this->consulta($sql, true);
        return $row;
    }

    public function insert($table, $idname, $insert, $delete_cache = true)
    { //consulta insert
        $valor_primario = "";
        $image          = array();
        if (isset($insert['image'])) {
            $image = $insert['image'];
            unset($insert['image']);
        }
        $file = array();
        if (isset($insert['file'])) {
            $file = $insert['file'];
            unset($insert['file']);
        }
        $sql = "INSERT INTO " . self::$_prefix . $table;
        $sql .= "(" . $idname;
        foreach ($insert as $key => $value) {
            $sql .= "," . $key;
        }
        $sql .= ") VALUES ('" . $valor_primario . "'";
        foreach ($insert as $key => $value) {
            $sql .= ",";
            $sql .= ($value == "true" || $value == "false") ? $value : "'" . str_replace("'", "\\'", $value) . "'";
        }
        $sql .= ")";
        $row = $this->consulta($sql, false, $delete_cache);
        if ($row) {
            $last_id = $this->get_last_insert_id();
            if (count($image) > 0) {
                $this->process_image($image, $table, $idname, $last_id);
            }
            if (count($file) > 0) {
                $this->process_file($file, $table, $idname, $last_id);
            }
            return $last_id;
        }
        return $row;
    }

    public function update($table, $idname, $set, $where, $delete_cache = true)
    { //consulta update
        $set   = self::process_multiple($set);
        $image = array();
        if (isset($set['image'])) {
            $image = $set['image'];
            unset($set['image']);
        }
        $file = array();
        if (isset($set['file'])) {
            $file = $set['file'];
            unset($set['file']);
        }
        if (isset($set['...'])) {
            unset($set['...']);
        }
        $sql = "UPDATE " . self::$_prefix . $table;
        $sql .= " SET ";
        $count = 0;
        foreach ($set as $key => $value) {
            $count++;
            $sql .= $key . "=";
            $sql .= ($value == "true" || $value == "false") ? $value : "'" . str_replace("'", "\\'", $value) . "'";
            $sql .= ($count < count($set)) ? ", " : "";
        }
        $sql .= "";
        $sql .= " WHERE (TRUE";
        foreach ($where as $key => $value) {
            $sql .= " AND " . $key . "='" . $value . "'";
        }
        $sql .= ") ";
        if (count($where) > 0) {
            $row = $this->consulta($sql, false, $delete_cache);
            if ($row) {
                if (count($image) > 0) {
                    $this->process_image($image, $table, $idname, $where[$idname]);
                }
                if (count($file) > 0) {
                    $this->process_file($file, $table, $idname, $where[$idname]);
                }
            }
            return $row;
        } else {
            echo "error cantidad de condiciones";
            return false;
        }
    }

    public function delete($table, $idname, $where, $delete_cache = true)
    { //consulta delete
        $sql = "DELETE FROM " . self::$_prefix . $table;
        $sql .= " WHERE (TRUE";
        foreach ($where as $key => $value) {
            $sql .= " AND " . $key . "='" . $value . "'";
        }
        $sql .= ")";
        if (count($where) > 0) {
            $row = $this->consulta($sql, false, $delete_cache);
            image::delete($table, '', $where[$idname]);
            file::delete($table, '', $where[$idname]);
            return $row;
        } else {
            echo "error cantidad de condiciones";
            return false;
        }
    }

    public function modify($table, $column, $type)
    { //consulta modificar campo
        $valor_primario = "";
        $sql            = "ALTER TABLE " . self::$_prefix . $table;
        $sql .= " MODIFY " . $column . " " . $type . " NOT NULL ";
        if ($type == 'tinyint(1)') {
            $sql .= " DEFAULT '1' ";
        }

        $row = $this->consulta($sql, false);
        return $row;
    }

    public function add($table, $column, $type, $after = '', $primary = false)
    { //consulta agregar campo
        $valor_primario = "";
        $sql            = "ALTER TABLE " . self::$_prefix . $table;
        $sql .= " ADD " . $column . " " . $type . " NOT NULL ";
        if ($type == 'tinyint(1)') {
            $sql .= " DEFAULT '1' ";
        }

        if ($primary) {
            $sql .= " AUTO_INCREMENT ";
        }
        if ($after != '') {
            $sql .= " AFTER " . $after;
        } else {
            $sql .= " FIRST";
        }
        if ($primary) {
            $sql .= ", ADD PRIMARY KEY ('" . $column . "')";
        }
        $row = $this->consulta($sql, false);
        return $row;
    }

    public function create($table, $columns)
    { //consulta crear tabla
        $valor_primario = "";
        $sql            = "CREATE TABLE " . self::$_prefix . $table . " (";
        foreach ($columns as $key => $column) {
            if ($key > 0) {
                $sql .= ",";
            }

            $sql .= $column['titulo'] . " " . $column['tipo'] . " NOT NULL ";

            if ($column['tipo'] == 'tinyint(1)') {
                $sql .= " DEFAULT '1' ";
            }

            if ($column['primary']) {
                $sql .= " AUTO_INCREMENT PRIMARY KEY ";
            }
        }
        $sql .= " )";
        $row = $this->consulta($sql, false);
        return $row;
    }
    public function truncate($tables)
    { //consulta crear tabla
        $valor_primario = "";
        $sql            = "";
        foreach ($tables as $key => $table) {
            $sql .= "TRUNCATE TABLE " . self::$_prefix . $table . " ;";
        }
        $row = $this->consulta($sql, false);
        return $row;
    }

    public function restore_backup($backup)
    {
        $sql   = file_get_contents($backup);
        $exito = $this->consulta($sql, false);
        if ($exito) {
            unlink($backup);
        }
        return $exito;
    }

    public function backup($tables = '*')
    {
        $respuesta                     = array('exito' => false, 'mensaje' => 'Error al respaldar base de datos', 'sql' => array());
        $this->disableForeignKeyChecks = true;
        $this->batchSize               = 1000; // default 1000 rows
        try {
            /**
             * Tables to export
             */
            if ($tables == '*') {
                $tables = array();
                $row    = $this->consulta('SHOW TABLES', true);
                foreach ($row as $key => $value) {
                    $tables[] = $value[0];
                }
            } else {
                $tables = is_array($tables) ? $tables : explode(',', str_replace(' ', '', $tables));
            }
            $sql = "";
            //$sql .= "CREATE DATABASE IF NOT EXISTS `" . $this->_dbName . "`;\n\n";
            //$sql .= 'USE `' . $this->_dbName . "`;\n\n";
            /**
             * Disable foreign key checks
             */
            if ($this->disableForeignKeyChecks === true) {
                $sql .= "SET foreign_key_checks = 0;\n\n";
            }

            /**
             * Iterate tables
             */
            foreach ($tables as $table) {
                /**
                 * CREATE TABLE
                 */
                $sql .= 'DROP TABLE IF EXISTS `' . $table . '`;';
                $row = $this->consulta('SHOW CREATE TABLE `' . $table . '`', true);
                $sql .= "\n\n" . $row[0][1] . ";\n\n";

                /**
                 * INSERT INTO
                 */
                $row     = $this->consulta('SELECT COUNT(*) FROM `' . $table . '`', true);
                $numRows = $row[0][0];
                // Split table in batches in order to not exhaust system memory
                $numBatches = intval($numRows / $this->batchSize) + 1; // Number of while-loop calls to perform

                $campos = $this->consulta("SELECT COLUMN_NAME,COLUMN_TYPE FROM information_schema.columns WHERE table_schema='" . $this->_dbName . "' AND table_name='" . $table . "'", true);

                for ($b = 1; $b <= $numBatches; $b++) {
                    $query         = 'SELECT * FROM `' . $table . '` LIMIT ' . ($b * $this->batchSize - $this->batchSize) . ',' . $this->batchSize;
                    $row           = $this->consulta($query, true);
                    $realBatchSize = count($row); // Last batch size can be different from $this->batchSize
                    $numFields     = count($campos);
                    if ($realBatchSize !== 0) {
                        $sql .= 'INSERT INTO `' . $table . '` VALUES ';
                        foreach ($row as $key => $fila) {
                            $rowCount = $key + 1;
                            $sql .= '(';

                            foreach ($campos as $k => $v) {
                                $j = $v[0];
                                if (isset($fila[$j])) {
                                    $fila[$j] = addslashes($fila[$j]);
                                    $fila[$j] = str_replace("\n", "\\n", $fila[$j]);
                                    $fila[$j] = str_replace("\r", "\\r", $fila[$j]);
                                    $fila[$j] = str_replace("\f", "\\f", $fila[$j]);
                                    $fila[$j] = str_replace("\t", "\\t", $fila[$j]);
                                    $fila[$j] = str_replace("\v", "\\v", $fila[$j]);
                                    $fila[$j] = str_replace("\a", "\\a", $fila[$j]);
                                    $fila[$j] = str_replace("\b", "\\b", $fila[$j]);
                                    $sql .= '"' . $fila[$j] . '"';
                                } else {
                                    $sql .= 'NULL';
                                }

                                if ($k < ($numFields - 1)) {
                                    $sql .= ',';
                                }
                            }

                            if ($rowCount == $realBatchSize) {
                                $rowCount = 0;
                                $sql .= ");\n"; //close the insert statement
                            } else {
                                $sql .= "),\n"; //close the row
                            }

                            $rowCount++;

                        }

                        $respuesta['sql'][] = $sql;
                        $sql                = '';
                    } else {
                        $respuesta['sql'][] = $sql;
                        $sql                = '';
                    }
                }

                /**
                 * CREATE TRIGGER
                 */
                // Check if there are some TRIGGERS associated to the table
                /*$query = "SHOW TRIGGERS LIKE '" . $table . "%'";
                $result = mysqli_query ($this->conn, $query);
                if ($result) {
                $triggers = array();
                while ($trigger = mysqli_fetch_row ($result)) {
                $triggers[] = $trigger[0];
                }

                // Iterate through triggers of the table
                foreach ( $triggers as $trigger ) {
                $query= 'SHOW CREATE TRIGGER `' . $trigger . '`';
                $result = mysqli_fetch_array (mysqli_query ($this->conn, $query));
                $sql.= "\nDROP TRIGGER IF EXISTS `" . $trigger . "`;\n";
                $sql.= "DELIMITER $$\n" . $result[2] . "$$\n\nDELIMITER ;\n";
                }
                $sql.= "\n";
                $this->saveFile($sql);
                $sql = '';
                }*/

                $sql .= "\n\n";
            }
            /**
             * Re-enable foreign key checks
             */
            if ($this->disableForeignKeyChecks === true) {
                $sql .= "SET foreign_key_checks = 1;\n";
            }

            $respuesta['sql'][] = $sql;
            $respuesta['exito'] = true;
        } catch (Exception $e) {
            $respuesta['mensaje'] = $e->getMessage();
        }
        return $respuesta;
    }

    public static function encript($password)
    {
        $salt = sha1($password);
        $p    = crypt($password, $salt);
        return $salt . sha1($p);
    }

    public static function create_data($model, $data)
    {
        $data = self::process_multiple($data);
        $m    = array();
        foreach ($model as $key => $value) {
            if (isset($data[$key])) {
                $m[$key] = $data[$key];
            } else {
                if ($value['tipo'] == 'tinyint(1)') {
                    $m[$key] = 'true';
                } else {
                    $m[$key] = '';
                }
            }
        }
        if (isset($data['image'])) {
            $m['image'] = $data['image'];
        }
        if (isset($data['file'])) {
            $m['file'] = $data['file'];
        }
        return $m;
    }

    private static function process_multiple($data)
    {
        if (isset($data['multiple'])) {
            foreach ($data['multiple'] as $key => $multiple) {
                $row = array();
                foreach ($multiple as $k => $e) {
                    if (is_array($e)) {
                        foreach ($e as $a => $f) {
                            if ($key == "image" || $key == "file") {
                                foreach ($f as $ke => $va) {
                                    $row[$k][$ke][$a] = $va;
                                }
                            } else {
                                $row[$a][$k] = $f;
                            }
                        }
                    } else {
                        $row[$k] = $e;
                    }
                }
                if ($key != "image" && $key != "file") {
                    $data[$key] = functions::encode_json($row);
                } else {
                    $data[$key] = $row;
                }

            }
            unset($data['multiple']);
        }
        return $data;
    }

    private function process_image($image, $table, $idname, $id)
    {
        $data = array();
        $ids  = array();
        foreach ($image as $key => $img) { //cada campo
            $row     = array();
            $portada = false;
            foreach ($img as $k => $f) { //cada foto
                if (isset($f['tmp']) && $f['tmp'] != '') {
                    $f = image::move($f, $table, $key, $id);
                }
                $ids[$key][$f['id']] = $f['url'];
                if ($f['portada'] == 'true') {
                    if ($portada) {
                        $f['portada'] = 'false';
                    } else {
                        $portada = true;
                    }
                }
                $f['parent'] = $id;
                $f['folder'] = $table;
                $row[$k]     = $f;
            }
            if (!$portada) {
                $row[0]['portada'] = 'true';
            }

            $data[$key] = functions::encode_json($row);
        }

        $row = $this->get($table, $idname, array($idname => $id), array('limit' => 1));
        $this->update($table, $idname, $data, array($idname => $id));
        foreach ($ids as $key => $value) {
            $images = json_decode(html_entity_decode($row[0][$key]), true);
            if (is_array($images)) {
                foreach ($images as $k => $file) {
                    if (!isset($value[$file['id']]) || $value[$file['id']] != $file['url']) {
                        image::delete($table, $file, $id, $key);
                    }
                }
            }
        }
        image::delete_temp();
        return $data;
    }

    private function process_file($file, $table, $idname, $id)
    {
        $data = array();
        $ids  = array();
        foreach ($file as $key => $archivo) { //cada campo
            $row = array();
            foreach ($archivo as $k => $f) { //cada archivo
                if (isset($f['tmp']) && $f['tmp'] != '') {
                    $f = file::move($f, $table, $key, $id);
                }
                $ids[$key][$f['id']] = $f['url'];
                $f['parent']         = $id;
                $f['folder']         = $table;
                $row[$k]             = $f;
            }
            $data[$key] = functions::encode_json($row);
        }

        $row = $this->get($table, $idname, array($idname => $id), array('limit' => 1));
        $this->update($table, $idname, $data, array($idname => $id));
        foreach ($ids as $key => $value) {
            $files = json_decode(html_entity_decode($row[0][$key]), true);
            if (is_array($files)) {
                foreach ($files as $k => $file) {
                    if (!isset($value[$file['id']]) || $value[$file['id']] != $file['url']) {
                        file::delete($table, $file, $id, $key);
                    }
                }
            }
        }
        file::delete_temp();
        return $data;
    }

    public static function set_prefix($prefix)
    {
        self::$_prefix = $prefix;
    }

    public static function get_prefix()
    {
        return self::$_prefix;
    }
    /**
     * [instance singleton]
     * @return [object] [class database]
     */
    public static function instance()
    {
        if (!isset(self::$_instance)) {
            $class           = __CLASS__;
            self::$_instance = new $class;
        }
        return self::$_instance;
    }

    /**
     * [__clone Evita que el objeto se pueda clonar]
     * @return [type] [message]
     */
    public function __clone()
    {
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
    }
}
