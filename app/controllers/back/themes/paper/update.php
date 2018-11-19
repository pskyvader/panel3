<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\configuracion as configuracion_model;
use \core\app;
use \core\functions;
use \core\view;

class update extends base
{
    protected $url        = array('update');
    protected $metadata   = array('title' => 'update', 'modulo' => 'update');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(null);
    }
    public function index()
    {
        if (!administrador_model::verificar_sesion()) {
            $this->url = array('login', 'index', 'update');
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        $aside = new aside();
        $aside->normal();

        $dir           = app::get_dir(true);
        $mensaje_error = '';
        if (file_exists($dir . 'update/')) {
            if (!is_writable($dir . 'update/')) {
                $mensaje_error = 'Debes dar permisos de escritura al directorio ' . $dir . 'update/';
            }
        } elseif (!is_writable($dir)) {
            $mensaje_error = 'Debes dar permisos de escritura en ' . $dir . ' o crear el directorio update/ con permisos de escritura';
        }

        $is_error = ($mensaje_error != '');

        view::set('breadcrumb', $this->breadcrumb);
        view::set('title', $this->metadata['title']);
        view::set('is_error', $is_error);
        view::set('progreso', 0);
        view::render('update');

        $footer = new footer();
        $footer->normal();
    }
    public function get_update()
    {
        $respuesta     = array('exito' => false);
        $url           = "http://update.mysitio.cl/";
        $file          = file_get_contents($url);
        $file          = functions::decode_json($file);
        $version_mayor = array('version' => '0.0.0');

        foreach ($file as $key => $f) {
            if (version_compare($f['version'], $version_mayor['version'], '>')) {
                unset($f['archivo']);
                $version_mayor = $f;
            }
        }

        $version = configuracion_model::getByVariable('version');
        if (is_bool($version) || version_compare($version_mayor['version'], $version, '>')) {
            $respuesta['version'] = $version_mayor;
            $respuesta['exito']   = true;
        } else {
            $respuesta['mensaje'] = 'No hay nuevas actualizaciones';
        }
        echo json_encode($respuesta);
        exit();
    }
    public function get_file()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $file      = 'v' . $_POST['file'] . '.zip';
        $url       = "http://update.mysitio.cl/" . $file;
        $path      = app::get_dir(true) . 'update/' . $file;
        if (is_writable(app::get_dir(true) . 'update/')) {
            $exito = $this->download($url, $path);
            if (!is_bool($exito)) {
                $respuesta['mensaje'] = $exito;
            } else {
                $respuesta['exito'] = $exito;
            }
        } else {
            $respuesta['mensaje'] = 'Debes dar permiso de escritura a ' . $path;
        }

        echo json_encode($respuesta);
        exit;
    }
    private function download($url, $path)
    {
        $fp = fopen($path, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($statusCode == 200) {
            return true;
        } else {
            return "Status Code: " . $statusCode;
        }
    }

}
