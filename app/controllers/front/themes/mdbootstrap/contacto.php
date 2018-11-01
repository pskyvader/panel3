<?php
namespace app\controllers\front\themes\mdbootstrap;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seo;
use \app\models\texto;
use \core\app;
use \core\functions;
use \core\view;

class contacto extends base
{
    public function __construct()
    {
        parent::__construct(seo::getById(4));
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

        $breadcrumb = new breadcrumb();
        $breadcrumb->normal($this->breadcrumb);

        $campos   = array();
        $campos[] = array('campo' => 'input', 'type' => 'text', 'field' => 'nombre', 'title' => 'Nombre', 'required' => true);
        $campos[] = array('campo' => 'input', 'type' => 'email', 'field' => 'email', 'title' => 'Email', 'required' => true);
        $campos[] = array('campo' => 'input', 'type' => 'text', 'field' => 'telefono', 'title' => 'Teléfono', 'required' => false);
        $campos[] = array('campo' => 'input', 'type' => 'file', 'field' => 'archivo', 'title' => 'Archivo', 'required' => false);
        $campos[] = array('campo' => 'textarea', 'type' => 'text', 'field' => 'mensaje', 'title' => 'Comentario', 'required' => true);

        foreach ($campos as $key => $c) {
            $campos[$key]['is_required'] = $c['required'];
            $campos[$key]['is_input']    = ($c['campo'] == 'input');
            $campos[$key]['is_file']     = ($c['type'] == 'file');
            $campos[$key]['is_textarea'] = ($c['campo'] == 'textarea');
        }
        view::set('campos', $campos);

        $informacion = array();

        $t             = texto::getById(6);
        $informacion[] = array('icono' => 'fas fa-map-marker-alt2', 'title' => $t['titulo'], 'text' => $t['texto'], 'is_link' => false, 'url' => '');
        $t             = texto::getById(1);
        $informacion[] = array('icono' => 'fas fa-phone2', 'title' => $t['titulo'], 'text' => $t['texto'], 'is_link' => true, 'url' => 'tel:' . $t['texto']);
        $t             = texto::getById(2);
        $informacion[] = array('icono' => 'fas fa-envelope-open2', 'title' => $t['titulo'], 'text' => $t['texto'], 'is_link' => true, 'url' => 'mailto:' . $t['texto']);

        view::set('informacion', $informacion);
        view::set('texto_contacto', strip_tags((texto::getById(7)['descripcion'])));
        view::set('title', $this->seo['titulo']);
        view::set('action', functions::generar_url(array('enviar')));
        $mapa = texto::getById(8);
        view::set('lat', $mapa['mapa']['lat']);
        view::set('lng', $mapa['mapa']['lng']);
        view::set('title_map', $mapa['titulo']);
        view::set('direccion', $mapa['mapa']['direccion']);

        $config = app::getConfig();
        view::set('googlemaps_key', $config['googlemaps_key']);
        view::set('google_captcha', $config['google_captcha']);
        view::set('action', functions::generar_url(array('enviar')));
        view::render('contact');

        $footer = new footer();
        $footer->normal();
    }
}
