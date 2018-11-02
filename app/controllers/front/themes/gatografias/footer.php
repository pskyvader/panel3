<?php
namespace app\controllers\front\themes\gatografias;

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




            //$logo = logo_model::getById(6);
            //$data['logo'] = image::generar_url($logo['foto'][0], 'sitio');
           // $seo = seo::getById(1);
            //$data['path'] = functions::generar_url(array($seo['url']));
            //$data['title'] = $config['title'];
            

            $telefono = texto::getById(1);
            view::set('telefono', $telefono['texto']);
            $email = texto::getById(2);
            view::set('email', $email['texto']);
            $nombre = texto::getById(11);
            view::set('nombre', $nombre['texto']);
            $redes_sociales = array();

            $rss=texto::getAll(array('tipo'=>2));
            foreach ($rss as $key => $r) {
                $redes_sociales[] = array('url' => functions::ruta($r['url']), 'icon' => $r['texto'], 'title' => $r['titulo']);
            }
            $instagram = texto::getById(5);

            view::set('social', $redes_sociales);
            view::set('is_social', (count($redes_sociales) > 0));
            view::set('instagram', functions::ruta($instagram['texto']));

            
            $texto=texto::getById(10);
            view::set('title', $texto['titulo']);
            view::set('descripcion', $texto['descripcion']);

            view::render('footer');
            view::js();
        }
    }
}