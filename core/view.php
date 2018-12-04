<?php
namespace core;

defined("APPPATH") or die("Access denied");

use \core\app;
use \core\minify as mini_files;

class view
{
    /**
     * @var
     */
    protected static $data = array();

    /**
     * @var
     */
    private static $theme;

    /**
     * @var
     */
    private static $resources = "";

    /**
     * @var
     */
    private static $content_url = array();
    /**
     * @var
     */
    const VIEWS_PATH = "app/views/";

    /**
     * @var
     */
    const EXTENSION_TEMPLATES = "html";

    /**
     * [render views with data]
     * @param  [String]  [template name]
     * @return [html]    [render html]
     */
    public static function render($template, $minify = true, $return = false)
    {
        $theme        = self::get_theme();
        $template_url = $theme . $template . "." . self::EXTENSION_TEMPLATES;
        if (!file_exists($template_url)) {
            throw new \Exception("Error: El archivo " . $template_url . " no existe", 1);
        }

        if (isset(self::$content_url[$template_url])) {
            $content = self::$content_url[$template_url];
        } else {
            $content                          = file_get_contents($template_url);
            self::$content_url[$template_url] = $content;
        }
        $str = self::render_template(self::$data, $content);
        if ($minify) {
            $str = minify::minify_html($str);
        }

        self::reset();
        if ($return) {
            return $str;
        } else {
            /*ob_start();
            echo $str;
            $str = ob_get_contents();
            ob_end_clean();*/
            echo $str;
        }
    }

    public static function render_template($data, $content)
    {
        $data2 = array();
        foreach ($data as $key => $d) {
            if (is_array($d)) { //arrray de elementos foreach en vista
                $array_open  = "{foreach " . $key . "}";
                $array_close = "{/foreach " . $key . "}";

                $pos_open  = strpos($content, $array_open);
                $pos_close = strpos($content, $array_close);

                if ($pos_open !== false && $pos_close !== false) { //existe el codigo foreach en vista?
                    $subcontent1 = substr($content, $pos_open, ($pos_close - $pos_open));
                    $subcontent  = str_replace($array_open, "", $subcontent1);
                    $sub         = "";
                    foreach ($d as $k => $s) { //rellenar recursivamente los elementos dentro del foreach
                        $sub .= self::render_template($s, $subcontent);
                    }
                    $content = str_replace($subcontent1, $sub, $content);
                    $content = str_replace($array_close, "", $content);
                } elseif (error_reporting()) {
                    throw new \Exception("Array no encontrado {$array_open}", 1);
                }

            } else { //si no es array, se procesa despues para evitar conflictos de nombres repetidos dentro y fuera del bloque foreach en template
                $data2[$key] = $d;
            }
        }
        foreach ($data2 as $key => $d) {
            $res     = self::template_if($content, $key, $d);
            $content = $res[0];
            if (!$res[1]) {
                $content = str_replace('{' . $key . '}', $d, $content);
            }
        }
        return $content;
    }
    private static function template_if($content, $key, $d)
    {
        $is_if    = false;
        $if_open  = "{if " . $key . "}";
        $if_else  = "{else " . $key . "}";
        $if_close = "{/if " . $key . "}";

        $pos_open  = strpos($content, $if_open);
        $pos_else  = strpos($content, $if_else);
        $pos_close = strpos($content, $if_close);

        if ($pos_open !== false && $pos_close !== false) { //existe el codigo IF en vista?
            $is_if = true;
            if ($d) { //valor if true
                if ($pos_else !== false && $pos_else < $pos_close) {
                    $subcontent = substr($content, $pos_else, ($pos_close - $pos_else));
                    $content    = implode("", explode($subcontent, $content, 2));
                }
                $content = implode("", explode($if_open, $content, 2));
                $content = implode("", explode($if_close, $content, 2));
            } elseif ($pos_else !== false && $pos_else < $pos_close) { //valor if false y existe else
                $subcontent = substr($content, $pos_open, ($pos_else - $pos_open));
                $content    = implode("", explode($subcontent, $content, 2));
                $content    = implode("", explode($if_else, $content, 2));
                $content    = implode("", explode($if_close, $content, 2));
            } else { //valor if false y no existe else
                $subcontent = substr($content, $pos_open, ($pos_close - $pos_open));
                $content    = implode("", explode($subcontent, $content, 2));
                $content    = implode("", explode($if_close, $content, 2));
            }
            $res     = self::template_if($content, $key, $d);
            $content = $res[0];
        }
        return array($content, $is_if);
    }

