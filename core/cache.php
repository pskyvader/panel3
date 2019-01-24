<?php
namespace core;

defined("APPPATH") or die("Access denied");

use \core\app;
use \core\functions;

class cache
{
    /**
     * @var
     */
    protected static $data             = array();
    protected static $cacheable        = true;
    protected static $cacheable_config = null;
    public static function set_cache(bool $cache)
    {
        self::$cacheable = $cache;
    }
    public static function is_cacheable()
    {
        return self::$cacheable;
    }

    public static function add_cache($str)
    {
        if (app::$_front && self::$cacheable) {
            self::$data[] = $str;
        }
    }
    public static function delete_cache()
    {
        $dir        = app::get_dir(true) . 'cache/';
        $directorio = opendir($dir); //ruta actual
        while ($archivo = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
        {
            if (!is_dir($dir . $archivo)) //verificamos si es o no un directorio
            {
                if (file_exists($dir . $archivo)) {
                    if ("index.php" != $archivo) //si el archivo tiene extension html, borrar
                    {
                        unlink($dir . $archivo);
                    }

                }
            }
        }
    }

    public static function get_cache(array $url)
    {
        $ruta    = functions::generar_url($url);
        $current = functions::current_url();
        if ($ruta != $current) {
            return "";
        }

        if (null == self::$cacheable_config) {
            $config                 = app::getConfig();
            self::$cacheable_config = (isset($config['cache']) ? $config['cache'] : true);
            if (!self::$cacheable_config) {
                self::$cacheable = false;
            }
        }

        if (app::$_front && self::$cacheable) {
            $dir  = app::get_dir(true) . 'cache/';
            $name = self::file_name($url);
            if ("" != $name && file_exists($dir . $name)) {
                return file_get_contents($dir . $name);
            } else {
                return "";
            }
        }
    }
    public static function save_cache(array $url)
    {
        $ruta    = functions::generar_url($url);
        $current = functions::current_url();
        if ($ruta == $current && app::$_front && self::$cacheable) {
            $dir = app::get_dir(true) . 'cache/';
            if (is_writable($dir)) {
                $name = self::file_name($url);
                if ('' != $name) {
                    file_put_contents($dir . $name, implode('', self::$data));
                }
            }
        }
    }

    private static function file_name(array $url)
    {
        if (!isset($url['url'])) {
            return "";
        }

        $name = str_replace('/', '-', $url['url']);
        unset($url['url']);
        $n = explode('.', $name, 2);
        if (isset($n[1])) {
            return "";
        }
        foreach ($url as $key => $u) {
            $n = "__" . $key . "-" . $u;
            $n = functions::url_amigable($n);
            $name .= $n;
        }
        $post = $_POST;
        if (isset($post['ajax'])) {
            $name .= '__ajax';
            unset($post['ajax']);
        }
        if (count($post) > 0) {
            return "";
        }

        return $name;
    }
}
