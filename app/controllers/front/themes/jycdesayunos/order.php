<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
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
        if (isset($var[0]) && in_array($var[0], $this->steps)) {
            $this->url[] = $var[0];
        } else {
            $this->url[] = '1';
        }
        $logueado = user::verificar(true);
        $carro=cart::current_cart(true);
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
            view::set_array($carro);
            view::render('order/1-resumen');
        }

        $footer = new footer();
        $footer->normal();
    }
}
