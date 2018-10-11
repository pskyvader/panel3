<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \core\functions;

class administrador extends base
{
    protected $url = array('administrador');
    protected $metadata = array('title' => 'Administradores', 'modulo' => 'administrador');
    public function __construct()
    {
        parent::__construct(new administrador_model);
    }

    public function detail($var = array())
    {
        $class = $this->class; // Clase para enviar a controlador de detalle
        $url_save = $url_list = $this->url;
        $url_save[] = 'guardar';
        $this->url[] = 'detail';
        if (isset($var[0])) {
            $id = (int) $var[0];
            $this->url[] = $id;
            $this->metadata['title'] = 'Editar '.$this->metadata['title'] ;
        } else {
            $id = 0;
            $this->metadata['title'] = 'Nuevo '.$this->metadata['title'] ;
        }
        $profile = false;

        if (isset($var[1]) && $var[1] == 'profile') {
            $this->url[] = 'profile';
            $profile = true;
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

        $detalle = new detalle($this->metadata); //controlador de detalle
        $configuracion = $detalle->configuracion($this->metadata['modulo'], $profile);
        $row = ($id != 0) ? ($class::getById($id)) : array();
        if ($this->contiene_tipos) {
            $configuracion['campos']['tipo'] = array('title_field' => 'tipo', 'field' => 'tipo', 'type' => 'hidden', 'required' => true);
            $row['tipo'] = $_GET['tipo'];
        }
        if ($id != 0) {
            $configuracion['campos']['pass']['required'] = false;
        }

        $data = array( //informacion para generar la vista del detalle, arrays SIEMPRE antes de otras variables!!!!
            'breadcrumb' => $this->breadcrumb,
            'campos' => $configuracion['campos'],
            'row' => $row,
            'id' => ($id != 0) ? $id : '',
            'current_url' => functions::generar_url($this->url),
            'save_url' => functions::generar_url($url_save),
            'list_url' => functions::generar_url($url_list),
        );

        $detalle->normal($data, $class);
    }
}
