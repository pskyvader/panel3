<?php
namespace app\controllers\front\themes\gatografias;

defined("APPPATH") or die("Acceso denegado");
use \app\models\galeria as galeria_model;
use \app\models\texto;
use \core\image;
use \core\functions;
use \core\view;

class galeria extends base
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
        $row= galeria_model::getAll($var);
        $galeria=array();
        foreach ($row as $key => $g) {
            $galeria[]=array(
                'foto'=>image::generar_url(image::portada($g['foto']),'foto1'),
                'original'=>image::generar_url(image::portada($g['foto']),''),
                'title'=>$g['titulo'],
                'subtitle'=>$g['subtitulo'],
                'url'=>functions::url_seccion(array($this->url[0], 'detail'), $g),
                'par'=>($key%2!=0)
            );
        }
        view::set('galeria',$galeria);
        view::render('gallery-zigzag');

        $footer = new footer();
        $footer->normal();
    }

    public function detail($var = array())
    {
        if (isset($var[0])) {
            $id      = functions::get_idseccion($var[0]);
            $seccion = galeria_model::getById($id);
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
        $banner->individual($this->seo['banner'], $this->metadata['title'],$seccion['subtitulo']);

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);
        
        view::set('text',$seccion['resumen']);
        view::render('text');

        $galeria=array();
        foreach ($seccion['foto'] as $key => $foto) {
            $galeria[]=array(
                'foto'=>image::generar_url($foto,'foto2'),
                'original'=>image::generar_url($foto,''),
            );
        }
        view::set('galeria',$galeria);
        view::set('title',$seccion['titulo']);
        view::render('gallery-grid4');

        $footer = new footer();
        $footer->normal();
    }
}
