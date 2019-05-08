<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\comuna as comuna_model;
use \app\models\administrador as administrador_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
use \core\functions;
//use \core\image;

class comuna extends base
{
    protected $url = array('comuna');
    protected $metadata = array('title' => 'comuna','modulo'=>'comuna');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new comuna_model);
    }
    public function index()
    {
        $class = $this->class; // Clase para enviar a controlador de lista
        if ($this->contiene_tipos && !isset($_GET['tipo'])) {
            $this->url = array('home');
        }
        if ($this->contiene_hijos && !isset($_GET['idpadre'])) {
            $this->url = array('home');
        }

        if (!administrador_model::verificar_sesion()) {
            $this->url = array_merge(array('login', 'index'), $this->url);
        }
        functions::url_redirect($this->url); //verificar sesion o redireccionar a login

        /* cabeceras y campos que se muestran en la lista:
        titulo,campo de la tabla a usar, tipo (ver archivo lista.php funcion "field") */

        $list = new lista($this->metadata); //controlador de lista
        $configuracion = $list->configuracion($this->metadata['modulo']);
        $list->head();

        $where = array();
        if ($this->contiene_tipos) {
            $where['tipo'] = $_GET['tipo'];
        }
        if ($this->contiene_hijos) {
            $where['idpadre'] = $_GET['idpadre'];
        }
        if (isset($this->class_parent)) {
            $class_parent = $this->class_parent;
                
            if (isset($_GET[$class_parent::$idname])) {
                $where[$class_parent::$idname] = $_GET[$class_parent::$idname];
            }
        }
        $condiciones = array('order'=>'titulo ASC');
        $url_detalle = $this->url;
        $url_detalle[] = 'detail';
        $respuesta = $list->get_row($class, $where, $condiciones, $url_detalle); //obtener unicamente elementos de la pagina actual

        if (isset($configuracion['th']['copy'])) {
            $configuracion['th']['copy']['action'] = $configuracion['th']['copy']['field'];
            $configuracion['th']['copy']['field'] = 0;
            $configuracion['th']['copy']['mensaje'] = 'Copiando';
        }

        if ($this->contiene_hijos) {
            if ($this->contiene_tipos) {
                foreach ($respuesta['row'] as $k => $v) {
                    $respuesta['row'][$k]['url_children'] = functions::generar_url($this->url, array('idpadre' => $v[0], 'tipo' => $_GET['tipo']));
                }
            } else {
                foreach ($respuesta['row'] as $k => $v) {
                    $respuesta['row'][$k]['url_children'] = functions::generar_url($this->url, array('idpadre' => $v[0]));
                }
            }
        } else {
            unset($configuracion['th']['url_children']);
        }

        if ($this->sub != '') {
            if ($this->contiene_tipos) {
                foreach ($respuesta['row'] as $k => $v) {
                    $respuesta['row'][$k]['url_sub'] = functions::generar_url(array($this->sub), array($class::$idname => $v[0], 'tipo' => $_GET['tipo']));
                }
            } else {
                foreach ($respuesta['row'] as $k => $v) {
                    $respuesta['row'][$k]['url_sub'] = functions::generar_url(array($this->sub), array($class::$idname => $v[0]));
                }
            }
        } else {
            unset($configuracion['th']['url_sub']);
        }

        $data = array( //informacion para generar la vista de la lista, arrays SIEMPRE antes de otras variables!!!!
            'breadcrumb' => $this->breadcrumb,
            'th' => $configuracion['th'],
            'current_url' => functions::generar_url($this->url),
            'new_url' => functions::generar_url($url_detalle),
        );
        $data = array_merge($data, $respuesta, $configuracion['menu']);

        $list->normal($data);
    }
}
