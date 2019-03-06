<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\configuracion as configuracion_model;
use \core\app;
use \core\database;
use \core\functions;
use \core\view;

class backup
{
    protected $url         = array('backup');
    protected $metadata    = array('title' => 'backup', 'modulo' => 'backup');
    protected $breadcrumb  = array();
    protected $dir         = '';
    protected $dir_backup  = '';
    protected $archivo_log = '';
    protected $no_restore   = array('backup/');
    public function __construct()
    {
        $this->dir         = app::get_dir(true);
        $this->dir_backup  = $this->dir . 'backup';
        $this->archivo_log = app::get_dir() . '/log.json';
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

        $mensaje_error = '';
        if (file_exists($this->dir_backup)) {
            if (!is_writable($this->dir_backup)) {
                $mensaje_error = 'Debes dar permisos de escritura o eliminar el archivo ' . $this->dir_backup;
            }
        } elseif (!is_writable($this->dir)) {
            $mensaje_error = 'Debes dar permisos de escritura en ' . $this->dir;
        }
        $is_error = ($mensaje_error != '');

        $is_mensaje = false;

        $mensaje      = "Tiempo promedio de respaldo: ";
        $tiempo_lento = configuracion_model::getByVariable('tiempo_backup_lento');
        if (is_bool($tiempo_lento)) {
            $tiempo_lento = 0;
        } else {
            $tiempo_lento = (int) $tiempo_lento;
            $is_mensaje   = true;
            $mensaje .= $tiempo_lento . " segundos (servidor lento)";
        }
        $tiempo_rapido = configuracion_model::getByVariable('tiempo_backup_rapido');
        if (is_bool($tiempo_rapido)) {
            $tiempo_rapido = 0;
        } else {
            $tiempo_rapido = (int) $tiempo_rapido;
            $is_mensaje    = true;
            if ($tiempo_lento > 0) {
                $mensaje .= ", ";
            }
            $mensaje .= $tiempo_rapido . " segundos (servidor rápido)";
        }

        $row   = array();
        $files = array_filter(scandir($this->dir_backup), function ($item) {
            if (is_file($this->dir_backup . '/' . $item)) {
                $extension = explode('.', $item);
                $extension = array_pop($extension);
                if ($extension == 'zip') {
                    return true;
                }
            }
            return false;
        });
        $url = app::get_url(true) . 'backup/';

        foreach ($files as $key => $f) {
            $extension = explode('.', $f);
            array_pop($extension);
            $fecha       = explode('-', implode('.', $extension));
            $fecha       = array_pop($fecha);
            $row[$fecha] = array(
                'even'  => ($key % 2 == 0),
                'id'    => $fecha,
                'fecha' => functions::formato_fecha($fecha),
                'size'  => functions::file_size($this->dir_backup . '/' . $f),
                'url'   => $url . $f,
            );
        }
        $row = array_reverse($row);

        view::set('row', $row);
        view::set('breadcrumb', $this->breadcrumb);
        view::set('title', $this->metadata['title']);
        view::set('is_error', $is_error);
        view::set('mensaje_error', $mensaje_error);
        view::set('is_mensaje', $is_mensaje);
        view::set('mensaje', $mensaje);
        view::set('tiempo_lento', $tiempo_lento);
        view::set('tiempo_rapido', $tiempo_rapido);
        view::render('backup');

        $footer = new footer();
        $footer->normal();
    }

