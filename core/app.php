<?php
namespace core;

defined("APPPATH") or die("Acceso denegado");

use \core\cache;
use \app\models\seo as seo_model;

class app
{
    private $_controller    = "";
    private $_method        = "index";
    private $_params        = array();
    public static $_title   = "";
    public static $prefix_site   = "";
    public static $_path    = "";
    public static $_front   = true;
    private static $_config = array();
    private static $_url    = array();
    private static $current_url    = "";
    const NAMESPACE_FRONT   = "app\controllers\\front\\themes\\";
    const NAMESPACE_BACK    = "app\controllers\\back\\themes\\";
    const CONTROLLERS_PATH  = "controllers/";
    const FRONT_PATH        = "front/themes/";
    const BACK_PATH         = "back/themes/";

    /**
     * [__construct description]
     */
    public function __construct($front)
    {
        $ds = DIRECTORY_SEPARATOR;
        if ($ds != "/") {
            $ds = "\\";
        }

        session_start();
        $config       = self::getConfig();
        self::$_title = $config['title'];
        self::$prefix_site = functions::url_amigable(self::$_title);
        self::$_front = $front;

        $site          = str_replace("www.", "", $_SERVER['HTTP_HOST']);
        $subdirectorio = $config['dir'];
        $https         = ($config['https']) ? "https://" : "http://";
        $www           = ($config['www']) ? "www." : "";

        self::$_path = $https . $www . $site . "/";
        if ($subdirectorio != '') {
            self::$_path .= $subdirectorio . "/";
            $subdirectorio = "/" . $subdirectorio . "/";
        } else {
            $subdirectorio = "/";
        }

        self::$_url['base']  = self::$_path;
        self::$_url['admin'] = self::$_path . $config['admin'] . '/';

        self::$_url['base_dir']  = PROJECTPATH . $ds;
        self::$_url['admin_dir'] = PROJECTPATH . $ds . $config['admin'] . $ds;

        self::$_url['base_sub']  = $subdirectorio;
        self::$_url['admin_sub'] = $subdirectorio . $config['admin'] . '/';

        $path = APPPATH . '/' . self::CONTROLLERS_PATH;
        if (self::$_front) {
            $path .= self::FRONT_PATH . $config['theme'] . '/';
            $namespace = self::NAMESPACE_FRONT . $config['theme'] . '\\';
        } else {
            self::$_path = self::$_url['admin'];
            $path .= self::BACK_PATH . $config['theme_back'] . '/';
            $namespace = self::NAMESPACE_BACK . $config['theme_back'] . '\\';
        }
        //obtenemos la url parseada
        $url =$this->parseUrl();
        $cache=cache::get_cache(self::$current_url);
        if($cache!=''){
            echo $cache;
            exit;
        }

        //comprobamos que exista el archivo en el directorio controllers
        if (file_exists($path . ($url[0]) . ".php")) {
            //nombre del archivo a llamar
            $this->_controller = ($url[0]);
            //eliminamos el controlador de url, así sólo nos quedaran los parámetros del método
            unset($url[0]);
        } else {
            $fullClass         = $namespace . 'error';
            $this->_controller = new $fullClass;
            return;
        }

        //obtenemos la clase con su espacio de nombres
        $fullClass = $namespace . $this->_controller;

        //asociamos la instancia a $this->_controller
        $this->_controller = new $fullClass;

        //si existe el segundo segmento comprobamos que el método exista en esa clase
        if (isset($url[1])) {
            //aquí tenemos el método
            $this->_method = $url[1];
            if (method_exists($this->_controller, $url[1])) {
                //eliminamos el método de url, así sólo nos quedaran los parámetros del método
                unset($url[1]);
            } else {
                throw new \Exception("Error de metodo {$this->_method}", 1);
            }
        }
        //asociamos el resto de segmentos a $this->_params para pasarlos al método llamado, por defecto será un array vacío
        $this->_params = $url ? array(array_merge($url)) : array();
    }

    /**
     * [parseUrl Parseamos la url en trozos]
     * @return [type] [description]
     */
    private function parseUrl()
    {
        if (isset($_GET["url"])) {
            $url = explode("/", filter_var(rtrim($_GET["url"], "/"), FILTER_SANITIZE_URL));
            if ($url[0] == 'manifest.js') {
                $url[0] = 'manifest';
            } elseif ($url[0] == 'sw.js') {
                $url[0] = 'sw';
            } elseif (self::$_front) {
                $seo = seo_model::getAll(array('url' => $url[0]), array('limit' => 1));
                if (count($seo) == 1) {
                    $url[0]            = $seo[0]['modulo_front'];
                    $_REQUEST['idseo'] = $seo[0][0];
                }
            }
            
            self::$current_url= $_GET;
            unset($_GET["url"]);
            return $url;
        } else {
            $url=array('');
            $seo = seo_model::getById(1);
            if (count($seo) > 0) {
                $url[0]            = $seo['modulo_front'];
                $_REQUEST['idseo'] = $seo[0];
                self::$current_url= array('url'=>$url[0]);
            }
            return $url;
        }
    }

    /**
     * [render  lanzamos el controlador/método que se ha llamado con los parámetros] */
    public function render()
    {
        $config = self::getConfig();
        if ($config['debug']) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
        setlocale(LC_ALL, 'spanish');
        setlocale(LC_ALL, "es_ES.UTF-8");
        if (!ini_get('date.timezone')) {date_default_timezone_set('America/Santiago');}
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        } else {
            @ini_set('expose_php', 'off');
        }
        call_user_func_array([$this->_controller, $this->_method], $this->_params);
        cache::save_cache(self::$current_url);
    }

    /**
     * [getConfig Obtenemos la configuración de la app]
     * @return [Array] [Array con la config]
     */
    public static function getConfig()
    {
        if (count(self::$_config) == 0) {
            //self::$_config = parse_ini_file(APPPATH . '/config/config.ini');
            self::$_config = functions::decode_json(file_get_contents(APPPATH . '/config/config.json'));
        }
        return self::$_config;
    }

    public static function get_dir($front = false)
    {
        if (self::$_front || $front) {
            return self::$_url['base_dir'];
        } else {
            return self::$_url['admin_dir'];

        }
    }
    public static function get_url($front = false)
    {
        if (self::$_front || $front) {
            return self::$_url['base'];
        } else {
            return self::$_url['admin'];
        }
    }
    public static function get_sub($front = false)
    {
        if (self::$_front || $front) {
            return self::$_url['base_sub'];
        } else {
            return self::$_url['admin_sub'];
        }
    }

    /**
     * [getController Devolvemos el controlador actual]
     * @return [type] [String]
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * [getMethod Devolvemos el método actual]
     * @return [type] [String]
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * [getParams description]
     * @return [type] [Array]
     */
    public function getParams()
    {
        return $this->_params;
    }
}
