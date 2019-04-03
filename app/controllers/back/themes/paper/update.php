<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\configuracion as configuracion_model;
use \core\app;
use \core\cache;
use \core\functions;
use \core\view;

class update extends base
{
    protected $url         = array('update');
    protected $metadata    = array('title' => 'update', 'modulo' => 'update');
    protected $breadcrumb  = array();
    protected $url_update  = "https://update.mysitio.cl/";
    protected $dir         = '';
    protected $dir_update  = '';
    protected $archivo_log = '';
    protected $no_update   = array('app\\config\\config.json', 'app/config/config.json');
    public function __construct()
    {
        $this->dir         = app::get_dir(true);
        $this->dir_update  = $this->dir . 'update';
        $this->archivo_log = app::get_dir() . '/log.json';
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

        $mensaje_error = '';
        if (file_exists($this->dir_update)) {
            if (!is_writable($this->dir_update)) {
                $mensaje_error = 'Debes dar permisos de escritura al directorio ' . $this->dir_update;
            }
        } elseif (!is_writable($this->dir)) {
            $mensaje_error = 'Debes dar permisos de escritura en ' . $this->dir . ' o crear el directorio update/ con permisos de escritura';
        }

        $is_error = ($mensaje_error != '');

        view::set('breadcrumb', $this->breadcrumb);
        view::set('title', $this->metadata['title']);
        view::set('is_error', $is_error);
        view::set('mensaje_error', $mensaje_error);
        view::set('progreso', 0);
        view::render('update');

        $footer = new footer();
        $footer->normal();
    }

    public function vaciar_log()
    {
        echo json_encode(unlink($this->archivo_log));
    }

    private function url_get_contents($url, $useragent = 'cURL', $headers = false, $follow_redirects = true, $debug = false)
    {

        // initialise the CURL library
        $ch = curl_init();

        // specify the URL to be retrieved
        curl_setopt($ch, CURLOPT_URL, $url);

        // we want to get the contents of the URL and store it in a variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // specify the useragent: this is a required courtesy to site owners
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

        // ignore SSL errors
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // return headers as requested
        if ($headers == true) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }

        // only return headers
        if ($headers == 'headers only') {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }

        // follow redirects - note this is disabled by default in most PHP installs from 4.4.4 up
        if ($follow_redirects == true) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        // if debugging, return an array with CURL's debug info and the URL contents
        if ($debug == true) {
            $result['contents'] = curl_exec($ch);
            $result['info']     = curl_getinfo($ch);
        }

        // otherwise just return the contents as a variable
        else {
            $result = curl_exec($ch);
        }

        // free resources
        curl_close($ch);

        // send back the data
        return $result;
    }

    public function get_update()
    {
        $respuesta = array('exito' => false);
        $url       = $this->url_update;
        #$file          = file_get_contents($url);
        $file          = $this->url_get_contents($url);
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
        $url       = $this->url_update . $file;
        $path      = $this->dir_update . "/" . $file;
        if (is_writable($this->dir_update)) {
            $exito = $this->download($url, $path);
            if (!is_bool($exito)) {
                $respuesta['mensaje'] = $exito;
            } else {
                $respuesta['exito']   = $exito;
                $respuesta['archivo'] = $_POST['file'];
            }
        } else {
            $respuesta['mensaje'] = 'Debes dar permiso de escritura a ' . $this->dir_update;
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

    public function update_file()
    {
        $tiempo    = time();
        $respuesta = array('exito' => false, 'mensaje' => 'archivo no encontrado', 'errores' => array());
        $id        = 'v' . $_POST['file'] . '.zip';
        $inicio    = (isset($_POST['inicio'])) ? ((int) $_POST['inicio'] - 1) : 0;
        foreach (scandir($this->dir_update) as $key => $files) {
            if (strpos($files, $id) !== false) {
                $file = $files;
                break;
            }
        }
        if (isset($file)) {
            if (extension_loaded('zip') === true) {
                $file = $this->dir_update . '/' . $file;
                $zip  = new \ZipArchive();
                if ($zip->open($file) === true) {
                    $total = $zip->numFiles;
                    for ($i = $inicio; $i < $total; $i++) {
                        $nombre = $zip->getNameIndex($i);
                        if (!in_array($nombre, $this->no_update)) {
                            //$exito = true;
                            $exito = $zip->extractTo($this->dir, array($nombre));
                            /*if(is_writable($this->dir . "/" . $nombre)){
                            $nombre_final = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $this->dir . "/" . $nombre);
                            rename($this->dir . "/" . $nombre, $nombre_final);
                            }*/
                            if (!$exito) {
                                $respuesta['errores'][] = $nombre;
                            }
                        }
                        if ($i % 100 == 0) {
                            $log = array('mensaje' => 'Actualizando ' . functions::substring($nombre, 30) . ' (' . ($i + 1) . '/' . $total . ')', 'porcentaje' => ((($i + 1) / $total) * 90));
                            file_put_contents($this->archivo_log, functions::encode_json($log));
                        }
                        if (time() - $tiempo > 15) {
                            $respuesta['inicio'] = $i;
                            break;
                        }
                    }
                    $zip->close();
                    if (count($respuesta['errores']) == 0) {
                        $respuesta['exito'] = true;
                    } else {
                        $respuesta['mensaje'] = array('Error al abrir archivo');
                        $respuesta['mensaje'] = array_merge($respuesta['mensaje'], $respuesta['errores']);
                    }
                } else {
                    $respuesta['mensaje'] = 'Error al abrir archivo';
                }
            } else {
                $respuesta['mensaje'] = 'Debes instalar la extension ZIP';
            }
        }

        if (!isset($respuesta['inicio'])) {
            $c = new configuracion_administrador();
            $c->json_update(false);
            $c->json(false);

            $log = array('mensaje' => 'Actualización finalizada', 'porcentaje' => 100);
            file_put_contents($this->archivo_log, functions::encode_json($log));
            cache::delete_cache();
        }
        echo json_encode($respuesta);
    }

}
