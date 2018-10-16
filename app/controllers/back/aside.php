<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use core\functions;
use core\image;
use core\view;
use \app\models\administrador as administrador_model;
use \app\models\modulo as modulo_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;

class aside
{
    public function normal()
    {
        if (!isset($_POST['ajax'])) {
            $prefix_site = functions::url_amigable(app::$_title);
            $administrador = administrador_model::getById($_SESSION[administrador_model::$idname . $prefix_site]);
            $tipo_admin = $administrador["tipo"];
            $moduloconfiguracion = moduloconfiguracion_model::getAll(array('estado' => true, 'aside' => true));
            $modulo = modulo_model::getAll();

            $mod = array();
            foreach ($modulo as $key => $m) {
                $mod[$m['idmoduloconfiguracion']][] = $m;
            }
            $current_url = functions::current_url();

            $menu = array();
            $url = functions::generar_url(array("home"), false);
            $active = ($url == $current_url);
            $menu[] = array('url' => $url, 'icon' => 'home', 'title' => 'Home', 'has_submenu' => false, 'active' => $active, 'separador' => false);

            foreach ($moduloconfiguracion as $key => $cm) {
                if (!isset($mod[$cm[0]])) {
                    if ($cm['module'] == 'separador') {
                        $menu[] = array('title' => $cm['titulo'], 'separador' => true);
                    }
                } elseif (count($mod[$cm[0]]) == 1) {
                    $modulo = $mod[$cm[0]][0];
                    $estados = $modulo['estado'][0]['estado'];
                    if ($estados[$tipo_admin] == 'true') {
                        $extra = array();
                        if ($cm['tipos']) {
                            $extra['tipo'] = $modulo['tipo'];
                        }
                        if ($modulo['hijos']) {
                            $extra['idpadre'] = 0;
                        }
                        if (count($extra) == 0) {
                            $extra = false;
                        }
                        $url = functions::generar_url(array($cm['module']), $extra);
                        $active = ($url == $current_url);
                        $menu[] = array('url' => $url, 'icon' => $cm['icono'], 'title' => $modulo['titulo'], 'has_submenu' => false, 'active' => $active, 'separador' => false);
                    }
                } else {
                    $active = false;
                    $me = array('icon' => $cm['icono'], 'title' => $cm['titulo'], 'has_submenu' => true, 'submenu' => array(), 'active' => $active, 'separador' => false);
                    foreach ($mod[$cm[0]] as $key => $m) {
                        $modulo = $m;
                        $estados = $modulo['estado'][0]['estado'];
                        if ($estados[$tipo_admin] == 'true') {
                            $extra = array();
                            if ($cm['tipos']) {
                                $extra['tipo'] = $modulo['tipo'];
                            }
                            if ($modulo['hijos']) {
                                $extra['idpadre'] = 0;
                            }
                            if (count($extra) == 0) {
                                $extra = false;
                            }
                            $url = functions::generar_url(array($cm['module']), $extra);
                            $active = ($url == $current_url);
                            $me['submenu'][] = array('url' => $url, 'sub_title' => $modulo['titulo'], 'active' => $active);
                            if ($active) {
                                $me['active'] = true;
                            }
                        }
                    }
                    if (count($me['submenu']) > 0) {
                        $menu[] = $me;
                    }
                }

            }

            if ($tipo_admin == 1) {
                $menu[] = array('title' => 'Solo para desarrollo', 'separador' => true);
                $url = functions::generar_url(array("moduloconfiguracion"), false);
                $active = ($url == $current_url);
                $menu[] = array('url' => $url, 'icon' => 'mode_edit', 'title' => 'Modulos', 'has_submenu' => false, 'active' => $active, 'separador' => false);

                $url = functions::generar_url(array("table"), false);
                $active = ($url == $current_url);
                $menu[] = array('url' => $url, 'icon' => 'table', 'title' => 'Tablas', 'has_submenu' => false, 'active' => $active, 'separador' => false);

                $url = functions::generar_url(array("configuracion_administrador"), false);
                $active = ($url == $current_url);
                $menu[] = array('url' => $url, 'icon' => 'settings_applications', 'title' => 'Configuracion Administrador', 'has_submenu' => false, 'active' => $active, 'separador' => false);

            }

            view::set('menu', $menu);

            view::set('name', $administrador["nombre"]);
            view::set('email', $administrador["email"]);
            view::set('url_admin', functions::generar_url(array("administrador", "detail", $administrador[0], 'profile'), array('tipo' => $tipo_admin)));
            view::set('img_admin', image::generar_url($administrador["foto"][0], 'profile', 'administrador', $administrador[0]));

            view::render('aside');
        }
    }

}
