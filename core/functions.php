<?php
namespace core;

defined("APPPATH") or die("Acceso denegado");

use \core\app;

/**
 * @class functions
 */
class functions
{

    private function __construct()
    {

    }
    public static function set_cookie($cookie, $value, $time)
    {
        if (ini_get("session.use_cookies")) {
            $path = app::get_sub();
            setcookie($cookie, $value, $time, $path);
        }
    }

    /*
    url_redirect comprueba si la url es valida y redirecciona si no lo es
     */
    public static function url_redirect($url)
    {
        $ruta = self::generar_url($url);
        $current = self::current_url();
        $redirect = ($ruta != $current);

        if ($redirect) {
            if (error_reporting()) {
                exit("<a href='" . $ruta . "'>" . $ruta . "</a>");
            }
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $ruta);
            exit;
        }
    }

    public static function generar_url($url, $extra = null, $front_auto = true, $front = true)
    {
        $url = implode('/', $url);
        if (is_array($extra) && count($extra) > 0) {
            $url .= "?" . http_build_query($extra);
        } elseif (count($_GET) > 0) {
            if (!is_bool($extra) || $extra) {
                $url .= "?" . http_build_query($_GET);
            }
        }
        $url = (($front_auto) ? (app::get_url()) : (app::get_url($front))) . $url;

        return $url;
    }

    public static function current_url()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $http = "https://";
        } else {
            $http = "http://";
        }

        return $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public static function generar_pass()
    {
        $pass = strtoupper(substr(str_shuffle(md5(uniqid())), -10, 8));
        return $pass;
    }
    // formatea texto como url amigables
    public static function url_amigable($url)
    {
        $url = str_replace(array('á', 'à', 'â', 'ã', 'ª', 'ä'), "a", $url);
        $url = str_replace(array('Á', 'À', 'Â', 'Ã', 'Ä'), "A", $url);
        $url = str_replace(array('Í', 'Ì', 'Î', 'Ï'), "I", $url);
        $url = str_replace(array('í', 'ì', 'î', 'ï'), "i", $url);
        $url = str_replace(array('é', 'è', 'ê', 'ë'), "e", $url);
        $url = str_replace(array('É', 'È', 'Ê', 'Ë'), "E", $url);
        $url = str_replace(array('ó', 'ò', 'ô', 'õ', 'ö'), "o", $url);
        $url = str_replace(array('Ó', 'Ò', 'Ô', 'Õ', 'Ö'), "O", $url);
        $url = str_replace(array('ú', 'ù', 'û', 'ü'), "u", $url);
        $url = str_replace(array('Ú', 'Ù', 'Û', 'Ü'), "U", $url);
        $url = str_replace(array('[', '^', '´', '`', '¨', '~', ']', ' ', '/', '°', 'º'), "-", $url);
        $url = str_replace("ç", "c", $url);
        $url = str_replace("Ç", "C", $url);
        $url = str_replace("ñ", "n", $url);
        $url = str_replace("Ñ", "N", $url);
        $url = str_replace("Ý", "Y", $url);
        $url = str_replace("ý", "y", $url);
        $url = strtolower($url);
        return $url;
    }
    //agrega la fecha del archivo como variable al nombre del archivo: style.css=> style.css?time=23426421
    public static function fecha_archivo($archivo, $only_fecha = false)
    {

        $c = (strpos($archivo, '?') === false) ? '?time=' : '&time=';
        $ac = explode("?", $archivo);
        $ac = $ac[0];
        if ($only_fecha) {
            return file_exists($ac) ? filemtime($ac) : false;
        } else {
            return file_exists($ac) ? $archivo . $c . filemtime($ac) : "";
        }
    }
    //formato de url
    public static function ruta($texto)
    {
        $texto = trim($texto);
        $texto = trim($texto, ' ');
        $pos = strpos($texto, 'http');
        if ($pos !== false || $texto == '#') {
            $ruta = $texto;
        } elseif ($texto == '.') {
            $ruta = '';
        } else {
            $ruta = "http://" . $texto;
        }
        return $ruta;
    }
    //comprueba si la url actual es la misma que la url enviada
    public static function active($string)
    {
        $pagina = $_SERVER['REQUEST_URI'];
        $url = self::current_url();
        if (strpos($pagina, $string) !== false) {
            return true;
        } else if ($url == $string) {
            return true;
        } else {
            return false;
        }
    }

    public static function encode_json($array, $pretty = false)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            if ($pretty) {
                $json = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                $json = json_encode($array, JSON_UNESCAPED_UNICODE);
            }
        } else {
            array_walk_recursive($array, function (&$item, $key) {if (is_string($item)) {
                $item = mb_encode_numericentity($item, array(0x80, 0xffff, 0, 0xffff), 'UTF-8');
            }
            });
            $json = mb_decode_numericentity(json_encode($array), array(0x80, 0xffff, 0, 0xffff), 'UTF-8');
        }
        return $json;
    }

    public static function decode_json($json)
    {
        $array = json_decode(html_entity_decode($json), true);
        if(!is_array($array)) $array=array();
        return $array;
    }

    public static function crear_arbol($data, $idpadre = 0)
    {
        $tree = array('children' => array(), 'root' => array());
        foreach ($data as $key => $node) {
            $id = $node[0];
            /* Puede que exista el children creado si los hijos entran antes que el padre */
            $node['children'] = (isset($tree['children'][$id])) ? $tree['children'][$id]['children'] : array();
            $tree['children'][$id] = $node;
            if ($node['idpadre'][0] == $idpadre) {
                $tree['root'][$id] = &$tree['children'][$id];
            } else {
                $tree['children'][$node['idpadre'][0]]['children'][$id] = &$tree['children'][$id];
            }
        }
        return $tree['root'];
    }

    public static function reArrayFiles(&$file_post) // multiples archivos, transformar array $_FILES
    {
        $file_ary = array();
        $multiple = is_array($file_post['name']);

        $file_count = $multiple ? count($file_post['name']) : 1;
        $file_keys = array_keys($file_post);

        for ($i = 0; $i < $file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $multiple ? $file_post[$key][$i] : $file_post[$key];
            }
        }

        return $file_ary;
    }

}
