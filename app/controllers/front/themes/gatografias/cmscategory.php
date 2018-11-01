<?php
namespace app\controllers\front\themes\gatografias;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seccion as seccion_model;
use \app\models\seccioncategoria as seccioncategoria_model;
use \app\models\seo;
use \core\functions;
use \core\view;

class cmscategory extends base
{
    public function __construct()
    {
        parent::__construct(seo::getById(2));
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

        $breadcrumb = new breadcrumb();
        $breadcrumb->normal($this->breadcrumb);

        $var = array();
        if ($this->seo['tipo_modulo'] != 0) {
            $var['tipo'] = $this->seo['tipo_modulo'];
        }
        if ($this->modulo['hijos']) {
            $var['idpadre'] = 0;
        }
        $row        = seccioncategoria_model::getAll($var);
        $categories = $this->lista($row);
        view::set('list', $categories);
        view::render('grid-border-3');

        $footer = new footer();
        $footer->normal();
    }

    public function detail($var = array())
    {
        if (isset($var[0])) {
            $id        = functions::get_idseccion($var[0]);
            $categoria = seccioncategoria_model::getById($id);
            if (isset($categoria[0])) {
                $this->url        = functions::url_seccion(array($this->url[0], 'detail'), $categoria, true);
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
        $banner->individual($this->seo['banner'], $this->seo['titulo']);

        $breadcrumb = new breadcrumb();
        $breadcrumb->normal($this->breadcrumb);

        view::set('title', $categoria['titulo']);
        view::set('description', $categoria['descripcion']);
        view::render('title-text');

        $var = array();
        if ($this->seo['tipo_modulo'] != 0) {
            $var['tipo'] = $this->seo['tipo_modulo'];
        }
        if ($this->modulo['hijos']) {
            $var['idpadre'] = $categoria[0];
        }
        $row        = seccioncategoria_model::getAll($var);
        $categories = $this->lista($row);
        view::set('list', $categories);
        view::render('grid-border-3');

        $var = array();
        if ($this->seo['tipo_modulo'] != 0) {
            $var['tipo'] = $this->seo['tipo_modulo'];
        }
        if ($this->modulo['hijos']) {
            $var[seccioncategoria_model::$idname] = $categoria[0];
        }
        $row = seccion_model::getAll($var);

        if (count($row) > 0) {
            view::set('title', 'Secciones');
            view::render('title');
            $secciones = $this->lista($row, 'sub', 'lista');
            view::set('list', $secciones);
            view::render('grid-3');
        }
        $footer = new footer();
        $footer->normal();
    }

    public function sub($var = array())
    {
        if (isset($var[0])) {
            $id      = functions::get_idseccion($var[0]);
            $seccion = seccion_model::getById($id);
            if (isset($seccion[0])) {
                $this->url = functions::url_seccion(array($this->url[0], 'sub'), $seccion, true);
                $this->breadcrumb[] = array('url' => functions::generar_url($this->url), 'title' => $seccion['titulo']);
            }
        }
        functions::url_redirect($this->url);
        $this->meta($seccion);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->seo['titulo']);

        $breadcrumb = new breadcrumb();
        $breadcrumb->normal($this->breadcrumb);

        view::set('title', $seccion['titulo']);
        view::set('description', $seccion['descripcion']);
        view::render('title-text');

        view::set('title', "Galería");
        view::render('title');
        $carousel = new carousel();
        $carousel->normal($seccion['foto'], $seccion['titulo']);

        $footer = new footer();
        $footer->normal();
    }
}
