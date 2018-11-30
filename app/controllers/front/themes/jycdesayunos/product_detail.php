<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \core\functions;
use \core\image;
use \core\view;

class product_detail extends base
{
    private $producto;
    public function __construct($producto)
    {
        $this->producto = $producto;
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
    public function resumen(){
        view::set('id', $this->producto[0]);
        view::set('title', $this->producto['titulo']);
        view::set('text', $this->producto['resumen']);
        view::set('price', functions::formato_precio($this->producto['precio']));
        view::set('description', $this->producto['descripcion']);
    }
}
