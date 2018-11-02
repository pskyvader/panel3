<?php
namespace app\controllers\front\themes\mdbootstrap;

defined("APPPATH") or die("Acceso denegado");

use \app\models\logo as logo_model;
use \app\models\seo;
use \app\models\texto;
use \core\app;
use \core\functions;
use \core\image;
use \core\view;

class footer
{
    public function normal()
    {
        if (!isset($_POST['ajax'])) {
            $data = array();
            $config = app::getConfig();
            $logo = logo_model::getById(6);
            $data['logo'] = image::generar_url($logo['foto'][0], 'sitio');
            $seo = seo::getById(1);
            $data['path'] = functions::generar_url(array($seo['url']));
            $data['title'] = $config['title'];
            view::set_array($data);

            $telefono = texto::getById(1);
            view::set('telefono', $telefono['texto']);
            $email = texto::getById(2);
            view::set('email', $email['texto']);
            $direccion = texto::getById(6);
            view::set('direccion', $direccion['texto']);
            $redes_sociales = array();
            $rss=texto::getAll(array('tipo'=>2));
            foreach ($rss as $key => $r) {
                $redes_sociales[] = array('url' => functions::ruta($r['url']), 'icon' => $r['texto'], 'title' => $r['titulo']);
            }

            view::set('social', $redes_sociales);

            view::set('is_social', (count($redes_sociales) > 0));

            view::render('footer');
            view::js();
        }
    }
}
