<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\producto as producto_model;
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
        $pl           = new product_list(); //product_list.php
        $product_list = $pl->product_list(); //Lista de productos, renderiza vista
        $sidebar      = $pl->sidebar(); // genera sidebar, renderiza vista
        $pl->orden_producto(); // genera lista de filtros
        $pl->limit_producto(); //genera lista de cantidad de productos por pagina
        $pl->pagination(); // genera paginador
        $pl->is_search(); // Genera texto de busqueda, si existe

        view::set('product_list', $product_list);
        view::set('sidebar', $sidebar);
        view::render('product-category');

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
        $pl           = new product_list(); //product_list.php
        $product_list = $pl->product_list($categoria); //Lista de productos, renderiza vista
        $sidebar      = $pl->sidebar($categoria); // genera sidebar, renderiza vista
        $pl->orden_producto(); // genera lista de filtros
        $pl->limit_producto(); //genera lista de cantidad de productos por pagina
        $pl->pagination(); // genera paginador
        $pl->is_search(); // Genera texto de busqueda, si existe

        view::set('product_list', $product_list);
        view::set('sidebar', $sidebar);
        view::render('product-category');

        $footer = new footer();
        $footer->normal();
    }

    public function detail($var = array())
    {
        if (isset($var[0])) {
            $id       = functions::get_idseccion($var[0]);
            $producto = producto_model::getById($id);
            if (isset($producto[0])) {
                $this->url          = functions::url_seccion(array($this->url[0], 'detail'), $producto, true);
                $this->breadcrumb[] = array('url' => functions::generar_url($this->url), 'title' => $producto['titulo']);
            }
        }
        functions::url_redirect($this->url);
        $this->meta($producto);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $producto['titulo']);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);
        $pl      = new product_list(); //product_list.php
        $sidebar = $pl->sidebar(); // genera sidebar, renderiza vista
        $pd      = new product_detail($producto, $this->url);
        $tabs    = $pd->tabs();
        $pd->galeria();
        $pd->resumen();

        view::set('sidebar', $sidebar);
        view::set('tabs', $tabs);
        view::set('url', functions::generar_url($this->url));
        view::render('product-detail');

        $footer = new footer();
        $footer->normal();
    }

}
