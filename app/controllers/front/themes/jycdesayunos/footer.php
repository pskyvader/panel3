<?php
namespace app\controllers\front\themes\jycdesayunos;
defined("APPPATH") or die("Acceso denegado");
use \app\models\logo as logo_model;
use \app\models\productocategoria as productocategoria_model;
use \app\models\seccion as seccion_model;
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

            $links_footer=array();
            $l=array('title'=>'Información','links'=>array(),'size'=>3);
            $row=seccion_model::getAll(array('tipo'=>3));
            $seo=seo::getById(7);
            foreach ($row as $key => $seccion) {
                $l['links'][]=array('url'=>functions::url_seccion(array($seo['url'], 'detail'), $seccion),'title'=>$seccion['titulo']);
            }
            $links_footer[]=$l;

            
            $l=array('title'=>'Productos','links'=>array(),'size'=>3);
            $row=productocategoria_model::getAll(array('tipo'=>1,'idpadre'=>0));
            $seo=seo::getById(8);
            foreach ($row as $key => $productos) {
                $l['links'][]=array('url'=>functions::url_seccion(array($seo['url'], 'detail'), $productos),'title'=>$productos['titulo']);
            }
            $links_footer[]=$l;
            
            $l=array('title'=>'Mi cuenta','links'=>array(),'size'=>2);
            $l['links'][]=array('url'=>functions::generar_url(array('cuenta','detail','mi-cuenta')),'title'=>"Mi cuenta");
            $l['links'][]=array('url'=>functions::generar_url(array('cuenta','detail','direcciones')),'title'=>"Mis direcciones");
            $l['links'][]=array('url'=>functions::generar_url(array('cuenta','detail','pedidos')),'title'=>"Mis pedidos");
            $links_footer[]=$l;

            view::set('links_footer', $links_footer);
            view::render('footer');
            view::js();
        }
    }
}
