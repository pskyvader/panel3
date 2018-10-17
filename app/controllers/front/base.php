<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \app\models\banner as banner_model;
use \app\models\modulo as modulo_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \core\functions;
use \core\image;
use \core\view;

class base
{
    protected $url = array();
    protected $metadata = array('title' => '','keywords_text'=>'','description_text'=>'');
    protected $breadcrumb = array();
    protected $modulo = array();
    protected $seo = array();
    public function __construct($seo)
    {
        $this->seo = $seo;
        $this->url = array($this->seo['url']);
        $this->metadata['image'] = image::generar_url($this->seo['foto'][0],'social');
        $moduloconfiguracion = moduloconfiguracion_model::getByModulo($this->seo['modulo_back']);
        if (isset($moduloconfiguracion[0])) {
            $modulo = modulo_model::getAll(array('idmoduloconfiguracion' => $moduloconfiguracion[0], 'tipo' => $this->seo['tipo_modulo']),array('limit'=>1));
            if (isset($modulo[0])) {
                $this->modulo=$modulo[0];
            }
        }
    }
    public function meta($meta){
        $this->metadata['title'] =(isset($meta['titulo']) && $meta['titulo']!='')?$meta['titulo']:$this->metadata['title'] ;
        $this->metadata['keywords_text']=(isset($meta['keywords']) && $meta['keywords']!='')?$meta['keywords']:$this->metadata['keywords_text'];
        $this->metadata['description_text']=(isset($meta['resumen']) && $meta['resumen']!='')?$meta['resumen']:$this->metadata['description_text'];
        $this->metadata['description_text']=(isset($meta['descripcion']) && $meta['descripcion']!='')?$meta['descripcion']:$this->metadata['description_text'];
        $this->metadata['description_text']=(isset($meta['metadescripcion']) && $meta['metadescripcion']!='')?$meta['metadescripcion']:$this->metadata['description_text'];
    }
    public function index()
    {
        $this->meta($this->seo);
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        //$breadcrumb = new breadcrumb();
        //$breadcrumb->normal($this->breadcrumb);

        $banner = new banner();
        $banner->individual($this->seo['banner'][0],$this->metadata['title']);
        $var=array();
        if($this->seo['tipo_modulo']!=0){
            $var['tipo']=$this->seo['tipo_modulo'];
        }
        if($this->modulo['hijos']){
            $var['idpadre'] = 0;
        }
        $row=seccioncategoria_model::getAll($var);
        $categories=array();
        foreach ($row as $key => $categoria) {
            $categories[]=array('title'=>$categoria['titulo'],'url'=>$categoria['foto'][0],'description'=>$categoria['descripcion']);
        }
        view::set('categories',$categories);
        view::render('cms-category');

        $footer = new footer();
        $footer->normal();
    }
}
