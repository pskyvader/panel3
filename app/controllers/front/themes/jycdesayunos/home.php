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
            $this->url[0]=$seo['url'];
            $lista_productos = $this->lista_productos($productos_destacados,'detail','foto2');
            view::set('lista_productos', $lista_productos);
            view::set('col-md','col-md-6');
            view::set('col-lg','col-lg-4');
            $product_list=view::render('product/grid',false,true);
            view::set('product_list',$product_list);
            //view::set('title',$seo['titulo']);
            view::set('title', "Nuestros productos destacados");
            view::render('home-products');
        }

        $footer = new footer();
        $footer->normal();
    }
}