    /**
     * [set Set Data form views]
     * @param [string] $name  [key]
     * @param [mixed] $value [value]
     */
    public static function set($name, $value)
    {
        self::$data[$name] = $value;
    }
    public static function set_array($data)
    {
        self::$data = $data;
    }
    public static function reset()
    {
        self::$data = array();
    }

    public static function js($combine = true, $array_only = false)
    {
        if (isset($_POST['ajax'])) {
            return;
        }
        $theme = self::get_theme();
        if (self::$resources == '') {
            $resources       = file_get_contents($theme . 'resources.json');
            self::$resources = json_decode($resources, true);
        }
        $js            = array();
        $locales       = array();
        $no_combinados = array();
        $nuevo         = 0;
        foreach (self::$resources['js'] as $key => $j) {
            if ($j['local']) {
                $j['url'] = $theme . $j['url'];
                if (file_exists($j['url'])) {
                    if ($combine && $j['combine'] && !$j['defer']) {
                        $fecha = functions::fecha_archivo($j['url'], true);
                        if ($fecha > $nuevo) {
                            $nuevo = $fecha;
                        }
                        $locales[] = $j;
                    } else {
                        $j['url']        = app::$_path . functions::fecha_archivo($j['url']);
                        $j['defer']      = ($j['defer']) ? 'async defer' : '';
                        $no_combinados[] = $j;
                    }
                } else {
                    if (error_reporting()) {
                        exit("Recurso no existe:" . $j['url']);
                    }
                }
            } else {
                $j['url']   = functions::ruta($j['url']);
                $j['defer'] = ($j['defer']) ? 'async defer' : '';
                $js[]       = $j;
            }
        }
        if ($combine && count($locales) > 0) {
            $dir  = app::get_dir();
            $file = 'resources-' . $nuevo . '-' . count($locales) . '.js';
            if (file_exists($dir . '/' . $file)) {
                if (isset($_COOKIE['loaded_js']) && $_COOKIE['loaded_js']) {
                    $defer = '';
                } else {
                    functions::set_cookie('loaded_js', true, time() + (31536000));
                    $defer = 'async defer';
                }
                $locales = array(array('url' => app::$_path . $file, 'defer' => $defer));
            } else {
                if (isset($_COOKIE['loaded_js'])) {
                    functions::set_cookie('loaded_js', false, time() + (31536000));
                }
                if (is_writable($dir)) {
                    $minifier = null;
                    foreach ($locales as $key => $l) {
                        if ($minifier == null) {
                            $minifier = new mini_files\JS($l['url']);
                        } else {
                            $minifier->add($l['url']);
                        }
                    }
                    array_map('unlink', glob($dir . "/*.js"));
                    $minify = $minifier->minify($dir . '/' . $file);

                    $locales = array(array('url' => app::$_path . $file, 'defer' => 'async defer'));
                } else {
                    foreach ($locales as $key => $l) {
                        $locales[$key]['url'] = app::$_path . functions::fecha_archivo($l['url']);
                    }
                }
            }
        }

        $js = array_merge($no_combinados, $locales, $js);
        if ($array_only) {
            return array($js, $nuevo);
        } else {
            self::set('css', array());
            self::set('js', $js);
            self::set('is_css', false);
            self::render('resources');
        }

    }

