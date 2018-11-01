<?php
namespace app\controllers\front\themes\gatografias;

defined("APPPATH") or die("Acceso denegado");
use \app\models\banner as banner_model;
use \app\models\logo as logo_model;
use \app\models\seo as seo_model;
use \core\app;
use \core\functions;
use \core\image;
use \core\view;

class application
{
    private $url = array('home');
    private $metadata = array('title' => 'Home', 'modulo' => 'home');
    public function index()
    {
        
        $this->metadata['class'] = (new \ReflectionClass($this))->getShortName();
        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        $config = app::getConfig();
        $logo = logo_model::getById(5);
        view::set('color_primario', $config['color_primario']);
        view::set('color_secundario', $config['color_secundario']);
        view::set('logo', image::generar_url($logo['foto'][0], 'sitio'));

        $seo = seo_model::getById(1);
        view::set('path', functions::generar_url(array($seo['url'])));

        $row_banner = banner_model::getAll(array('tipo' => 1));
        $banner = new banner();
        $imgs = array();
        foreach ($row_banner as $key => $b) {
            if (isset($b["foto"][0])) {
                $foto = image::generar_url($b["foto"][0], 'foto1');
            } else {
                $foto = '';
            }
            if ($foto != '') {
                $srcset = $banner->srcset($b["foto"][0]);
                $imgs = array_merge($imgs, $srcset);
            }
        }
        $images = array();
        foreach ($imgs as $key => $i) {
            $images[] = array('src' => $i['url']);
        }

        view::set('images', $images);

        view::render('application');
        $footer = new footer();
        $footer->normal();
    }
}
