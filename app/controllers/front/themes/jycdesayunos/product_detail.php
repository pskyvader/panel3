<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\productocategoria as productocategoria_model;
use \core\functions;
use \core\file;
use \core\image;
use \core\view;

class product_detail
{
    private $producto;
    private $url;
    public function __construct($producto,$url)
    {
        $this->producto = $producto;
        $this->url = $url;
    }

    public function galeria($recorte = 'foto1')
    {
        $lista_imagenes = array();
        $thumb = array();
        foreach ($this->producto['foto'] as $key => $foto) {
            $li = array('srcset' => array());
            $th=array();
            //$li['title'] = $this->producto['titulo'];
            $li['image'] = image::generar_url($foto, $recorte);
            $li['thumb']=$th['thumb'] = image::generar_url($foto, 'cart');
            $th['url'] = image::generar_url($foto, '');
            $src         = image::generar_url($foto, $recorte, 'webp');
            if ($src != '') {
                $li['srcset'][] = array('media' => '', 'src' => $src, 'type' => 'image/webp');
            }
            if ($li['image'] != '') {
                $lista_imagenes[] = $li;
                $thumb[] = $th;
            }
        }
        view::set('lista_imagenes', $lista_imagenes);
        view::set('thumb', $thumb);
    }
    public function tabs(){
        $extra='';
        if (count($this->producto['archivo']) > 0) {
            $files = array();
            foreach ($this->producto['archivo'] as $key => $a) {
                $files[] = array('title' => $a['url'], 'size' => functions::file_size(file::generar_dir($a, '')), 'url' => file::generar_url($a, ''));
            }
            view::set('files', $files);
            view::set('title', 'Archivos');
            $extra=view::render('files',false,true);
        }
        
        $is_description=(strip_tags($this->producto['descripcion'])!='');
        view::set('is_description', $is_description);
        view::set('description', $this->producto['descripcion']);
        
        $is_extra=($extra!='');
        view::set('is_extra', $is_extra);
        view::set('extra', $extra);
        if($is_description || $is_extra){
            $tabs=view::render('product-tabs',false,true);
        }else{
            $tabs="";
        }
        return $tabs;
    }
    public function resumen(){
        view::set('id', $this->producto[0]);
        view::set('title', $this->producto['titulo']);
        view::set('text', $this->producto['resumen']);
        view::set('price', functions::formato_precio($this->producto['precio']));
        view::set('stock', $this->producto['stock']);
        view::set('is_stock', ($this->producto['stock']>0));

        $row=$this->producto['idproductocategoria'];
        $categorias=array();
        foreach ($row as $key => $value) {
            $c=productocategoria_model::getById($value);
            $categorias[]=array('title'=>$c['titulo'],'url'=>functions::url_seccion(array($this->url[0], 'category'),$c),'is_final'=>($key+1==count($row)));
        }
        view::set('is_categoria', (count($categorias)>0));
        view::set('categorias', $categorias);
    }
}
