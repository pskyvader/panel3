<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");

use \core\app;
use \core\functions;
use \core\view;

class sw
{
    public function index()
    {
        $version_application=1;
        $config = app::getConfig();

        $lista_cache = array();
        $lista_cache[] = functions::generar_url(array("application","index",$version_application), false);

        $css = view::css(false, true, true); //array(css,fecha modificacion mas reciente)
        $js = view::js(true, true); //array(js,fecha modificacion mas reciente)
        foreach ($css[0] as $key => $c) {
            $lista_cache[] = $c['url'];
        }
        foreach ($js[0] as $key => $j) {
            $lista_cache[] = $j['url'];
        }
        
        
        view::set('lista_cache',functions::encode_json($lista_cache));
        view::set('cache',true);
        view::set('version',$js[1].'-'.$css[1]);
        header('Content-Type: application/javascript');
        view::render('sw',false);
    }
}
