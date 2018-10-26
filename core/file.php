<?php
namespace core;

defined("APPPATH") or die("Access denied");

use \core\functions;

class file extends image
{
    protected static $types      = array("application/zip", "application/x-zip-compressed", "application/octet-stream", "application/postscript", "application/msword", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.spreadsheetml.template", "application/vnd.openxmlformats-officedocument.presentationml.template", "application/vnd.openxmlformats-officedocument.presentationml.slideshow", "application/vnd.openxmlformats-officedocument.presentationml.presentation", "application/vnd.openxmlformats-officedocument.presentationml.slide", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.openxmlformats-officedocument.wordprocessingml.template", "application/vnd.ms-excel.addin.macroEnabled.12", "application/vnd.ms-excel.sheet.binary.macroEnabled.12", "application/pdf", "application/download");
    protected static $extensions = array("zip", "doc", "docx", "dotx", "xls", "xlsx", "xltx", "xlam", "xlsb", "ppt", "pptx", "potx", "ppsx", "sldx", "pdf");

    public function __construct($metadata)
    {

    }
    public static function upload_tmp($modulo = '')
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        if (isset($_FILES)) {
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
                    $name           = self::nombre_archivo($archivo['name'], '');
                    $archivo['url'] = self::get_upload_url() . $archivo['folder'] . '/' . $name;
                    $respuesta['mensaje'] .= $archivo['original_name'] . ' <br/>';
                    $archivos[] = $archivo;

                }
            }
            $respuesta['archivos'] = $archivos;
        } else {
            $respuesta['mensaje'] = 'No se encuentran archivos a subir';
        }

        return $respuesta;
    }

    public static function move($file, $folder, $subfolder, $name_final, $folder_tmp = 'tmp')
    {
        $folder_tmp = self::get_upload_dir() . $folder_tmp;
        $folder     = self::get_upload_dir() . $folder . '/' . $name_final . '/' . $subfolder;
        if (!file_exists($folder)) {
            if (!mkdir($folder, 0777, true)) {
                echo "Error al crear directorio " . $folder;
                exit();
            }
        }
        $name      = explode(".", $file['tmp']);
        $extension = strtolower(array_pop($name));

        $nombre_final = explode(".", $file['original_name']);
        array_pop($nombre_final);
        $nombre_final = functions::url_amigable(implode($nombre_final, ''));

        $file['url'] = $file['id'] . '-' . $nombre_final . '.' . $extension;
        rename($folder_tmp . '/' . $file['tmp'], $folder . '/' . $file['url']);
        unset($file['original_name'],$file['tmp']);
        $file['subfolder'] = $subfolder;
        return $file;
    }

    public static function delete($folder, $file = '', $subfolder = '', $sub = '')
    {
        if ("" == $file && '' != $subfolder) {
            $url = self::get_upload_dir() . $folder . '/' . $subfolder . '/';
            if (file_exists($url)) {
                array_map('unlink', glob("$url/*.*"));
                rmdir($url);
            }
        } elseif ('' == $file && '' == $subfolder) {
            $url = self::get_upload_dir() . $folder . '/';
            if (file_exists($url)) {
                array_map('unlink', glob("$url/*.*"));
                rmdir($url);
            }
        } else {
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

        }
    }

}
