<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\producto as producto_model;
use \app\models\seo as seo_model;
use \core\image;
use \core\functions;
use \core\view;

class order extends base
{
    private $steps = array(1, 2, 3, 4, 5);
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo'], false);
    }
    public function index()
    {
        $this->meta($this->seo);
        $this->url[] = 'step';
        $this->url[] = '1';
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'], $this->seo['subtitulo']);

        $footer = new footer();
        $footer->normal();
    }
    public function step($var = array())
    {
        $error=false;
        $mensaje='';
        $this->meta($this->seo);
        $this->url[] = 'step';
        $current_step=1;
        if (isset($var[0]) && in_array($var[0], $this->steps)) {
            $current_step = $var[0];
        }
        $this->url[] = $current_step;
        $logueado = user::verificar(true);
        $carro=cart::current_cart(true);
        $seo_producto=seo_model::getById(8);
        if (!$logueado['exito']) {
            $_GET['next_url'] = implode('/', $this->url);
            $this->url        = array('cuenta', 'login');
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'], 'Paso 1: resumen del carro');
        if(count($carro)==0 || count($carro['productos'])==0){
            $mensaje="Tu carro está vacío. Por favor agrega productos para continuar tu compra";
            $error=true;
        }

        if($error){
            view::set('mensaje',$mensaje);
            view::render('order/error');
        }else{
            $attr = producto_model::getAll(array('tipo' => 2), array('order' => 'titulo ASC'));
            foreach ($attr as $key => $lp) {
                $portada               = image::portada($lp['foto']);
                $thumb_url             = image::generar_url($portada, 'cart');
                $attr[$key] = array('titulo' => $lp['titulo'], 'idproducto' => $lp['idproducto'], 'foto' => $thumb_url);
            }
            foreach ($carro['productos'] as $key => $p) {
                $carro['productos'][$key]['mensaje']= str_replace('<br />',"",$p['mensaje']);
                $atributos=$attr;
                foreach ($atributos as $k => $a) {
                    if($a['idproducto']==$p['idproductoatributo']){
                        $atributos[$k]['selected']=true;
                    }else{
                        $atributos[$k]['selected']=false;
                    }
                }
                $carro['productos'][$key]['atributos']=$atributos;
            }
            view::set_array($carro);
            view::set('url_product',functions::generar_url(array($seo_producto['url'])));
            view::set('url_next',functions::generar_url(array($this->url[0],'step',$current_step+1)));
            view::render('order/1-resumen');
        }

        $footer = new footer();
        $footer->normal();
    }
}
