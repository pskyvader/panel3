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
            if (!headers_sent()) {
                setcookie($cookie, $value, $time, $path);
            } else {
                echo '<script>document.cookie = "' . $cookie . '=' . $value . '; expires=' . date('r', $time) . '; path=' . $path . ';";</script>';
            }
        }
    }

    private static function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    //Cortar string a "length" caracteres
    public static function substring($string, $length = null, $caracteres = " ...")
    {
        if (null == $length) {
            $length = 50;
        }

        $s = substr(strip_tags($string), 0, $length);
        if (strlen(strip_tags($string)) > $length) {
            $s .= $caracteres;
        }

        return $s;
    }

    /*
    url_redirect comprueba si la url es valida y redirecciona si no lo es
     */
    public static function url_redirect($url)
    {
        $ruta    = self::generar_url($url);
        $current = self::current_url();

        if ($ruta != $current) {
            if (error_reporting()) {
                exit("<a href='" . $ruta . "'>" . $ruta . "</a>");
            }
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $ruta);
            exit;
        }
    }

    public static function get_idseccion($url)
    {
        $url = explode('-', $url, 2);
        return (int) $url[0];
    }
    public static function url_seccion(array $url_base, $seccion, $return = false, $extra_variables = false)
    {
        $url   = $url_base;
        $extra = "";
        if (isset($seccion[0])) {
            $extra .= $seccion[0];
            if (isset($seccion['url'])) {
                $extra .= "-" . $seccion['url'];
            } elseif (isset($seccion['titulo'])) {
                $extra .= "-" . self::url_amigable($seccion['titulo']);
            }
        }
        $url[] = $extra;
        if ($return) {
            return $url;
        } else {
            return self::generar_url($url, $extra_variables);
        }
    }

    public static function generar_url(array $url, $extra = null, $front_auto = true, $front = true)
    {
        $url = implode('/', $url);
        if (is_array($extra) && count($extra) > 0) {
            $url .= "?" . http_build_query($extra);
        } elseif (count($_GET) > 0) {
            if (!is_bool($extra) || $extra) {
                $url .= "?" . http_build_query($_GET);
            }
        }
        $url = str_replace("%2F", "/", $url);
        $url = (($front_auto) ? (app::get_url()) : (app::get_url($front))) . $url;

        return $url;
    }

    public static function current_url()
    {
        if (isset($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'] || 1 == $_SERVER['HTTPS']) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public static function generar_pass($length = 8)
    {
        $pass = strtoupper(substr(md5(uniqid()), -10, $length));
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
        $url = explode('-', $url);
        $url = implode('-', array_filter($url));
        $url = strtolower($url);
        return $url;
    }

    public static function test_input($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::test_input($value);
            }
        } else {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
        }
        return $data;
    }

    //agrega la fecha del archivo como variable al nombre del archivo: style.css=> style.css?time=23426421
    public static function fecha_archivo($archivo, $only_fecha = false)
    {
        $c  = (strpos($archivo, '?') === false) ? '?time=' : '&time=';
        $ac = explode("?", $archivo);
        $ac = $ac[0];
        if ($only_fecha) {
            return file_exists($ac) ? filemtime($ac) : false;
        } else {
            return file_exists($ac) ? $archivo . $c . filemtime($ac) : "";
        }
    }
    //formato de precio en pesos
    public static function formato_precio($precio)
    {
        $final = "$" . number_format($precio, 0, '', '.');
        return $final;
    }

    //fecha con formato
    public static function formato_fecha($fecha, $formato = '')
    {
        //$fecha=strtotime($fecha);
        if ('' == $formato) {
            $fecha_final = strftime('%d de %B del %Y', $fecha);
        } else {
            $fecha_final = strftime($formato, $fecha);
        }

        return $fecha_final;
    }

    /**
     * getContrastColor
     * retorna blanco o negro, dependiendo de que color de un mejor contraste
     *
     * @param  string $hexColor
     *
     * @return string
     */
    public static function getContrastColor(string $hexColor): string
    {

        //////////// hexColor RGB
        $R1 = hexdec(substr($hexColor, 1, 2));
        $G1 = hexdec(substr($hexColor, 3, 2));
        $B1 = hexdec(substr($hexColor, 5, 2));

        //////////// Black RGB
        $blackColor   = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

        //////////// Calc contrast ratio
        $L1 = 0.2126 * pow($R1 / 255, 2.2) +
        0.7152 * pow($G1 / 255, 2.2) +
        0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
        0.7152 * pow($G2BlackColor / 255, 2.2) +
        0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int) (($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int) (($L2 + 0.05) / ($L1 + 0.05));
        }

        //////////// If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000';
        } else { //////////// if not, return white color.
            return '#fff';
        }
    }

    //formato de url
    public static function ruta($texto)
    {
        $texto = trim($texto);
        $texto = trim($texto, ' ');
        $pos   = strpos($texto, 'http');
        if (false !== $pos || '#' == $texto) {
            $ruta = $texto;
        } elseif ('.' == $texto) {
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
        $url    = self::current_url();
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
        if ($pretty) {
            $json = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            $json = json_encode($array, JSON_UNESCAPED_UNICODE);
        }
        return $json;
    }

    public static function decode_json($json)
    {
        $array = json_decode(html_entity_decode($json), true);
        if (!is_array($array)) {
            $array = array();
        }
        return $array;
    }

    public static function crear_arbol($data, $idpadre = 0)
    {
        $tree = array('children' => array(), 'root' => array());
        foreach ($data as $key => $node) {
            $id = $node[0];
            /* Puede que exista el children creado si los hijos entran antes que el padre */
            $node['children']      = (isset($tree['children'][$id])) ? $tree['children'][$id]['children'] : array();
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
        $file_keys  = array_keys($file_post);

        for ($i = 0; $i < $file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $multiple ? $file_post[$key][$i] : $file_post[$key];
            }
        }

        return $file_ary;
    }

    
    public static function get_max_size()
    {
        $max_size      = -1;
        $post_max_size = self::parse_size(ini_get('post_max_size'));
        $upload_max    = self::parse_size(ini_get('upload_max_filesize'));

        if ($post_max_size > 0 && $upload_max > 0) {
            $max_size = min($post_max_size, $upload_max);
        }
        return $max_size;
    }

    public static function file_size($file_url, $only_size = false)
    {
        if (!$only_size) {
            $size = filesize($file_url);
        } else {
            $size = $file_url;
        }
        $unit       = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        $final_size = @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
        return $final_size;
    }

    public static function protection_template($folder)
    {
        if ("" != $folder && !file_exists($folder . "/index.php") && is_dir($folder) && is_writable($folder)) {
            $a = '<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: ../");
exit;?>';
            file_put_contents($folder . "/index.php", $a);
        }
    }

}
