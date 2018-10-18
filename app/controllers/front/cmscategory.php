<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seccioncategoria as seccioncategoria_model;
use \app\models\seo;
use \core\functions;
use \core\image;
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
        $banner->individual($this->seo['banner'][0], $this->metadata['title']);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);

        $var = array();
        if ($this->seo['tipo_modulo'] != 0) {
            $var['tipo'] = $this->seo['tipo_modulo'];
        }
        if ($this->modulo['hijos']) {
            $var['idpadre'] = 0;
        }
        $row = seccioncategoria_model::getAll($var);
        $categories = $this->categorias($row);
        view::set('list', $categories);
        view::render('grid3');

        $footer = new footer();
        $footer->normal();
    }

    public function detail($var = array())
    {
        if (isset($var[0])) {
            $categoria = seccioncategoria_model::getByUrl($var[0]);
            if (isset($categoria[0])) {
                $this->url = array($this->url[0], 'detail', $categoria['url']);
            }
        }
        functions::url_redirect($this->url);
        $this->meta($categoria);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'][0], $this->seo['titulo']);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);

        
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
        $row = seccioncategoria_model::getAll($var);
        $categories = $this->categorias($row);
        view::set('list', $categories);
        view::render('grid3');

        $footer = new footer();
        $footer->normal();
    }

    private function categorias($row)
    {
        $categories = array();
        foreach ($row as $key => $categoria) {
            $c = array(
                'title' => $categoria['titulo'],
                'url' => image::generar_url($categoria['foto'][0], 'foto1'),
                'description' => $categoria['resumen'],
                'srcset' => array(),
                'link' => functions::generar_url(array($this->url[0], 'detail', $categoria['url'])),
            );
            $src = image::generar_url($categoria['foto'][0], 'foto1', 'webp');
            if ($src != '') {
                $c['srcset'][] = array('media' => '', 'src' => $src, 'type' => 'image/webp');
            }
            $categories[] = $c;
        }
        return $categories;
    }
}
