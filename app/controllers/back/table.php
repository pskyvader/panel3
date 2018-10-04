<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\table as table_model;
use \core\functions;

class table extends base
{
    protected $url = array('table');
    protected $metadata = array('title' => 'Tablas','modulo'=>'table');
    protected $tipos = array(
        'char(255)' => array('text' => 'Texto', 'value' => 'char(255)'),
        'int(11)' => array('text' => 'Numero', 'value' => 'int(11)'),
        'tinyint(1)' => array('text' => 'Bool', 'value' => 'tinyint(1)'),
        'longtext' => array('text' => 'Texto largo', 'value' => 'longtext'),
        'datetime' => array('text' => 'Fecha y hora', 'value' => 'datetime'),
    );
    public function __construct()
    {
        parent::__construct(new table_model);
    }
    public function index()
    {
        $class = $this->class; // Clase para enviar a controlador de lista

        if (!administrador_model::verificar_sesion()) {
            $this->url = array_merge(array('login', 'index'), $this->url);
        }
        functions::url_redirect($this->url); //verificar sesion o redireccionar a login

        /* cabeceras y campos que se muestran en la lista:
        titulo,campo de la tabla a usar, tipo (ver archivo lista.php funcion "field") */
        $th = array(
            'id' => array('title_th' => 'ID', 'field' => 0, 'type' => 'text'),
            'tablename' => array('title_th' => 'Titulo', 'field' => 'tablename', 'type' => 'text'),
            'truncate' => array('title_th' => 'Permite vaciar', 'field' => 'truncate', 'type' => 'active'),
            'validar' => array('title_th' => 'Validar', 'field' => 0, 'type' => 'action','action'=>'validar','mensaje'=>'Validando Tabla'),
            'generar' => array('title_th' => 'Generar mvc', 'field' => 0, 'type' => 'action','action'=>'generar','mensaje'=>'Generando mvc'),
            'copy' => array('title_th' => 'Copiar', 'field' => 0, 'type' => 'action','action'=>'copy','mensaje'=>'Copiando Elemento'),
            'editar' => array('title_th' => 'Editar', 'field' => 'url_detalle', 'type' => 'link'),
        );

        $list = new lista($this->metadata); //controlador de lista

        $where = array();
        $condiciones = array();
        $url_detalle = $this->url;
        $url_detalle[] = 'detail';
        $respuesta = $list->get_row($class, $where, $condiciones, $url_detalle); //obtener unicamente elementos de la pagina actual
        $menu = array('new' => true, 'edit' => true, 'delete' => true);

        $data = array( //informacion para generar la vista de la lista, arrays SIEMPRE antes de otras variables!!!!
            'breadcrumb' => $this->breadcrumb,
            'th' => $th,
            'current_url' => functions::generar_url($this->url),
            'new_url' => functions::generar_url($url_detalle),
        );
        $data = array_merge($data, $respuesta, $menu);
        $list->normal($data);
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
            $this->metadata['title'] = 'Editar';
        } else {
            $id = 0;
            $this->metadata['title'] = 'Nuevo';
        }

        $this->breadcrumb[] = array('url' => functions::generar_url($this->url), 'title' => ($this->metadata['title']), 'active' => 'active');
        if (!administrador_model::verificar_sesion()) {
            $this->url = array_merge(array('login', 'index'), $this->url);
        }
        functions::url_redirect($this->url); //verificar sesion o redireccionar a login

        /* cabeceras y campos que se muestran en el detalle:
        titulo,campo de la tabla a usar, tipo (ver archivo detalle.php funcion "field") */

        $columnas = array(
            'orden' => array('title_field' => 'Orden', 'field' => 'orden', 'type' => 'multiple_order', 'required' => true, 'col' => 2),
            'titulo' => array('title_field' => 'Titulo', 'field' => 'titulo', 'type' => 'multiple_text', 'required' => true, 'col' => 3),
            'tipo' => array('title_field' => 'Tipo', 'field' => 'tipo', 'type' => 'multiple_select', 'required' => true, 'option' => $this->tipos, 'col' => 3),
            'button' => array('field' => '', 'type' => 'multiple_button', 'col' => 4),
        );
        $campos = array(
            'tablename' => array('title_field' => 'Titulo', 'field' => 'tablename', 'type' => 'text', 'required' => true),
            'idname' => array('title_field' => 'ID tablas', 'field' => 'idname', 'type' => 'text', 'required' => true),
            'fields' => array('title_field' => 'Campos', 'field' => 'fields', 'type' => 'multiple', 'required' => true, 'columnas' => $columnas),
            'truncate' => array('title_field' => 'Permite vaciar', 'field' => 'truncate', 'type' => 'active', 'required' => true),
        );

        $detalle = new detalle($this->metadata); //controlador de detalle
        $row = ($id != 0) ? ($class::getById($id)) : array();

        $data = array( //informacion para generar la vista del detalle, arrays SIEMPRE antes de otras variables!!!!
            'breadcrumb' => $this->breadcrumb,
            'campos' => $campos,
            'row' => $row,
            'id' => ($id != 0) ? $id : '',
            'current_url' => functions::generar_url($this->url),
            'save_url' => functions::generar_url($url_save),
            'list_url' => functions::generar_url($url_list),
        );

        $detalle->normal($data, $class);
    }

    public function validar()
    {
        $campos = $_POST['campos'];
        $class=$this->class;
        $respuesta=$class::validate($campos['id']);
        echo json_encode($respuesta);
    }

    public function generar()
    {
        $campos = $_POST['campos'];
        $class=$this->class;
        $respuesta=$class::generar($campos['id']);
        echo json_encode($respuesta);
    }
}
