<?php
namespace core;

defined("APPPATH") or die("Access denied");

use \app\models\modulo as modulo_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \core\app;
use \core\functions;

class image
{
    private static $types      = array("image/webp", "image/bmp", "image/gif", "image/pjpeg", "image/jpeg", "image/svg+xml", "image/png", "video/webm", "video/mp4", "application/zip", "application/x-zip-compressed", "application/octet-stream", "application/postscript", "application/msword", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.spreadsheetml.template", "application/vnd.openxmlformats-officedocument.presentationml.template", "application/vnd.openxmlformats-officedocument.presentationml.slideshow", "application/vnd.openxmlformats-officedocument.presentationml.presentation", "application/vnd.openxmlformats-officedocument.presentationml.slide", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.openxmlformats-officedocument.wordprocessingml.template", "application/vnd.ms-excel.addin.macroEnabled.12", "application/vnd.ms-excel.sheet.binary.macroEnabled.12", "application/pdf", "application/download");
    private static $extensions = array("webp", "bmp", "ico", "gif", "jpeg", "jpg", "svg", "xml", "png", "webm", "mp4", "zip", "doc", "docx", "dotx", "xls", "xlsx", "xltx", "xlam", "xlsb", "ppt", "pptx", "potx", "ppsx", "sldx", "pdf");
    private static $upload_dir = '';
    private static $upload_url = '';

    public function __construct($metadata)
    {

    }
    //Subir a carpeta temporal, durante la creacion de la seccion. al guardar el archivo se mueve a la carpeta definitiva
    public static function upload_tmp($modulo)
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        if (isset($_FILES)) {
            $recortes = self::get_recortes($modulo);
            $archivos = array();

            if (isset($_FILES['file'])) {
                $file_ary = functions::reArrayFiles($_FILES['file']);
            } else {
                $file_ary = $_FILES;
            }

            foreach ($file_ary as $key => $files) {
                $archivo            = self::upload($files, 'tmp');
                $respuesta['exito'] = $archivo['exito'];
                if (!$archivo['exito']) {
                    $respuesta['mensaje'] = $archivo['mensaje'];
                    break;
                } else {
                    $recorte = self::recortes_foto($archivo, $recortes);
                    if (!$recorte['exito']) {
                        $respuesta['mensaje'] = $recorte['mensaje'];
                        break;
                    } else {
                        $name           = self::nombre_archivo($archivo['name'], 'thumb');
                        $archivo['url'] = self::get_upload_url() . $archivo['folder'] . '/' . $name;
                        $respuesta['mensaje'] .= $archivo['original_name'] . ' <br/>';
                        $archivos[] = $archivo;
                    }
                }
            }
            $respuesta['archivos'] = $archivos;
        } else {
            $respuesta['mensaje'] = 'No se encuentran archivos a subir';
        }

