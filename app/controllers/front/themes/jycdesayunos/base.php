<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\modulo as modulo_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \app\models\seo as seo_model;
use \core\cache;
use \core\functions;
use \core\image;

class base
{
    protected $url        = array();
    protected $metadata   = array('title' => '', 'keywords_text' => '', 'description_text' => '');
    protected $breadcrumb = array();
    protected $modulo     = array();
    protected $seo        = array();
    public function __construct(int $idseo,bool $cache=true)
    {
        if(!$cache){
            cache::set_cache(false);
        }
        $this->seo               = seo_model::getById($idseo);
        $this->url               = array($this->seo['url']);
        $this->breadcrumb[]      = array('url' => functions::generar_url(array($this->seo['url'])), 'title' => $this->seo['titulo']);
        $this->metadata['image'] = image::generar_url(image::portada($this->seo['foto']), 'social');
        $this->metadata['class'] = (new \ReflectionClass($this))->getShortName();
        $moduloconfiguracion     = moduloconfiguracion_model::getByModulo($this->seo['modulo_back']);
        if (isset($moduloconfiguracion[0])) {
            $modulo = modulo_model::getAll(array('idmoduloconfiguracion' => $moduloconfiguracion[0], 'tipo' => $this->seo['tipo_modulo']), array('limit' => 1));
            if (isset($modulo[0])) {
                $this->modulo = $modulo[0];
            }
        }
    }
    public function meta($meta)
    {
        $this->metadata['title']            = (isset($meta['titulo']) && $meta['titulo'] != '') ? $meta['titulo'] : $this->metadata['title'];
        $this->metadata['keywords_text']    = (isset($meta['keywords']) && $meta['keywords'] != '') ? $meta['keywords'] : $this->metadata['keywords_text'];
        $this->metadata['description_text'] = (isset($meta['resumen']) && $meta['resumen'] != '') ? $meta['resumen'] : $this->metadata['description_text'];
        $this->metadata['description_text'] = (isset($meta['descripcion']) && $meta['descripcion'] != '') ? $meta['descripcion'] : $this->metadata['description_text'];
        $this->metadata['description_text'] = (isset($meta['metadescripcion']) && $meta['metadescripcion'] != '') ? $meta['metadescripcion'] : $this->metadata['description_text'];
        if(isset($meta['foto']) && $meta['foto']!=''){
            $social=image::generar_url(image::portada($meta['foto']), 'social');
            if($social!=''){
                $this->metadata['image'] = $social;
            }
        }
    }

    protected function lista($row, $url = 'detail', $recorte = 'foto1')
    {
        $lista = array();
        foreach ($row as $key => $v) {
            $portada = image::portada($v['foto']);
            $c       = array(
                'title'       => $v['titulo'],
                'image'       => image::generar_url($portada, $recorte),
                'description' => $v['resumen'],
                'srcset'      => array(),
                'url'         => functions::url_seccion(array($this->url[0], $url), $v),
            );
            $src = image::generar_url($portada, $recorte, 'webp');
            if ($src != '') {
                $c['srcset'][] = array('media' => '', 'src' => $src, 'type' => 'image/webp');
            }
            $lista[] = $c;
        }
        return $lista;
    }

    protected function lista_productos($row, $url = 'detail', $recorte = 'foto1')
    {
        $lista = array();
        foreach ($row as $key => $v) {
            $portada = image::portada($v['foto']);
            $c       = array(
                'id'          => $v[0],
                'title'       => $v['titulo'],
                'price'       => functions::formato_precio($v['precio']),
                'is_stock'       => ($v['stock']>0),
                'image'       => image::generar_url($portada, $recorte),
                'description' => strip_tags($v['resumen']),
                'srcset'      => array(),
                'url'         => functions::url_seccion(array($this->url[0], $url), $v),
            );
            $src = image::generar_url($portada, $recorte, 'webp');
            if ($src != '') {
                $c['srcset'][] = array('media' => '', 'src' => $src, 'type' => 'image/webp');
            }
            if ($c['image'] != "") {
                $lista[] = $c;
            }
        }
        return $lista;
    }
}
