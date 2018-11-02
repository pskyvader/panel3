<?php
namespace app\controllers\front\themes\gatografias;

defined("APPPATH") or die("Acceso denegado");
use \app\models\logo as logo_model;
use \app\models\modulo as modulo_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \app\models\seo;
use \app\models\texto;
use \core\app;
use \core\functions;
use \core\image;
use \core\view;

class header
{
    public function normal()
    {
        if (!isset($_POST['ajax'])) {

            $data = array();
            $data['header-top'] = $this->header_top();
            //$data['header-top'] = '';
            $data['menu'] = $this->menu();

            $config = app::getConfig();
            $logo = logo_model::getById(5);
            $data['logo'] = image::generar_url($logo['foto'][0], 'sitio');
            $seo = seo::getById(1);
            $data['path'] = functions::generar_url(array($seo['url']));
            $data['title'] = $config['title'];
            view::set_array($data);
            view::render('header');
        }
    }
    private function header_top(){

        $telefono = texto::getById(1);
        view::set('telefono', $telefono['texto']);
        $email = texto::getById(2);
        view::set('email', $email['texto']);
        $redes_sociales = array();
        $rss=texto::getAll(array('tipo'=>2));
        foreach ($rss as $key => $r) {
            $redes_sociales[] = array('url' => functions::ruta($r['url']), 'icon' => $r['texto'], 'title' => $r['titulo']);
        }

        view::set('social', $redes_sociales);

        view::set('is_social', (count($redes_sociales) > 0));
        return view::render('header-top', false, true);
    }
    private function menu()
    {
        $lista_menu = array();
        $seo = seo::getAll();
        foreach ($seo as $key => $s) {
            if ($s['submenu'] && $s['modulo_back'] != '' && $s['modulo_back'] != 'none') {
                if($s['menu']){
                    $url=functions::generar_url(array($s['url']));
                }else{
                    $url='';
                }
                $menu = array('titulo' => $s['titulo'], 'link' => $url, 'active' => $s['url']);
                $moduloconfiguracion = moduloconfiguracion_model::getByModulo($s['modulo_back']);
                if (isset($moduloconfiguracion[0])) {
                    $modulo = modulo_model::getAll(array('idmoduloconfiguracion' => $moduloconfiguracion[0], 'tipo' => $s['tipo_modulo']), array('limit' => 1));
                    if (isset($modulo[0])) {
                        $c = '\app\models\\' . $s['modulo_back'];
                        $class = new $c;
                        $var = array();
                        if ($s['tipo_modulo'] != 0) {
                            $var['tipo'] = $s['tipo_modulo'];
                        }
                        if (isset($modulo[0]['hijos']) && $modulo[0]['hijos']) {
                            $var['idpadre'] = 0;
                        }
                        $row = $class::getAll($var);
                        $hijos = array();
                        foreach ($row as $key => $sub) {
                            $hijos[] = array('titulo' => $sub['titulo'], 'link' => functions::url_seccion(array($s['url'], 'detail'), $sub), 'active' => $sub['url']);
                        }
                        $menu['hijo'] = $hijos;
                    }
                }

                $lista_menu[] = $menu;
            } else {
                if($s['menu']){
                    $lista_menu[] = array('titulo' => $s['titulo'], 'link' => functions::generar_url(array($s['url'])), 'active' => $s['url']);
                }
            }
        }

        $menu = $this->generar_menu($lista_menu);

        return $menu;
    }

    private function generar_menu($lista_menu, $nivel = 0, $simple = false)
    {
        $menu_final = '';
        $nivel_maximo_hijo = 2;
        foreach ($lista_menu as $key => $menu) {
            $data = array('hijos' => '');
            $data['contiene_hijo'] = $data['contiene_hijoa'] = ($nivel < $nivel_maximo_hijo && !$simple && isset($menu['hijo']) && count($menu['hijo']) > 0);
            if ($data['contiene_hijo']) {
                $data['hijos'] = $this->generar_menu($menu['hijo'], $nivel + 1, $simple);
            }
            $data['target'] = (isset($menu['target'])) ? 'target="' . $menu['target'] . '" rel="noopener noreferrer"' : '';
            $data['active'] = ($nivel == 0 && !$simple && functions::active($menu['active'])) ? 'active' : '';
            $data['nivel0']=($nivel == 0);
            $data['nivel1']=($nivel == 1);
            $data['prefetch'] = ($nivel == 0 && !$simple);
            $data['is_url'] = ($menu['link'] != '');
            $data['no_url'] = !$data['is_url'];
            $data['url'] = $menu['link'];
            $data['title'] = $menu['titulo'];
            
            $data['nivel'] = $nivel;
            
            $data['key'] = $key;
            view::set_array($data);
            $menu_final .= view::render('menu', false, true);
        }
        return $menu_final;
    }
}