        return $respuesta;
    }
    //regenerar imagenes ya guardadas
    public static function regenerar($file)
    {
        $recortes       = self::get_recortes($file['folder']);
        $file['name']   = $file['url'];
        $file['folder'] = $file['folder'] . '/' . $file['parent'] . '/' . $file['subfolder'];
        array_map('unlink', glob(self::get_upload_dir() . $file['folder'] . "/" . $file['id'] . "-*.*"));
        $respuesta = self::recortes_foto($file, $recortes);
        return $respuesta;
    }

    //mover archivo (normalmente) desde la carpeta temporal a la definitiva
    public static function move($file, $folder, $subfolder, $name_final, $folder_tmp = 'tmp')
    {
        $recortes   = self::get_recortes($folder);
        $folder_tmp = self::get_upload_dir() . $folder_tmp;
        $base_folder= self::get_upload_dir() . $folder;
        $folder     = $base_folder . '/' . $name_final . '/' . $subfolder;

        if (!file_exists($folder)) {
            if (!mkdir($folder, 0777, true)) {
                echo "Error al crear directorio " . $folder;
                exit();
            }
            functions::protection_template($base_folder);
            functions::protection_template($base_folder . '/' . $name_final);
            functions::protection_template($folder);
        }

        $name      = explode(".", $file['tmp']);
        $extension = strtolower(array_pop($name));

        $file['url'] = $file['id'] . '.' . $extension;
        rename($folder_tmp . '/' . $file['tmp'], $folder . '/' . $file['url']);

        foreach ($recortes as $key => $recorte) {
            rename($folder_tmp . '/' . self::nombre_archivo($file['tmp'], $recorte['tag']), $folder . '/' . self::nombre_archivo($file['url'], $recorte['tag']));
            if (file_exists($folder_tmp . '/' . self::nombre_archivo($file['tmp'], $recorte['tag'], 'webp'))) {
                rename($folder_tmp . '/' . self::nombre_archivo($file['tmp'], $recorte['tag'], 'webp'), $folder . '/' . self::nombre_archivo($file['url'], $recorte['tag'], 'webp'));
            }
        }
        unset($file['tmp']);
        $file['subfolder'] = $subfolder;
        return $file;
    }
    //obtener recortes de fotos desde bdd, segun la seccion actual
    private static function get_recortes($modulo)
    {
        $moduloconfiguracion = moduloconfiguracion_model::getByModulo($modulo);
        $var                 = array('idmoduloconfiguracion' => $moduloconfiguracion[0]);
        if (isset($_GET['tipo'])) {
            $var['tipo'] = $_GET['tipo'];
        }
        $modulo     = modulo_model::getAll($var, array('limit' => 1));
        $recortes   = array();
        $recortes[] = array('tag' => 'thumb', 'titulo' => 'Thumb', 'ancho' => 200, 'alto' => 200, 'calidad' => 90, 'tipo' => 'centrar');
        $recortes[] = array('tag' => 'zoom', 'titulo' => 'Zoom', 'ancho' => 600, 'alto' => 600, 'calidad' => 90, 'tipo' => 'centrar');
        $recortes[] = array('tag' => 'color', 'titulo' => 'Color', 'ancho' => 30, 'alto' => null, 'calidad' => 99, 'tipo' => 'recortar');

        if (isset($modulo[0]['recortes'])) {
            foreach ($modulo[0]['recortes'] as $key => $recorte) {
                $recorte['ancho']   = (int) $recorte['ancho'];
                $recorte['alto']    = (int) $recorte['alto'];
                $recorte['calidad'] = (int) $recorte['calidad'];
                if ($recorte['calidad'] > 100) {
                    $recorte['calidad'] = 100;
                }

                if ($recorte['calidad'] < 0) {
                    $recorte['calidad'] = 0;
                }

                $recortes[] = $recorte;
            }
        }

        return $recortes;
    }
