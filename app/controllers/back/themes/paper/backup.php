<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \core\app;
use \core\database;
use \core\functions;
use \core\view;

class backup extends base
{
    protected $url        = array('backup');
    protected $metadata   = array('title' => 'backup', 'modulo' => 'backup');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(null);
    }
    public function index()
    {
        if (!administrador_model::verificar_sesion()) {
            $this->url = array('login', 'index', 'backup');
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
        if (file_exists($dir . 'backup')) {
            if (!is_writable($dir . 'backup')) {
                $mensaje_error = 'Debes dar permisos de escritura o eliminar el archivo ' . $dir . 'backup';
            }
        } elseif (!is_writable($dir)) {
            $mensaje_error = 'Debes dar permisos de escritura en ' . $dir;
        }
        $is_error = ($mensaje_error != '');

        $row = array();

        view::set('row', $row);
        view::set('breadcrumb', $this->breadcrumb);
        view::set('title', $this->metadata['title']);
        view::set('is_error', $is_error);
        view::set('mensaje_error', $mensaje_error);
        view::render('backup');

        $footer = new footer();
        $footer->normal();
    }

    public function generar()
    {
        $respuesta = array('exito' => true, 'mensaje' => '');

        $dir = app::get_dir(true);
        if (file_exists($dir . 'backup')) {
            if (!is_writable($dir . 'backup')) {
                $respuesta['mensaje'] = 'Debes dar permisos de escritura o eliminar el archivo ' . $dir . 'backup';
                $respuesta['exito']   = false;
            }
        } elseif (!is_writable($dir)) {
            $respuesta['mensaje'] = 'Debes dar permisos de escritura en ' . $dir;
            $respuesta['exito']   = false;
        }
        if ($respuesta['exito']) {
            $respuesta = $this->get_files($dir);
        }
        echo json_encode($respuesta);
    }
    private function get_files($source)
    {
        $respuesta = array('exito' => false, 'mensaje' => 'Debes instalar la extension ZIP');
        $largo     = strlen($source);
        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip    = new \ZipArchive();
                $source = realpath($source);
                if (is_dir($source) === true) {
                    $files          = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
                    $lista_archivos = array();
                    foreach ($files as $file) {
                        $file = substr($file->getPathname(), $largo);
                        if (strpos($file, '.git') === false && strpos($file, '.zip') === false && strpos($file, '.sql') === false && $file != '.' && $file != '..') {
                            $lista_archivos[] = $file;
                        }
                    }
                    $respuesta['lista']          = $lista_archivos;
                    $dir                         = app::get_dir(true);
                    $respuesta['archivo_backup'] = $dir . 'backup/' . functions::url_amigable(app::$_title) . '-' . time() . '.zip';
                    $respuesta['exito']          = true;
                } else {
                    $respuesta['mensaje'] = 'Directorio no valido';
                }

            } else {
                $respuesta['mensaje'] = 'Directorio no valido';
            }
        }
        return $respuesta;
    }

    public function bdd()
    {
        $connection = database::instance();
        $respuesta  = $connection->backup();
        if ($respuesta['exito']) {
            $zip       = new \ZipArchive();
            if ($zip->open($_POST['archivo_backup'], \ZIPARCHIVE::CREATE) === true) {
                $zip->addFromString('bdd.sql', implode("\n", $respuesta['sql']));
                $respuesta['exito'] = $zip->close();
            }
        }
        echo json_encode($respuesta);
    }

    public function continuar()
    {
        $dir       = app::get_dir(true);
        $config    = app::getConfig();
        $respuesta = $this->zipData($dir, $_POST['archivo_backup'], $_POST['lista']);
        echo json_encode($respuesta);
    }

    private function zipData($source, $destination, $lista)
    {
        $archivo  = $destination;
        $partes[] = $archivo;
        $lista    = functions::decode_json($lista);
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $respuesta = array('exito' => false, 'mensaje' => 'Error al crear archivo');
        $zip       = new \ZipArchive();
        if ($zip->open($archivo, \ZIPARCHIVE::CREATE) === true) {
            $source = realpath($source);
            foreach ($lista as $key => $file) {
                $total_memory = memory_get_usage(true);
                $total_memory += (is_file($source . '/' . $file) === true) ? filesize($source . '/' . $file) : 0;
                if ($total_memory > 120000000) {
                    break;
                }
                if (is_dir($source . '/' . $file) === true) {
                    $zip->addEmptyDir($file . '/');
                } else if (is_file($source . '/' . $file) === true) {
                    $zip->addFromString($file, file_get_contents($source . '/' . $file));
                }
                unset($lista[$key]);
            }
            $respuesta['exito']          = $zip->close();
            $respuesta['lista']          = array_values($lista);
            $respuesta['archivo_backup'] = $destination;
            $respuesta['archivo_actual'] = $file;
            $respuesta['partes']         = $partes;
        }
        return $respuesta;
    }

}
