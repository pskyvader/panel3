<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use core\functions;
use \app\models\logo as logo_model;
use \core\image;
use \core\view;

class head
{
    private $data = array(
        'favicon' => '',
        'keywords' => false,
        'keywords_text' => '',
        'description' => false,
        'description_text' => '',
        'title' => '',
        'current_url' => '',
        'image' => false,
        'image_url' => '',
        'logo' => '',
        'color_primario' => '',
        'manifest_url' => '',
        'path' => ''
    );

    public function __construct($metadata)
    {
        foreach ($metadata as $key => $value) {
            if (isset($this->data[$key])) {
                $this->data[$key] = $value;
            }
        }
        $config = app::getConfig();
        $this->data['current_url'] = functions::current_url();
        $this->data['path'] = app::$_path;
        $this->data['color_primario'] = $config['color_primario'];
        $this->data['googlemaps_key'] = $config['googlemaps_key'];

        $title = $config['title'];
        $short_title = $config['short_title'];
        $titulo = $this->data['title'] . ' - ' . $title;
        if (strlen($titulo) > 75) {
            $titulo = $this->data['title'] . ' - ' . $short_title;
        }
        if (strlen($titulo) > 75) {
            $titulo = $this->data['title'];
        }

        if (strlen($titulo) > 75) {
            $titulo = substr($this->data['title'], 0, 75);
        }
        $this->data['title'] = $titulo;

        $logo = logo_model::getById(5);
        $this->data['logo'] = image::generar_url($logo['foto'][0], 'social');
        if (isset($metadata['image'])) {
            $this->data['image_url'] = $metadata['image'];
            $this->data['image'] = true;
        }
        $logo = logo_model::getById(1);
        $this->data['favicon'] = image::generar_url($logo['foto'][0], 'favicon');

        $this->data['manifest_url'] = app::get_url() . 'manifest.js';

    }
    public function normal()
    {
        if (!isset($_POST['ajax'])) {
            if (isset($_POST['ajax_header'])) {
                $this->ajax();
            } else {
                $this->data['css'] = view::css(true);
                view::set_array($this->data);
                view::render('head');
            }
        }
    }

    public function ajax()
    {
        header('Content-Type: application/json');
        echo json_encode($this->data);
        exit;
    }

}