    public function restaurar()
    {
        $tiempo    = time();
        $respuesta = array('exito' => false, 'mensaje' => 'archivo no encontrado', 'errores' => array());
        $id        = $_POST['id'];
        $inicio    = (isset($_POST['inicio'])) ? ((int) $_POST['inicio'] - 1) : 0;
        foreach (scandir($this->dir_backup) as $key => $files) {
            if (strpos($files, $id) !== false) {
                $file = $files;
                break;
            }
        }
        if (isset($file)) {
            if (extension_loaded('zip') === true) {
                $file = $this->dir_backup . '/' . $file;
                $zip  = new \ZipArchive();
                if ($zip->open($file) === true) {
                    $total = $zip->numFiles;
                    for ($i = $inicio; $i < $total; $i++) {
                        $nombre = $zip->getNameIndex($i);
                        if (!in_array($nombre, $this->no_restore)) {
                            //$exito  = true;
                            $exito = $zip->extractTo($this->dir, array($nombre));
                            /*if(is_writable($this->dir . "/" . $nombre)){
                                $nombre_final = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $this->dir . "/" . $nombre);
                                rename($this->dir . "/" . $nombre, $nombre_final);
                            }*/
                            if (!$exito) {
                                $respuesta['errores'][] = $nombre;
                            }
                        }
                        $respuesta['errores'][] = $nombre;
                        
                        if ($i % 100 == 0) {
                            $log = array('mensaje' => 'Restaurando ' . functions::substring($nombre, 30) . ' (' . ($i + 1) . '/' . $total . ')', 'porcentaje' => ((($i + 1) / $total) * 90));
                            file_put_contents($this->archivo_log, functions::encode_json($log));
                        }
                        if (time() - $tiempo > 15) {
                            $respuesta['inicio'] = $i;
                            break;
                        }
                    }
                    $zip->close();
                    if (!isset($respuesta['inicio'])) {
                        if (file_exists($this->dir . '/bdd.sql')) {
                            $log = array('mensaje' => 'Restaurando Base de datos', 'porcentaje' => 95);
                            file_put_contents($this->archivo_log, functions::encode_json($log));
                            $connection = database::instance();
                            $exito      = $connection->restore_backup($this->dir . '/bdd.sql');
                            if (!$exito) {
                                $respuesta['errores'][] = $exito;
                            }
                        } else {
                            $respuesta['mensaje']   = 'No existe base de datos';
                            $respuesta['errores'][] = 'bdd.sql';
                        }
                    }
                    $respuesta['exito'] = true;
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

            $log = array('mensaje' => 'Restauracion finalizada', 'porcentaje' => 100);
            file_put_contents($this->archivo_log, functions::encode_json($log));
        }
        echo json_encode($respuesta);
    }

    public function eliminar()
    {
        $campos    = $_POST['campos'];
        $respuesta = array('exito' => false, 'mensaje' => '');
        $id        = $campos['id'];
        $files     = array_filter(scandir($this->dir_backup), function ($item) use ($id) {
            if (strpos($item, $id) !== false) {
                return true;
            }
            return false;
        });
        $file = array_pop($files);
        if (!is_writable($this->dir_backup . '/' . $file)) {
            $respuesta['mensaje'] = 'Debes dar permisos de escritura o eliminar el archivo manualmente';
        } else {
            unlink($this->dir_backup . '/' . $file);
            $respuesta['exito']   = true;
            $respuesta['mensaje'] = "Eliminado correctamente.";
        }
        echo json_encode($respuesta);
    }
    public function vaciar_log()
    {
        echo json_encode(unlink($this->archivo_log));
    }

    public function actualizar_tiempo()
    {
        $respuesta = array('exito' => false);
        $campos    = $_POST;
        if (isset($campos['tiempo']) && isset($campos['tipo_backup'])) {
            $cantidad = configuracion_model::getByVariable('cantidad_backup_' . $campos['tipo_backup']);
            if (is_bool($cantidad)) {
                $cantidad = 0;
            }

            $tiempo = configuracion_model::getByVariable('tiempo_backup_' . $campos['tipo_backup']);
            if (is_bool($tiempo)) {
                $tiempo = 0;
            }

            $tiempo = ($tiempo * $cantidad) + $campos['tiempo'];
            $cantidad++;
            $tiempo = $tiempo / $cantidad;
            configuracion_model::setByVariable('cantidad_backup_' . $campos['tipo_backup'], $cantidad);
            configuracion_model::setByVariable('tiempo_backup_' . $campos['tipo_backup'], $tiempo);
            $respuesta['exito']   = true;
            $respuesta['mensaje'] = 'tiempo: ' . $tiempo . ', cantidad: ' . $cantidad;
        }
        echo json_encode($respuesta);
    }

    //Elimina archivos que no se lograron completar
    public function eliminar_error()
    {
        $respuesta = array('exito' => true);
        $files     = array_filter(scandir($this->dir_backup), function ($item) {
            if (is_file($this->dir_backup . '/' . $item)) {
                $extension = explode('.', $item);
                $extension = array_pop($extension);
                if ($extension != 'zip' && $extension != 'php') {
                    return true;
                }
            }
            return false;
        });
        $url = app::get_dir(true) . 'backup/';

        foreach ($files as $key => $f) {
            if (!unlink($url . $f)) {
                $respuesta['exito']   = false;
                $respuesta['mensaje'] = $f;
            }
        }
        echo json_encode($respuesta);
    }

    public function generar()
    {
        $c = new configuracion_administrador();
        $c->json(false);
        ini_set('memory_limit', '-1');
        $respuesta = array('exito' => true, 'mensaje' => '');

        if (file_exists($this->dir_backup)) {
            if (!is_writable($this->dir_backup)) {
                $respuesta['mensaje'] = 'Debes dar permisos de escritura o eliminar el archivo ' . $this->dir_backup;
                $respuesta['exito']   = false;
            }
        } elseif (!is_writable($this->dir)) {
            $respuesta['mensaje'] = 'Debes dar permisos de escritura en ' . $this->dir;
            $respuesta['exito']   = false;
        }
        if ($respuesta['exito']) {
            $respuesta = $this->get_files($this->dir);
        }
        echo json_encode($respuesta);
    }

    public function generar_backup($log = true)
    {
        $c = new configuracion_administrador();
        $c->json(false);
        ini_set('memory_limit', '-1');
        $respuesta = array('exito' => true, 'mensaje' => '');

        if (file_exists($this->dir_backup)) {
            if (!is_writable($this->dir_backup)) {
                $respuesta['mensaje'] = 'Debes dar permisos de escritura o eliminar el archivo ' . $this->dir_backup;
                $respuesta['exito']   = false;
            }
        } elseif (!is_writable($this->dir)) {
            $respuesta['mensaje'] = 'Debes dar permisos de escritura en ' . $this->dir;
            $respuesta['exito']   = false;
        }
        if ($respuesta['exito']) {
            $respuesta = $this->get_files($this->dir, $log);
        }

        if ($respuesta['exito']) {
            $total = count($respuesta['lista']);
            do {
                $respuesta = $this->zipData($this->dir, $respuesta['archivo_backup'], $respuesta['lista'], $total, $log);
            } while ((count($respuesta['lista']) > 0) && $respuesta['exito']);
        }

        if ($respuesta['exito']) {
            if ($log) {
                file_put_contents($this->archivo_log, functions::encode_json(array('mensaje' => 'Respaldando Base de datos ', 'porcentaje' => 90)));
            }
            $respuesta = $this->bdd(false, $respuesta['archivo_backup']);
        }
        if ($respuesta['exito']) {
            if ($log) {
                file_put_contents($this->archivo_log, functions::encode_json(array('mensaje' => 'Respaldo finalizado', 'porcentaje' => 100)));
            }
        }
        if ($log) {
            echo json_encode($respuesta);
        } else {
            return $respuesta;
        }
    }
    private function get_files($source, $log = true)
    {
        $respuesta = array('exito' => false, 'mensaje' => 'Debes instalar la extension ZIP');
        $largo     = strlen($source);
        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $source = realpath($source);
                if (is_dir($source) === true) {
                    $files          = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
                    $lista_archivos = array();
                    $count          = 0;
                    foreach ($files as $file) {
                        $file = substr($file->getPathname(), $largo);
                        if (strpos($file, '.git') === false && strpos($file, '.zip') === false && strpos($file, '.sql') === false && $file != '.' && $file != '..' && substr($file, -1) != '.' && substr($file, -2) != '..') {
                            $count++;
                            $file = str_replace(array("/", "\\"), "/", $file);
                            $lista_archivos[] = $file;
                            if ($log && $count % 1000 == 0) {
                                file_put_contents($this->archivo_log, functions::encode_json(array('mensaje' => 'Recuperando archivo ' . $file, 'porcentaje' => 10)));
                            }
                        }
                    }
                    $respuesta['lista']          = $lista_archivos;
                    $respuesta['archivo_backup'] = $this->dir_backup . '/' . app::$prefix_site . '-' . time() . '.zip';
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

    public function bdd($log = true, $archivo_backup = '')
    {
        if ($archivo_backup == '') {
            $archivo_backup = $_POST['archivo_backup'];
        }

        $connection = database::instance();
        $respuesta  = $connection->backup();
        if ($respuesta['exito']) {
            $zip = new \ZipArchive();
            if ($zip->open($archivo_backup, \ZIPARCHIVE::CREATE) === true) {
                $zip->addFromString('bdd.sql', implode("\n", $respuesta['sql']));
                $respuesta['exito'] = $zip->close();
            }
        }
        if ($log) {
            echo json_encode($respuesta);
        } else {
            return $respuesta;
        }
    }

    public function continuar()
    {
        $config    = app::getConfig();
        $respuesta = $this->zipData($this->dir, $_POST['archivo_backup'], functions::decode_json($_POST['lista']), $_POST['total']);
        echo json_encode($respuesta);
    }

    private function zipData($source, $destination, $lista, $total = 1, $log = true)
    {
        ini_set('max_execution_time', '-1');
        $tiempo   = 0;
        $archivo  = $destination;
        $partes[] = $archivo;

        $memory_limit = ini_get('memory_limit');

        if ($memory_limit != '-1') {
            if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
                if ($matches[2] == 'G') {
                    $memory_limit = $matches[1] * 1024 * 1024 * 1024; // nnnM -> nnn MB
                } else if ($matches[2] == 'M') {
                    $memory_limit = $matches[1] * 1024 * 1024; // nnnK -> nnn KB
                } else if ($matches[2] == 'K') {
                    $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
                }
            }
            $memory_limit = (int) ($memory_limit) / 1.5;
        }

        $respuesta = array('exito' => false, 'mensaje' => 'Error al crear archivo');
        $zip       = new \ZipArchive();
        if ($zip->open($archivo, \ZIPARCHIVE::CREATE) === true) {
            $source = realpath($source);
            $count  = 0;
            foreach ($lista as $key => $file) {
                $count++;
                if ($memory_limit != '-1') {
                    $total_memory = memory_get_usage(true);
                    $total_memory += (is_file($source . '/' . $file) === true) ? filesize($source . '/' . $file) : 0;
                    if ($total_memory > $memory_limit) {
                        break;
                    }
                }

                if (is_dir($source . '/' . $file) === true) {
                    $zip->addEmptyDir($file . '/');
                } else if (is_file($source . '/' . $file) === true) {
                    $zip->addFromString($file, file_get_contents($source . '/' . $file));
                }

                unset($lista[$key]);
                if ($log && (time() - $tiempo > 5 || $count % 1000 == 0)) {
                    file_put_contents(
                        $this->archivo_log,
                        functions::encode_json(
                            array(
                                'mensaje'    => $file . ' (' . ($total - count($lista)) . '/' . $total . ')',
                                'porcentaje' => 10 + (($total - count($lista)) / $total) * 40,
                            )
                        )
                    );
                    $tiempo = time();
                }
            }
            if ($log) {
                file_put_contents(
                    $this->archivo_log,
                    functions::encode_json(
                        array(
                            'mensaje'      => functions::substring($file, 30) . ' (' . ($total - count($lista)) . '/' . $total . ')',
                            'notificacion' => 'Guardando archivo, Esta operacion puede tomar algun tiempo',
                            'porcentaje'   => 10 + (($total - count($lista)) / $total) * 40,
                        )
                    )
                );

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