    public static function css($return = false, $combine = true, $array_only = false)
    {
        if (isset($_POST['ajax'])) {
            return;
        }
        $theme = self::get_theme();
        if (self::$resources == '') {
            $resources       = file_get_contents($theme . 'resources.json');
            self::$resources = json_decode($resources, true);
        }
        $css           = array();
        $locales       = array();
        $no_combinados = array();
        $nuevo         = 0;
        foreach (self::$resources['css'] as $key => $c) {
            $c['is_content'] = false;
            if ($c['local']) {
                $c['url'] = $theme . $c['url'];
                if (file_exists($c['url'])) {
                    if ($combine && $c['combine']) {
                        $fecha = functions::fecha_archivo($c['url'], true);
                        if ($fecha > $nuevo) {
                            $nuevo = $fecha;
                        }
                        $locales[] = $c;
                    } else {
                        if (filesize($c['url']) < 2000) {
                            $minifier = new mini_files\CSS($c['url']);
                            //$c['content_css'] = file_get_contents($c['url']);
                            $c['content_css'] = $minifier->minify();
                            $c['is_content']  = true;
                        } else {
                            $c['url'] = app::$_path . functions::fecha_archivo($c['url']);
                        }
                        $no_combinados[] = $c;
                    }
                } else {
                    if (error_reporting()) {
                        exit("Recurso no existe:" . $c['url']);
                    }
                }
            } else {
                $c['url'] = functions::ruta($c['url']);
                $css[]    = $c;
            }
        }

        if ($combine && count($locales) > 0) {
            $dir  = app::get_dir();
            $file = 'resources-' . $nuevo . '-' . count($locales) . '.css';
            if (file_exists($dir . '/' . $file)) {
                if (isset($_COOKIE['loaded_css']) && $_COOKIE['loaded_css']) {
                    $defer = false;
                } else {
                    functions::set_cookie('loaded_css', true, time() + (31536000));
                    $defer = true;
                }
                $locales = array(array('url' => app::$_path . $file, 'media' => 'all', 'defer' => $defer, 'is_content' => false));
            } else {
                if (isset($_COOKIE['loaded_css'])) {
                    functions::set_cookie('loaded_css', false, time() + (31536000));
                }
                if (is_writable($dir)) {
                    $minifier = null;
                    foreach ($locales as $key => $l) {
                        if ($minifier == null) {
                            $minifier = new mini_files\CSS($l['url']);
                        } else {
                            $minifier->add($l['url']);
                        }
                    }
                    array_map('unlink', glob($dir . "/*.css"));
                    $minify  = $minifier->minify($dir . '/' . $file);
                    $locales = array(array('url' => app::$_path . $file, 'media' => 'all', 'defer' => true, 'is_content' => false));
                } else {
                    foreach ($locales as $key => $l) {
                        $locales[$key]['url'] = app::$_path . functions::fecha_archivo($l['url']);
                    }
                }
            }
        }
        $css = array_merge($no_combinados, $locales, $css);

        if ($array_only) {
            return array($css, $nuevo);
        } else {
            self::set('js', array());
            self::set('is_css', true);
            self::set('css', $css);

            if ($return) {
                $theme        = self::get_theme();
                $template_url = $theme . 'resources' . "." . self::EXTENSION_TEMPLATES;
                $content      = file_get_contents($template_url);
                return self::render_template(self::$data, $content);
            } else {
                self::render('resources');
            }
        }

    }

    public static function get_theme()
    {
        if (self::$theme == '') {
            $config = app::getConfig();
            if (!app::$_front) {
                self::$theme = '../' . self::VIEWS_PATH . 'back/themes/' . $config['theme_back'] . '/';
            } else {
                self::$theme = self::VIEWS_PATH . 'front/themes/' . $config['theme'] . '/';
            }
        }
        return self::$theme;
    }

}
