<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use \app\models\modulo as modulo_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \core\functions;
use \core\image;
use \core\view;

class detalle
{

    private $metadata = array('title' => '');
    private $templates = array();

    public function __construct($metadata)
    {
        foreach ($metadata as $key => $value) {
            $this->metadata[$key] = $value;
        }
        $list_dir = view::get_theme() . 'detail/';
        $files = scandir($list_dir);
        foreach ($files as $file) {
            $nombre = explode(".", $file);
            $extension = strtolower(array_pop($nombre));
            if ($extension == 'html') {
                $html = file_get_contents($list_dir . $file);
                $this->templates[implode('.', $nombre)] = $html;
            }
        }
    }
    public function normal($data)
    {
        $campos = $data['campos'];
        unset($data['campos']);
        $row_data = $data['row'];
        $row = array();
        foreach ($campos as $k => $v) {
            $content = $this->field($v, $row_data);
            $row[] = array('content' => $content, 'content_field' => $v['field'], 'class' => ($v['type'] == 'hidden') ? 'hidden' : '');
        }

        $data['row'] = $row;
        $data['title'] = $this->metadata['title'];

        $head = new head($this->metadata);
        $head->normal();
        $header = new header();
        $header->normal();
        $aside = new aside();
        $aside->normal();

        view::set_array($data);
        view::render('detail');

        $footer = new footer();
        $footer->normal();
    }

    public function ajax()
    {

    }

    public static function configuracion($modulo, $force = false)
    {
        $prefix_site = functions::url_amigable(app::$_title);
        $tipo_admin = $_SESSION["tipo" . $prefix_site];
        $moduloconfiguracion = moduloconfiguracion_model::getByModulo($modulo);
        $var = array('idmoduloconfiguracion' => $moduloconfiguracion[0]);
        if (isset($_GET['tipo'])) {
            $var['tipo'] = $_GET['tipo'];
        }
        $modulo = modulo_model::getAll($var, array('limit' => 1));
        $modulo = $modulo[0];
        $estados = $modulo['estado'][0]['estado'];
        if ($estados[$tipo_admin] != 'true' && !$force) {
            functions::url_redirect(array('home'));
        }
        $campos = array();
        foreach ($modulo['detalle'] as $key => $m) {
            if ($m['estado'][$tipo_admin] == 'true') {
                $campos[$m['field']] = array('title_field' => $m['titulo'], 'field' => $m['field'], 'type' => $m['tipo'], 'required' => ($m['required'] == 'true') ? true : false, 'help' => $m['texto_ayuda']);
            }
        }

        return array('campos' => $campos);
    }

    private function field($campos, $fila, $parent = '', $idparent = 0, $level = 0)
    {
        switch ($campos['type']) {
            case 'active':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'active' => (isset($fila[$campos['field']])) ? (string) $fila[$campos['field']] : '',
                    'class' => (isset($fila[$campos['field']])) ? (($fila[$campos['field']]) ? 'btn-success' : 'btn-danger') : 'btn-default',
                    'icon' => (isset($fila[$campos['field']])) ? (($fila[$campos['field']]) ? 'fa-check' : 'fa-close') : 'fa-question-circle',
                );
                break;
            case 'editor':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                );
                break;
            case 'multiple':
                $fields = array();
                $count = (isset($fila[$campos['field']]) && is_array($fila[$campos['field']])) ? count($fila[$campos['field']]) : 0;
                if ($count > 0) {
                    foreach ($fila[$campos['field']] as $key => $f) {
                        $td = array();
                        foreach ($campos['columnas'] as $k => $v) {
                            $content = $this->field($v, $f, $campos['field'], $key);
                            $td[] = array('content' => $content, 'content_field' => $v['field']);
                        }
                        $linea = array('columna' => $td);
                        $fields[] = $linea;
                    }
                    $new_field = false;
                } else {
                    $new_field = true;
                }
                $new_line = array();
                foreach ($campos['columnas'] as $k => $v) {
                    $content = $this->field($v, array(), $campos['field']);
                    $new_line[] = array('content' => $content, 'content_field' => $v['field']);
                }

