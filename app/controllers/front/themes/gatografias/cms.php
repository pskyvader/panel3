<?php
namespace app\controllers\front\themes\gatografias;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seccion as seccion_model;
use \core\file;
use \core\functions;
use \core\view;

class cms extends base
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
        $banner->individual($this->seo['banner'], $this->metadata['title'],$this->seo['subtitulo']);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);

        $var = array();
        if ($this->seo['tipo_modulo'] != 0) {
            $var['tipo'] = $this->seo['tipo_modulo'];
        }
        if ($this->modulo['hijos']) {
            $var['idpadre'] = 0;
        }
        $row     = seccion_model::getAll($var);
        
        view::set('descripcion', $row[0]['descripcion']);
        view::render('cms');
       /* $sidebar = array();
        foreach ($row as $key => $s) {
            $sidebar[] = array('title' => $s['titulo'], 'active' => '', 'url' => functions::url_seccion(array($this->url[0], 'detail'), $s));
        }

        view::set('title_category', $this->seo['titulo']);
        view::set('sidebar', $sidebar);

        view::set('description', '');
        view::render('cms-sidebar');*/

        $footer = new footer();
        $footer->normal();
    }

    public function detail($var = array())
    {
        if (isset($var[0])) {
            $id      = functions::get_idseccion($var[0]);
            $seccion = seccion_model::getById($id);
            if (isset($seccion[0])) {
                $this->url          = functions::url_seccion(array($this->url[0], 'detail'), $seccion, true);
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

        $var = array();
        if ($this->seo['tipo_modulo'] != 0) {
            $var['tipo'] = $this->seo['tipo_modulo'];
        }
        if ($this->modulo['hijos']) {
            $var['idpadre'] = 0;
        }
        $row     = seccion_model::getAll($var);
        $sidebar = array();
        foreach ($row as $key => $s) {
            $sidebar[] = array('title' => $s['titulo'], 'active' => '', 'url' => functions::url_seccion(array($this->url[0], 'detail'), $s));
        }

        $extra='';
        if (count($seccion['archivo']) > 0) {
            $files = array();
            foreach ($seccion['archivo'] as $key => $a) {
                $files[] = array('title' => $a['url'], 'size' => functions::file_size(file::generar_dir($a, '')), 'url' => file::generar_url($a, ''));
            }
            view::set('files', $files);
            view::set('title', 'Archivos');
            $extra=view::render('files',false,true);
        }

        view::set('sidebar', $sidebar);
        view::set('title_category', $this->seo['titulo']);
        view::set('title', $seccion['titulo']);
        view::set('description', $seccion['descripcion']);
        view::set('extra', $extra);
        view::render('cms-sidebar');

        $footer = new footer();
        $footer->normal();
    }
}
