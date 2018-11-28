<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\banner as banner_model;
use \app\models\producto as producto_model;
use \app\models\seccion as seccion_model;
use \app\models\seo;
use \core\functions;
use \core\image;
use \core\view;

class home extends base
{
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo']);
    }
    public function index()
    {
        $this->meta($this->seo);
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $row_banner = banner_model::getAll(array('tipo' => 1));
        $banner     = new banner();
        $banner->normal($row_banner);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);

        $secciones_destacadas = seccion_model::getAll(array('tipo' => 3, 'destacado' => true));
        $seo                  = seo::getById(7);
        foreach ($secciones_destacadas as $key => $seccion) {
            view::set('title', $seccion['titulo']);
            view::set('subtitle', $seccion['subtitulo']);
            view::set('text', $seccion['resumen']);
            view::set('url', functions::url_seccion(array($seo['url'], 'detail'), $seccion));
            view::set('image', image::generar_url(image::portada($seccion['foto']), ''));
            view::render('home-text');
        }

        $productos_destacados = producto_model::getAll(array('tipo' => 1, 'destacado' => true));
        if (count($productos_destacados > 0)) {
            $seo             = seo::getById(8);
            $lista_productos = array();
            foreach ($productos_destacados as $key => $producto) {
                $lp          = array();
                $lp['id']    = $producto[0];
                $lp['title'] = $producto['titulo'];
                $lp['price'] = functions::formato_precio($producto['precio']);
                $lp['url']   = functions::url_seccion(array($seo['url'], 'detail'), $producto);
                $lp['image'] = image::generar_url(image::portada($producto['foto']), 'foto2');
                if ($lp['image'] != "") {
                    $lista_productos[] = $lp;
                }
            }
            //view::set('title',$seo['titulo']);
            view::set('title', "Nuestros productos destacados");
            view::set('lista_productos', $lista_productos);
            view::render('home-products');
        }

        $footer = new footer();
        $footer->normal();
    }
}
