<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \core\functions;

class moduloconfiguracion extends base
{
    protected $url = array('moduloconfiguracion');
    protected $metadata = array('title' => 'Configuracion de modulos', 'modulo' => 'moduloconfiguracion');
    protected $tipos_mostrar = array(
        'action' => array('text' => 'Accion', 'value' => 'action'),
        'active' => array('text' => 'Active', 'value' => 'active'),
        'delete' => array('text' => 'Eliminar', 'value' => 'delete'),
        'image' => array('text' => 'Imagen', 'value' => 'image'),
        'link' => array('text' => 'Link', 'value' => 'link'),
        'text' => array('text' => 'Texto', 'value' => 'text'),
    );
    protected $tipos_detalle = array(
        'active' => array('text' => 'Active', 'value' => 'active'),
        'file' => array('text' => 'Archivo', 'value' => 'file'),
        'multiple_file' => array('text' => 'Archivo multiple', 'value' => 'multiple_file'),
        'recursive_checkbox' => array('text' => 'Arbol de botones checkbox', 'value' => 'recursive_checkbox'),
        'recursive_radio' => array('text' => 'Arbol de botones radio', 'value' => 'recursive_radio'),
        'password' => array('text' => 'Contraseña', 'value' => 'password'),
        'editor' => array('text' => 'Editor', 'value' => 'editor'),
        'email' => array('text' => 'Email', 'value' => 'email'),
        'image' => array('text' => 'Imagen', 'value' => 'image'),
        'multiple_image' => array('text' => 'Imagen multiple', 'value' => 'multiple_image'),
        'map' => array('text' => 'Mapa', 'value' => 'map'),
        'multiple' => array('text' => 'Multiple', 'value' => 'multiple'),
        'number' => array('text' => 'Numero', 'value' => 'number'),
        'text' => array('text' => 'Texto', 'value' => 'text'),
        'textarea' => array('text' => 'Texto largo', 'value' => 'textarea'),
        'token' => array('text' => 'Token', 'value' => 'token'),
        'url' => array('text' => 'URL', 'value' => 'url'),
    );
    public function __construct()
    {
        parent::__construct(new moduloconfiguracion_model);
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
            //'id' => array('title_th' => 'ID', 'field' => 0, 'type' => 'text'),
            'orden' => array('title_th' => 'Orden', 'field' => 'orden', 'type' => 'text'),
            'module' => array('title_th' => 'Modulo', 'field' => 'module', 'type' => 'text'),
            'titulo' => array('title_th' => 'Titulo', 'field' => 'titulo', 'type' => 'text'),
            'estado' => array('title_th' => 'Estado', 'field' => 'estado', 'type' => 'active'),
            'aside' => array('title_th' => 'Aparece en aside', 'field' => 'aside', 'type' => 'active'),
            //'tipos' => array('title_th' => 'Contiene tipos', 'field' => 'tipos', 'type' => 'active'),
            'copy' => array('title_th' => 'Copiar', 'field' => 0, 'type' => 'action', 'action' => 'copy', 'mensaje' => 'Copiando Elemento'),
            'editar' => array('title_th' => 'Editar', 'field' => 'url_detalle', 'type' => 'link'),
            'subseccion' => array('title_th' => 'Modulos', 'field' => 'url_subseccion', 'type' => 'link'),
            'delete' => array('title_th' => 'Eliminar', 'field' => 'delete', 'type' => 'delete'),
        );

        $list = new lista($this->metadata); //controlador de lista

