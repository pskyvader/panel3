<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use \app\models\logo as logo_model;
use \core\functions;
use \core\image;
use \core\view;

class header
{
    private $data = array(
        'logo' => '',
        'url_exit' => '',
    );
    public function normal()
    {
        if (!isset($_POST['ajax'])) {
            $logo = logo_model::getById(3);
            $this->data['logo_max'] = image::generar_url($logo['foto'][0], 'panel_max');
            $logo = logo_model::getById(4);
            $this->data['logo_min'] = image::generar_url($logo['foto'][0], 'panel_min');
            $this->data['url_exit'] = functions::generar_url(array('logout'), false);
            view::set_array($this->data);
            view::set('date', date('Y-m-d H:i:s'));
            view::render('header');
        }
    }
}
