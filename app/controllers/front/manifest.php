<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");

use \core\app;
use \core\image;
use \core\functions;
use \app\models\logo as logo_model;


class manifest
{
    public function index()
    {
        $version_application=101;
        $config=app::getConfig();
        $logo=logo_model::getById(7);
        $manifest = array(
            'short_name' => $config['short_title'],
            'name' => $config['title'],
            'icons' => array(
                array(
                    'src' => image::generar_url($logo['foto'][0], 'icono50'),
                    'type' => 'image/png',
                    'sizes' => '50x50',
                ), array(
                    'src' => image::generar_url($logo['foto'][0], 'icono100'),
                    'type' => 'image/png',
                    'sizes' => '100x100',
                ), array(
                    'src' => image::generar_url($logo['foto'][0], 'icono200'),
                    'type' => 'image/png',
                    'sizes' => '200x200',
                ), array(
                    'src' => image::generar_url($logo['foto'][0], 'icono600'),
                    'type' => 'image/png',
                    'sizes' => '600x600',
                )
            ),
            "start_url" => functions::generar_url(array("application","index",$version_application), false),
            "background_color" => $config['color_secundario'],
            "display" => "standalone",
            "theme_color" => $config['color_primario'],
        );
        header('Content-Type: application/json');
        echo functions::encode_json($manifest);
    }
}