        $where = array();
        $condiciones = array();
        $url_detalle = $this->url;
        $url_detalle[] = 'detail';
        $respuesta = $list->get_row($class, $where, $condiciones, $url_detalle); //obtener unicamente elementos de la pagina actual
        foreach ($respuesta['row'] as $key => $value) {
            $respuesta['row'][$key]['url_subseccion'] = functions::generar_url(array('modulo'), array($class::$idname => $value[0]));
        }
        $menu = array('new' => true, 'excel' => true);
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
            $this->metadata['title'] = 'Editar ' . $this->metadata['title'];
        } else {
            $id = 0;
            $this->metadata['title'] = 'Nuevo ' . $this->metadata['title'];
        }

        $this->breadcrumb[] = array('url' => functions::generar_url($this->url), 'title' => ($this->metadata['title']), 'active' => 'active');
        if (!administrador_model::verificar_sesion()) {
            $this->url = array_merge(array('login', 'index'), $this->url);
        }
        functions::url_redirect($this->url); //verificar sesion o redireccionar a login

        /* cabeceras y campos que se muestran en el detalle:
        titulo,campo de la tabla a usar, tipo (ver archivo detalle.php funcion "field") */

        $columnas_mostrar = array(
            'orden' => array('title_field' => 'Orden', 'field' => 'orden', 'type' => 'multiple_order', 'required' => true, 'col' => 1),
            'field' => array('title_field' => 'Campo', 'field' => 'field', 'type' => 'multiple_text', 'required' => true, 'col' => 2),
            'titulo' => array('title_field' => 'Titulo', 'field' => 'titulo', 'type' => 'multiple_text', 'required' => true, 'col' => 3),
            'tipo' => array('title_field' => 'Tipo', 'field' => 'tipo', 'type' => 'multiple_select', 'required' => true, 'option' => $this->tipos_mostrar, 'col' => 3),
            'button' => array('field' => '', 'type' => 'multiple_button', 'col' => 3),
        );
        $columnas_detalle = array(
            'orden' => array('title_field' => 'Orden', 'field' => 'orden', 'type' => 'multiple_order', 'required' => true, 'col' => 1),
            'field' => array('title_field' => 'Campo', 'field' => 'field', 'type' => 'multiple_text', 'required' => true, 'col' => 2),
            'titulo' => array('title_field' => 'Titulo', 'field' => 'titulo', 'type' => 'multiple_text', 'required' => true, 'col' => 3),
            'tipo' => array('title_field' => 'Tipo', 'field' => 'tipo', 'type' => 'multiple_select', 'required' => true, 'option' => $this->tipos_detalle, 'col' => 3),
            'button' => array('field' => '', 'type' => 'multiple_button', 'col' => 3),
        );
        $campos = array(
            'module' => array('title_field' => 'Modulo', 'field' => 'module', 'type' => 'url', 'required' => true, 'help' => 'Modulo asociado'),
            'titulo' => array('title_field' => 'Titulo', 'field' => 'titulo', 'type' => 'text', 'required' => true),
            'icono' => array('title_field' => 'Icono', 'field' => 'icono', 'type' => 'icon', 'required' => true, 'help' => 'Icono para barra lateral'),
            'sub' => array('title_field' => 'Sub seccion', 'field' => 'sub', 'type' => 'url', 'required' => false, 'help' => 'Modulo de subseccion, si existe'),
            'padre' => array('title_field' => 'Modulo padre', 'field' => 'padre', 'type' => 'text', 'required' => false, 'help' => 'Nombre del modulo padre, si existe'),
            'mostrar' => array('title_field' => 'Mostrar', 'field' => 'mostrar', 'type' => 'multiple', 'required' => true, 'columnas' => $columnas_mostrar),
            'detalle' => array('title_field' => 'Detalle', 'field' => 'detalle', 'type' => 'multiple', 'required' => true, 'columnas' => $columnas_detalle),
            'orden' => array('title_field' => 'Orden', 'field' => 'orden', 'type' => 'number', 'required' => true),
            'estado' => array('title_field' => 'Estado', 'field' => 'estado', 'type' => 'active', 'required' => true),
            'aside' => array('title_field' => 'Aside', 'field' => 'aside', 'type' => 'active', 'required' => true),
            'tipos' => array('title_field' => 'Contiene Tipos', 'field' => 'tipos', 'type' => 'active', 'required' => true),
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
}
