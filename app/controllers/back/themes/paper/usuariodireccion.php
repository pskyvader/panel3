<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\usuariodireccion as usuariodireccion_model;
use \app\models\administrador as administrador_model;
use \app\models\comuna as comuna_model;
use \app\models\table;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
use \core\functions;
//use \core\image;

class usuariodireccion extends base
{
    protected $url = array('usuariodireccion');
    protected $metadata = array('title' => 'usuariodireccion','modulo'=>'usuariodireccion');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new usuariodireccion_model);
    }
    public function detail($var = array())
    {
        $class       = $this->class; // Clase para enviar a controlador de detalle
        $url_save    = $url_list    = $this->url;
        $url_save[]  = 'guardar';
        $this->url[] = 'detail';
        if (isset($var[0])) {
            $id                      = (int) $var[0];
            $this->url[]             = $id;
            $this->metadata['title'] = 'Editar ' . $this->metadata['title'];
        } else {
            $id                      = 0;
            $this->metadata['title'] = 'Nuevo ' . $this->metadata['title'];
        }

        $this->breadcrumb[] = array('url' => functions::generar_url($this->url), 'title' => ($this->metadata['title']), 'active' => 'active');
        if ($this->contiene_tipos && !isset($_GET['tipo'])) {
            $this->url = array('home');
        }
        if (!administrador_model::verificar_sesion()) {
            $this->url = array_merge(array('login', 'index'), $this->url);
        }
        functions::url_redirect($this->url); //verificar sesion o redireccionar a login

        /* cabeceras y campos que se muestran en el detalle:
        titulo,campo de la tabla a usar, tipo (ver archivo detalle.php funcion "field") */

        $detalle       = new detalle($this->metadata); //controlador de detalle
        $configuracion = $detalle->configuracion($this->metadata['modulo']);
        $row           = ($id != 0) ? ($class::getById($id)) : array();
        if ($this->contiene_tipos) {
            $configuracion['campos']['tipo'] = array('title_field' => 'tipo', 'field' => 'tipo', 'type' => 'hidden', 'required' => true);
            $row['tipo']                     = $_GET['tipo'];
        }
        if ($this->contiene_hijos && isset($configuracion['campos']['idpadre'])) {
            $categorias = $class::getAll();
            foreach ($categorias as $key => $c) {
                if ($c[0] == $id) {
                    unset($categorias[$key]);
                    break;
                }
            }
            $raiz = array(0, 'titulo' => 'Raíz', 'idpadre' => array(-1));
            array_unshift($categorias, $raiz);
            $configuracion['campos']['idpadre']['parent'] = functions::crear_arbol($categorias, -1);
        } else if ($this->contiene_hijos || isset($configuracion['campos']['idpadre'])) {
            $configuracion['campos']['idpadre'] = array('title_field' => 'idpadre', 'field' => 'idpadre', 'type' => 'hidden', 'required' => true);
            if ($id == 0) {
                if (isset($_GET['idpadre'])) {
                    $row['idpadre'] = functions::encode_json(array((string) $_GET['idpadre']));
                } else {
                    $row['idpadre'] = functions::encode_json(array('0'));
                }
            }
        } else {
            unset($configuracion['campos']['idpadre']);
        }

        if (isset($this->class_parent)) {
            $class_parent = $this->class_parent;
            $idparent     = $class_parent::$idname;

            $is_array = true;
            $fields   = table::getByname($class::$table);
            if (isset($fields[$idparent]) && $fields[$idparent]['tipo'] != 'longtext') {
                $is_array = false;
            }
            if (isset($configuracion['campos'][$idparent])) {
                $categorias = $class_parent::getAll();
                if ($is_array) {
                    $configuracion['campos'][$idparent]['parent'] = functions::crear_arbol($categorias);
                } else {
                    $configuracion['campos'][$idparent]['parent'] = $categorias;
                }
            } else {
                $configuracion['campos'][$idparent] = array('title_field' => $idparent, 'field' => $idparent, 'type' => 'hidden', 'required' => true);
                if ($id == 0) {
                    if (isset($_GET[$idparent])) {
                        if ($is_array) {
                            $row[$idparent] = functions::encode_json(array((string) $_GET[$idparent]));
                        } else {
                            $row[$idparent] = (int) $_GET[$idparent];
                        }

                    } else {
                        if ($is_array) {
                            $row[$idparent] = functions::encode_json(array('0'));
                        } else {
                            $row[$idparent] = 0;
                        }
                    }
                } else {
                    if ($is_array) {
                        $row[$idparent] = functions::encode_json($row[$idparent]);
                    } else {
                        $row[$idparent] = $row[$idparent];
                    }
                }
            }
        }
        if(isset($configuracion['campos']['idcomuna'])){
            $comunas=comuna_model::getAll(array(),array('order'=>'titulo ASC'));
            $configuracion['campos']['idcomuna']['parent']=$comunas;
        }

        $data = array( //informacion para generar la vista del detalle, arrays SIEMPRE antes de otras variables!!!!
            'breadcrumb'  => $this->breadcrumb,
            'campos'      => $configuracion['campos'],
            'row'         => $row,
            'id'          => ($id != 0) ? $id : '',
            'current_url' => functions::generar_url($this->url),
            'save_url'    => functions::generar_url($url_save),
            'list_url'    => functions::generar_url($url_list),
        );

        $detalle->normal($data, $class);
    }
}
