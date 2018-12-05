<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\usuario as usuario_model;
use \core\app;
use \core\functions;
use \core\view;

class user extends base
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
        $banner->individual($this->seo['banner'], $this->metadata['title']);

        $footer = new footer();
        $footer->normal();
    }
    public function registro(){
        $this->meta($this->seo);
        $this->url[]='registro';
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Registro');
        view::render('registro');

        $footer = new footer();
        $footer->normal();

    }
    public function login(){
        $this->meta($this->seo);
        $this->url[]='login';
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Login');
        view::render('login');

        $footer = new footer();
        $footer->normal();
    }
    public function verificar()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $logueado  = usuario_model::verificar_sesion();
        if (!$logueado) {
            $prefix_site = functions::url_amigable(app::$_title);
            if (isset($_COOKIE['cookieusuario' . $prefix_site])) {
                $logueado = usuario_model::login_cookie($_COOKIE['cookieusuario' . $prefix_site]);
            }
        }
        $respuesta['exito'] = $logueado;
        if ($logueado) {
            $nombre=explode(" ",$_COOKIE['nombreusuario' . $prefix_site]);
            $respuesta['mensaje'] = $nombre[0];
        }
        echo json_encode($respuesta);
    }
}
