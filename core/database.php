<?php
namespace core;

defined("APPPATH") or die("Acceso denegado");

use \core\app;
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

    /**
     * [__construct]
     */
    private function __construct()
    {
        try {
            //load from config/config.ini
            $config = app::getConfig();
            $this->_dbHost = $config["host"];
            $this->_dbUser = $config["user"];
            $this->_dbPassword = $config["password"];
            $this->_dbName = $config["database"];
            self::$_prefix = $config["prefix"] . "_";
            $this->conect();
        } catch (\PDOException $e) {
            print "Error!: " . $e->getMessage();
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
    private function consulta($sql, $return)
    { //Procesar consulta, sql=consulta,return=devolver resultados o solo true o false si se ejecuto la operacion
        try {
            $query = $this->prepare($sql);
            $query->execute();
            if ($return) {
                $rows = $query->fetchAll();
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
        return $rows;
    }

    public function get_last_insert_id()
    { //ultimo elemento insertado
        return $this->_connection->lastInsertId();
    }

    public function get($table, $idname, $where, $condiciones = array(), $select = "")
    {
        if ($select == "") {
            $select = "*";
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

    public function insert($table, $idname, $insert)
    { //consulta insert
        $valor_primario = "";
        $image = array();
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
            $sql .= ($value == "true" || $value == "false") ? $value : "'" . $value . "'";
        }
        $sql .= ")";
        $row = $this->consulta($sql, false);
        if (count($image) > 0) {
            $this->process_image($image, $table, $idname, $this->get_last_insert_id());
        }
        if (count($file) > 0) {
            $this->process_file($file, $table, $idname, $this->get_last_insert_id());
        }
        return $row;
    }

    public function update($table, $idname, $set, $where)
    { //consulta update
        $set = self::process_multiple($set);
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
            $row = $this->consulta($sql, false);
            if (count($image) > 0) {
                $this->process_image($image, $table, $idname, $where[$idname]);
            }
            if (count($file) > 0) {
                $this->process_file($file, $table, $idname, $where[$idname]);
            }
            return $row;
        } else {
            echo "error cantidad de condiciones";
            return false;
        }
    }

    public function delete($table, $idname, $where)
    { //consulta delete
        $sql = "DELETE FROM " . self::$_prefix . $table;
        $sql .= " WHERE (TRUE";
        foreach ($where as $key => $value) {
            $sql .= " AND " . $key . "='" . $value . "'";
        }
        $sql .= ")";
        if (count($where) > 0) {
            $row = $this->consulta($sql, false);
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
        $sql = "ALTER TABLE " . self::$_prefix . $table;
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
        $sql = "ALTER TABLE " . self::$_prefix . $table;
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
        $sql = "CREATE TABLE " . self::$_prefix . $table . " (";
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
        $sql = "";
        foreach ($tables as $key => $table) {
            $sql .= "TRUNCATE TABLE " . self::$_prefix . $table . " ;";
        }
        $row = $this->consulta($sql, false);
        return $row;
    }

    public static function encript($password)
    {
        $salt = sha1($password);
        $p = crypt($password, $salt);
        return $salt . sha1($p);
    }

    public static function create_data($model, $data)
    {
        $data = self::process_multiple($data);
        $m = array();
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
                            if ($key == "image" || $key == "file" ) {
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
        $ids = array();
        foreach ($image as $key => $img) { //cada campo
            $row = array();
            $portada = false;
            foreach ($img as $k => $f) { //cada foto
                if (isset($f['tmp']) && $f['tmp'] != '') {
                    $f = image::move($f, $table, $key, $id);
                }
                $ids[$key][$f['id']] =  $f['url'];
                if ($f['portada'] == 'true') {
                    if ($portada) {
                        $f['portada'] = 'false';
                    } else {
                        $portada = true;
                    }
                }
                $f['parent'] = $id;
                $f['folder'] = $table;
                $row[$k] = $f;
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
                    if (!isset($value[$file['id']]) || $value[$file['id']]!=$file['url']) {
                        image::delete($table, $file, $id,$key);
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
        $ids = array();
        foreach ($file as $key => $archivo) { //cada campo
            $row = array();
            foreach ($archivo as $k => $f) { //cada archivo
                if (isset($f['tmp']) && $f['tmp'] != '') {
                    $f = file::move($f, $table, $key, $id);
                }
                $ids[$key][$f['id']] = $f['url'];
                $f['parent'] = $id;
                $f['folder'] = $table;
                $row[$k] = $f;
            }
            $data[$key] = functions::encode_json($row);
        }

        $row = $this->get($table, $idname, array($idname => $id), array('limit' => 1));
        $this->update($table, $idname, $data, array($idname => $id));
        foreach ($ids as $key => $value) {
            $files = json_decode(html_entity_decode($row[0][$key]), true);
            if (is_array($files)) {
                foreach ($files as $k => $file) {
                    if (!isset($value[$file['id']]) || $value[$file['id']]!=$file['url']) {
                        file::delete($table, $file, $id,$key);
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
            $class = __CLASS__;
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