                $data = array(
                    'fields' => $fields,
                    'count' => $count,
                    'new_field' => $new_field,
                    'new_line' => $new_line,
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                );
                break;
            case 'multiple_text':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'parent' => $parent,
                    'col' => $campos['col'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                );
                break;
            case 'multiple_number':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'parent' => $parent,
                    'col' => $campos['col'],
                    'max' => $campos['max'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : $campos['default'],
                );
                break;
            case 'multiple_label':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'parent' => $parent,
                    'col' => $campos['col'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                );
                break;
            case 'multiple_hidden':
                $data = array(
                    'field' => $campos['field'],
                    'parent' => $parent,
                    'required' => ($campos['required']) ? 'required' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                );
                break;
            case 'multiple_select':
                foreach ($campos['option'] as $key => $option) {
                    $campos['option'][$key]['selected'] = (isset($fila[$campos['field']]) && $fila[$campos['field']] == $option['value']) ? 'selected="selected"' : '';
                }
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'parent' => $parent,
                    'col' => $campos['col'],
                    'option' => $campos['option'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required' : '',
                );
                break;
            case 'multiple_button':
                $data = array(
                    'col' => $campos['col'],
                );
                break;
            case 'multiple_order':
                $data = array(
                    'col' => $campos['col'],
                );
                break;
            case 'multiple_active':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'parent' => $parent,
                    'col' => $campos['col'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required' : '',
                    'active' => (isset($fila[$campos['field']])) ? (string) $fila[$campos['field']] : '',
                    'class' => (isset($fila[$campos['field']])) ? (($fila[$campos['field']] == 'true') ? 'btn-success' : 'btn-danger') : 'btn-default',
                    'icon' => (isset($fila[$campos['field']])) ? (($fila[$campos['field']] == 'true') ? 'fa-check' : 'fa-close') : 'fa-question-circle',
                );
                break;
            case 'multiple_active_array':
                $array = array();
                foreach ($campos['array'] as $key => $value) {
                    $campos['array'][$key]['active'] = (isset($fila[$campos['field']][$key])) ? (string) $fila[$campos['field']][$key] : 'true';
                    $campos['array'][$key]['class'] = (isset($fila[$campos['field']][$key])) ? (($fila[$campos['field']][$key] == 'true') ? 'btn-success' : 'btn-danger') : 'btn-success';
                    $campos['array'][$key]['icon'] = (isset($fila[$campos['field']][$key])) ? (($fila[$campos['field']][$key] == 'true') ? 'fa-check' : 'fa-close') : 'fa-check';
                }
                $data = array(
                    'title_field' => $campos['title_field'],
                    'array' => $campos['array'],
                    'field' => $campos['field'],
                    'idparent' => $idparent,
                    'parent' => $parent,
                    'col' => $campos['col'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required' : '',
                );
                break;
            case 'image':
                $folder = $this->metadata['modulo'];
                $image_url = (isset($fila[$campos['field']]) && isset($fila[$campos['field']][0])) ? (image::generar_url($fila[$campos['field']][0], 'thumb')) : '';
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'is_required_modal' => ($image_url != '') ? $campos['required'] : true,
                    'is_required_alert' => ($image_url != '') ? $campos['required'] : true,
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'image' => $image_url,
                    'is_image' => ($image_url != '') ? true : false,
                    'url' => ($image_url != '') ? $fila[$campos['field']][0]['url'] : '',
                    'parent' => ($image_url != '') ? $fila[$campos['field']][0]['parent'] : '',
                    'folder' => ($image_url != '') ? $fila[$campos['field']][0]['folder'] : '',
                    'help' => $campos['help'],
                );
                break;
            case 'multiple_image':
                $folder = $this->metadata['modulo'];
                $fields = array();
                if (isset($fila[$campos['field']])) {
                    foreach ($fila[$campos['field']] as $key => $campo) {
                        $field = $campo;
                        $field['title_field'] = $campos['title_field'];
                        $field['field'] = $campos['field'];
                        $field['image'] = image::generar_url($campo, 'thumb');
                        $field['active'] = $campo['portada'];
                        $field['class'] = ($campo['portada'] == 'true') ? 'btn-success' : 'btn-danger';
                        $field['icon'] = ($campo['portada'] == 'true') ? 'fa-check' : 'fa-close';
                        $fields[] = $field;
                    }
                }

                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'help' => $campos['help'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'fields' => $fields,
                );
                break;
            case 'number':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                );
                break;
            case 'email':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                );
                break;
            case 'password':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                );
                break;
            case 'token':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                );
                break;
            case 'map':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'direccion' => (isset($fila[$campos['field']])) ? $fila[$campos['field']]['direccion'] : '',
                    'lat' => (isset($fila[$campos['field']])) ? $fila[$campos['field']]['lat'] : '',
                    'lng' => (isset($fila[$campos['field']])) ? $fila[$campos['field']]['lng'] : '',
                );
                break;
            case 'recursive_checkbox':
            case 'recursive_radio':
                if ($level == 0) {
                    $data = array(
                        'is_children' => false,
                        'title_field' => $campos['title_field'],
                        'field' => $campos['field'],
                        'is_required' => $campos['required'],
                        'children' => '',
                    );
                    foreach ($campos['parent'] as $key => $children) {
                        $data['children'] .= $this->field($campos, $fila, '', $children[0], 1);
                    }

                } else {
                    $parent = $campos['parent'];
                    if (!isset($fila[$campos['field']])) {
                        if (isset($_GET[$campos['field']])) {
                            $checked = ($idparent == $_GET[$campos['field']]) ? 'checked="checked"' : '';
                        } else {
                            $checked = ($idparent == 0) ? 'checked="checked"' : '';
                        }

                    } else {
                        $checked = (in_array($idparent,$fila[$campos['field']])) ? 'checked="checked"' : '';
                    }
                    $data = array(
                        'is_children' => true,
                        'field' => $campos['field'],
                        'value' => $idparent,
                        'title' => (isset($parent[$idparent])) ? $parent[$idparent]['titulo'] : '',
                        'checked' => $checked,
                        'required' => ($campos['required']) ? 'required' : '',
                        'level' => ($level - 1) * 20,
                        'children' => '',
                    );
                    if (isset($parent[$idparent])) {
                        $campos['parent'] = $parent[$idparent]['children'];
                        foreach ($campos['parent'] as $key => $children) {
                            $data['children'] .= $this->field($campos, $fila, '', $children[0], $level + 1);
                        }
                    }
                }
                break;
            case 'textarea':
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                );
                break;
            case 'text':
            default:
                $data = array(
                    'title_field' => $campos['title_field'],
                    'field' => $campos['field'],
                    'is_required' => $campos['required'],
                    'required' => ($campos['required']) ? 'required="required"' : '',
                    'value' => (isset($fila[$campos['field']])) ? $fila[$campos['field']] : '',
                    'help' => (isset($campos['help'])) ? $campos['help'] : '',
                );
                break;
        }

        return view::render_template($data, $this->templates[$campos['type']]);
    }

    public static function guardar($class)
    {
        $campos = $_POST['campos'];
        $respuesta = array('exito' => false, 'mensaje' => '');

        if ($campos['id'] == '') {
            $respuesta['id'] = $class::insert($campos);
            $respuesta['mensaje'] = "Creado correctamente";
        } else {
            $respuesta['id'] = $class::update($campos);
            $respuesta['mensaje'] = "Actualizado correctamente";
        }
        $respuesta['exito'] = true;
        if (is_array($respuesta['id'])) {
            return $respuesta['id'];
        }
        return $respuesta;
    }

}
