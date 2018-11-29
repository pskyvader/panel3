<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\productocategoria as productocategoria_model;
use \core\functions;
use \core\view;

class product extends base
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

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title']);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);
        $pl = new product_list(); //product_list.php
        $pl->product_list(); //Lista de productos, renderiza vista (por lo tanto debe ir al principio para no pisar variables dela vista general)
        $pl->sidebar(); // genera sidebar
        $pl->orden_producto(); // genera lista de filtros
        $pl->limit_producto(); //genera lista de cantidad de productos por pagina
        $pl->pagination(); // genera paginador
        view::render('product-sidebar');

        $footer = new footer();
        $footer->normal();
    }

    public function category($var = array())
    {
        if (isset($var[0])) {
            $id        = functions::get_idseccion($var[0]);
            $categoria = productocategoria_model::getById($id);
            if (isset($categoria[0])) {
                $this->url          = functions::url_seccion(array($this->url[0], 'category'), $categoria, true);
                $this->breadcrumb[] = array('url' => functions::generar_url($this->url), 'title' => $categoria['titulo']);
            }
        }
        functions::url_redirect($this->url);
        $this->meta($categoria);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $categoria['titulo']);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);
        $pl = new product_list();
        $pl->product_list($categoria);
        $pl->sidebar($categoria);
        $pl->orden_producto();
        $pl->limit_producto();
        $pl->pagination();
        view::render('product-sidebar');

        $footer = new footer();
        $footer->normal();
    }

}