//subir archivo
    protected static function upload($file, $folder_upload = 'tmp', $name_final = '')
    {
        $folder = self::get_upload_dir() . $folder_upload;

        $respuesta = self::validate($file);
        if ($respuesta['exito']) {
            if ('' == $name_final) {
                $name_final = uniqid();
            } else {
                $name_final = explode(".", $name_final);
                $extension  = '.' . strtolower(array_pop($name_final));
                $name_final = functions::url_amigable(implode($name_final, ''));
            }
            $name      = explode(".", $file['name']);
            $extension = '.' . strtolower(array_pop($name));
            $name      = functions::url_amigable(implode($name, ''));
            if (!file_exists($folder)) {
                $respuesta['exito'] = mkdir($folder, 0777, true);
                if (!$respuesta['exito']) {
                    $respuesta['mensaje'] = "Error al crear directorio " . $folder;
                    return $respuesta;
                }
            }
            functions::protection_template($folder);
            $respuesta['exito'] = move_uploaded_file($file['tmp_name'], $folder . '/' . $name_final . $extension);
            if (!$respuesta['exito']) {
                $respuesta['mensaje'] = "Error al mover archivo. Permisos: " . substr(sprintf('%o', fileperms($folder)), -4) . ", carpeta: " . $folder;
            } else {
                $respuesta['name']          = $name_final . $extension;
                $respuesta['folder']        = $folder_upload;
                $respuesta['original_name'] = $file['name'];
                $respuesta['mensaje']       = "Imagen " . $file['name'] . " Subida correctamente";
            }
        }
        return $respuesta;
    }

    private static function validate($file)
    {
        $name      = explode(".", $file['name']);
        $extension = strtolower(array_pop($name));
        $respuesta = array('exito' => false, 'mensaje' => 'Error: formato no valido');
        if (0 != $file['error']) {
            $respuesta['mensaje'] = 'Error al subir archivo: ' . $file['error'];
        } elseif (!in_array($file['type'], static::$types)) {
            $respuesta['mensaje'] .= '. Extension: ' . $file['type'];
        } elseif (!in_array($extension, static::$extensions)) {
            $respuesta['mensaje'] .= '.<br/> Extension de archivo: ' . $extension;
        } else {
            $respuesta['exito'] = true;
        }
        return $respuesta;
    }

    private static function recortes_foto($archivo, $recortes_foto)
    {
        $respuesta   = array('exito' => false);
        $ruta        = self::get_upload_dir() . $archivo['folder'];
        $foto        = $archivo['name'];
        $ruta_imagen = $ruta . '/' . $foto;
        if (!file_exists($ruta_imagen)) {
            $respuesta['mensaje'] = 'Archivo ' . $ruta_imagen . ' no existe';
            return $respuesta;
        }
        $info_imagen = getimagesize($ruta_imagen);
        $ancho       = $info_imagen[0];
        $alto        = $info_imagen[1];

        $ancho_maximo = 0;
        $alto_maximo  = 0;
        $ancho_valido = 0;
        $alto_valido  = 0;

        foreach ($recortes_foto as $recorte) {
            if ($recorte['ancho'] > $ancho_maximo) {
                $ancho_maximo = $recorte['ancho'];
                if ($ancho_maximo > $ancho_valido && $ancho_maximo <= $ancho) {
                    $ancho_valido = $ancho_maximo;
                }
            }
            if ($recorte['alto'] > $alto_maximo) {
                $alto_maximo = $recorte['alto'];
                if ($alto_maximo > $alto_valido && $alto_maximo <= $alto) {
                    $alto_valido = $alto_maximo;
                }
            }

        }

        // si es valido, se crea una imagen intermedia para acelerar el proceso de recorte de las demas imagenes
        if (($alto > ($alto_valido * 1.5) && $alto_valido>0) || ($ancho > ($ancho_valido * 1.5) && $ancho_valido>0 )) {
            $alto_final  = ($alto / $ancho) * $ancho_valido; //alto proporcional segun mayor ancho valido
            $ancho_final = ($ancho / $alto) * $alto_valido; //ancho proporcional segun mayor alto valido
            if ($ancho_final >= $ancho_valido) {
                $respuesta = self::recortar_foto(array('tag' => 'recorte_previo', 'ancho' => null, 'alto' => $alto_valido, 'calidad' => 100, 'tipo' => 'recortar'), $archivo);
            } else {
                $respuesta = self::recortar_foto(array('tag' => 'recorte_previo', 'ancho' => $ancho_valido, 'alto' => null, 'calidad' => 100, 'tipo' => 'recortar'), $archivo);
            }
            if (!$respuesta['exito']) {
                return $respuesta;
            }
            $archivo_recorte         = $archivo;
            $archivo_recorte['name'] = self::nombre_archivo($archivo_recorte['name'], 'recorte_previo');
            foreach ($recortes_foto as $recorte) {
                if ($recorte['ancho'] <= $ancho_valido && $recorte['alto'] <= $alto_valido) {
                    $respuesta = self::recortar_foto($recorte, $archivo_recorte);
                } else {
                    $respuesta = self::recortar_foto($recorte, $archivo);
                }
                if (!$respuesta['exito']) {
                    return $respuesta;
                }
            }
        } else {
            foreach ($recortes_foto as $recorte) {
                $respuesta = self::recortar_foto($recorte, $archivo);
                if (!$respuesta['exito']) {
                    return $respuesta;
                }
            }
        }

        return $respuesta;
    }

    private static function proporcion_foto($ancho_maximo, $alto_maximo, $ancho, $alto, $tipo)
    {
        $proporcion_imagen    = $ancho / $alto;
        $proporcion_miniatura = $ancho_maximo / $alto_maximo;
        $miniatura_ancho      = $ancho_maximo;
        $miniatura_alto       = $alto_maximo;

        if ($tipo == 'recortar') {
            if ($proporcion_imagen > $proporcion_miniatura) {
                $miniatura_ancho = $alto_maximo * $proporcion_imagen;
            } else if ($proporcion_imagen < $proporcion_miniatura) {
                $miniatura_alto = $ancho_maximo / $proporcion_imagen;
            }
            $x = ($miniatura_ancho - $ancho_maximo) / 2;
            $y = ($miniatura_alto - $alto_maximo) / 2;
        } else {
            if ($proporcion_imagen > $proporcion_miniatura) {
                if ($ancho > $alto) {
                    $miniatura_alto = $ancho_maximo / $proporcion_imagen;
                } else {
                    if ($ancho_maximo > $alto_maximo) {
                        $miniatura_alto = $alto_maximo * $proporcion_imagen;
                    } else {
                        $miniatura_alto = $ancho_maximo / $proporcion_imagen;
                    }
                }
            } else if ($proporcion_imagen < $proporcion_miniatura) {
                if ($ancho_maximo > $alto_maximo) {
                    $miniatura_ancho = $alto_maximo * $proporcion_imagen;
                } elseif ($ancho_maximo < $alto_maximo) {
                    $miniatura_ancho = $ancho_maximo * $proporcion_miniatura;
                } else {
                    $miniatura_ancho = $ancho_maximo * $proporcion_imagen;
                }
            }

            if ($tipo == 'centrar' && $ancho < $miniatura_ancho && $alto < $miniatura_alto) {
                $x = ($ancho_maximo - $ancho) / 2;
                $y = ($alto_maximo - $alto) / 2;
            } else {
                $x = ($ancho_maximo - $miniatura_ancho) / 2;
                $y = ($alto_maximo - $miniatura_alto) / 2;
            }

        }

        return array('x' => $x, 'y' => $y, 'miniatura_ancho' => $miniatura_ancho, 'miniatura_alto' => $miniatura_alto);
    }

    private static function recortar_foto($recorte, $datos)
    {
        $respuesta    = array('exito' => false, 'mensaje' => 'error al recortar imagen');
        $ancho_maximo = $recorte['ancho'];
        $alto_maximo  = $recorte['alto'];
        $ruta         = self::get_upload_dir() . $datos['folder'];
        $foto         = $datos['name'];
        $etiqueta     = $recorte['tag'];
        $tipo         = $recorte['tipo'];

        ini_set('memory_limit', '-1');
        $ruta_imagen = $ruta . '/' . $foto;
        if (!file_exists($ruta_imagen)) {
            $respuesta['mensaje'] = 'Archivo ' . $ruta_imagen . ' no existe';
            return $respuesta;
        }
        $info_imagen = getimagesize($ruta_imagen);
        $ancho       = $info_imagen[0];
        $alto        = $info_imagen[1];
        $imagen_tipo = $info_imagen['mime'];

        $proporcion_imagen = $ancho / $alto;
        if (null == $ancho_maximo || 0 == $ancho_maximo) {
            $ancho_maximo = (float) $alto_maximo *$proporcion_imagen;
        }
        if (null == $alto_maximo || 0 == $alto_maximo) {
            $alto_maximo = (float) $ancho_maximo / $proporcion_imagen;
        }

        $tamano_final    = self::proporcion_foto($ancho_maximo, $alto_maximo, $ancho, $alto, $tipo);
        $x               = $tamano_final['x'];
        $y               = $tamano_final['y'];
        $miniatura_ancho = $tamano_final['miniatura_ancho'];
        $miniatura_alto  = $tamano_final['miniatura_alto'];

        switch ($imagen_tipo) {
            case "image/jpg":
            case "image/jpeg":
                $imagen = imagecreatefromjpeg($ruta_imagen);
                break;
            case "image/png":
                $imagen = imagecreatefrompng($ruta_imagen);
                break;
            case "image/gif":
                $imagen = imagecreatefromgif($ruta_imagen);
                break;
        }

        $lienzo = imagecreatetruecolor($ancho_maximo, $alto_maximo);
        if ("recortar" == $tipo) {
            $lienzo_temporal = imagecreatetruecolor($miniatura_ancho, $miniatura_alto);
        }

        if ("image/png" == $imagen_tipo) {
            imagecolortransparent($lienzo, imagecolorallocatealpha($imagen, 0, 0, 0, 127));
            imagealphablending($lienzo, false);
            imagesavealpha($lienzo, true);
            imagefill($lienzo, 0, 0, imagecolorallocatealpha($imagen, 0, 0, 0, 127));

            if ("recortar" == $tipo) {
                imagecolortransparent($lienzo_temporal, imagecolorallocatealpha($lienzo_temporal, 0, 0, 0, 127));
                imagealphablending($lienzo_temporal, false);
                imagesavealpha($lienzo_temporal, true);
            }
        } else {
            $blanco = imagecolorallocate($imagen, 255, 255, 255);
            imagefill($lienzo, 0, 0, $blanco);

            if ("recortar" == $tipo) {
                imagefill($lienzo_temporal, 0, 0, $blanco);
            }
        }

        if ("recortar" == $tipo) {
            imagecopyresampled($lienzo_temporal, $imagen, 0, 0, 0, 0, $miniatura_ancho, $miniatura_alto, $ancho, $alto);
            imagecopy($lienzo, $lienzo_temporal, 0, 0, $x, $y, $ancho_maximo, $alto_maximo);
        } elseif ("rellenar" == $tipo) {
            imagecopyresampled($lienzo, $imagen, $x, $y, 0, 0, $miniatura_ancho, $miniatura_alto, $ancho, $alto);
        } else {
            if ($ancho >= $miniatura_ancho || $alto >= $miniatura_alto) {
                imagecopyresampled($lienzo, $imagen, $x, $y, 0, 0, $miniatura_ancho, $miniatura_alto, $ancho, $alto);
            } else {
                imagecopyresampled($lienzo, $imagen, $x, $y, 0, 0, $ancho, $alto, $ancho, $alto);

            }
        }

        $foto_recorte = self::nombre_archivo($foto, $etiqueta, '', true);
        $foto_webp    = self::nombre_archivo($foto, $etiqueta, 'webp', true);
        if (file_exists($ruta . $foto_recorte)) {
            unlink($ruta . $foto_recorte);
        }
        if (file_exists($ruta . $foto_webp)) {
            unlink($ruta . $foto_webp);
        }

        if ("image/png" == $imagen_tipo) {
            imagepng($lienzo, $ruta . '/' . $foto_recorte, 8);
        } else {
            imagejpeg($lienzo, $ruta . '/' . $foto_recorte, $recorte['calidad']);
            imagewebp($lienzo, $ruta . '/' . $foto_webp, $recorte['calidad']);
        }

        $respuesta['exito'] = true;

        return $respuesta;
    }
    public static function nombre_archivo($file, $tag = '', $extension = '', $remove = false)
    {
        $name = explode(".", $file);
        if ('' == $extension) {
            $extension = strtolower(array_pop($name));
        } else {
            array_pop($name);
        }
        if ($remove) {
            $name = explode('-', implode($name, ''));
            if (count($name) > 1) {
                array_pop($name);
            }
        }

        $name = functions::url_amigable(implode($name, ''));
        if ('' != $tag) {
            return $name . '-' . $tag . '.' . $extension;
        } else {
            return $name . '.' . $extension;
        }
    }
    public static function generar_url($file, $tag = 'thumb', $extension = "", $folder = "", $subfolder = "")
    {
        if ('' == $folder) {
            $folder = $file['folder'];
        }
        if ('' != $subfolder) {
            $subfolder .= '/';
        } else {
            $subfolder = $file['parent'] . '/' . $file['subfolder'] . '/';
        }

        $url  = $folder . '/' . $subfolder . (self::nombre_archivo($file['url'], $tag, $extension));
        $time = functions::fecha_archivo(self::get_upload_dir() . $url, true);
        if (false != $time) {
            $archivo = self::get_upload_url() . $url . '?time=' . $time;

        } else { $archivo = '';}
        return $archivo;
    }

    public static function generar_dir($file, $tag = 'thumb', $extension = "", $folder = "", $subfolder = "")
    {
        if ('' == $folder) {
            $folder = $file['folder'];
        }
        if ('' != $subfolder) {
            $subfolder .= '/';
        } else {
            $subfolder = $file['parent'] . '/' . $file['subfolder'] . '/';
        }

        $url     = $folder . '/' . $subfolder . (self::nombre_archivo($file['url'], $tag, $extension));
        $archivo = self::get_upload_dir() . $url;
        if (!file_exists($archivo)) {
            $archivo = '';
        }
        return $archivo;
    }

    public static function portada($fotos)
    {
        $portada = array();
        if (count($fotos) > 0) {
            $portada = $fotos[0];
            foreach ($fotos as $key => $f) {
                if ('true' == $f['portada']) {
                    $portada = $f;
                    break;
                }
            }
        }
        return $portada;
    }

    private static function removeDirectory($path) {
        // The preg_replace is necessary in order to traverse certain types of folder paths (such as /dir/[[dir2]]/dir3.abc#/)
        // The {,.}* with GLOB_BRACE is necessary to pull all hidden files (have to remove or get "Directory not empty" errors)
        $files = glob(preg_replace('/(\*|\?|\[)/', '[$1]', $path).'/{,.}*', GLOB_BRACE);
        $s=true;
        foreach ($files as $file) {
            if ($file == $path.'/.' || $file == $path.'/..') { continue; } // skip special dir entries
            $s=is_dir($file) ? self::removeDirectory($file) : unlink($file);
            if(!$s){
                echo 'Error :'.$file;
                exit;
            }
        }
        if($s){
            $s=rmdir($path);
        }else{
            echo 'Error :'.$path;
        }
        return $s;
    }
    public static function delete($folder, $file = '', $subfolder = '', $sub = '')
    {
        if ("" == $file && '' != $subfolder) {
            $url = self::get_upload_dir() . $folder . '/' . $subfolder . '/';
            if (file_exists($url)) {
                self::removeDirectory($url);
            }
        } elseif ('' == $file && '' == $subfolder) {
            $url = self::get_upload_dir() . $folder . '/';
            if (file_exists($url)) {
                self::removeDirectory($url);
            }
        } else {
            $recortes = self::get_recortes($folder);
            if ('' != $subfolder) {
                $subfolder .= '/';
            }
            if ('' != $sub) {
                $sub .= '/';
            }
            $url = self::get_upload_dir() . $folder . '/' . $subfolder . $sub . $file['url'];
            if (file_exists($url)) {
                unlink($url);
            }

            foreach ($recortes as $key => $recorte) {
                $url = self::get_upload_dir() . $folder . '/' . $subfolder . $sub . self::nombre_archivo($file['url'], $recorte['tag']);
                if (file_exists($url)) {
                    unlink($url);
                }

                $url = self::get_upload_dir() . $folder . '/' . $subfolder . $sub . self::nombre_archivo($file['url'], $recorte['tag'], 'webp');

                if (file_exists($url)) {
                    unlink($url);
                }

            }
        }
    }

    public static function delete_temp()
    {
        $carpeta    = self::get_upload_dir() . 'tmp/';
        $directorio = opendir($carpeta); //ruta actual
        $now        = time();
        $horas      = 1;
        while ($archivo = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
        {
            if (!is_dir($carpeta . $archivo)) //verificamos si es o no un directorio
            {
                if (file_exists($carpeta . $archivo)) {
                    if (($now - filemtime($carpeta . $archivo)) / 3600 > $horas) {
                        //si el archivo fue creado hace más de $horas, borrar
                        unlink($carpeta . $archivo);
                    }
                }
            }
        }
    }
    public static function get_upload_dir()
    {
        if ('' == self::$upload_dir) {
            self::$upload_dir = app::get_dir(true) . 'uploads/img/';
        }

        return self::$upload_dir;
    }
    public static function get_upload_url()
    {
        if ('' == self::$upload_url) {
            self::$upload_url = app::get_url(true) . 'uploads/img/';
        }

        return self::$upload_url;
    }
}
