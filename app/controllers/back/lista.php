<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use \app\models\modulo as modulo_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \core\functions;
use \core\image;
use \core\view;

class lista
{
    private $metadata = array('title' => '');
    private $templates = array();

    public function __construct($metadata)
    {
        foreach ($metadata as $key => $value) {
            $this->metadata[$key] = $value;
        }
        $list_dir = view::get_theme() . 'list/';
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
        $th = $data['th'];
        $row_data = $data['row'];
        $row = array();
        $even = false;
        foreach ($row_data as $key => $fila) {
            $td = array();
            foreach ($th as $k => $v) {
                $content = $this->field($v, $fila);
                $td[] = array('content' => $content, 'content_field' => $v['field']);
            }
            $linea = array('even' => $even, 'id' => $fila[0], 'td' => $td, 'order' => (isset($fila['orden'])) ? $fila['orden'] : '');
            $row[] = $linea;
            $even = !$even;
        }
        $data['row'] = $row;
        $data['title'] = $this->metadata['title'];
        $data['order_class'] = isset($th['orden']);
        $data['order_head'] = isset($th['orden']);
        $data['order_body'] = isset($th['orden']);

        $data = $this->pagination($data);

        $data['delete'] = (isset($th['delete'])) ? true : false;

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        $aside = new aside();
        $aside->normal();

        view::set_array($data);
        view::render('list');

        $footer = new footer();
        $footer->normal();
    }

    public function ajax()
    {

    }
    public function get_row($class, $where, $condiciones, $urledit)
    {
        $limit = (isset($_GET['limit'])) ? (int) $_GET['limit'] : 10;
        $page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
        $search = (isset($_GET['search'])) ? $_GET['search'] : '';

        if ($search != '') {
            $condiciones['palabra'] = $search;
        }

        $count = $class::getAll($where, $condiciones, 'COUNT(' . $class::$idname . ') as count');
        $count = $count[0]['count'];
        $total = (int) ($count / $limit);
        if ($total < ($count / $limit)) {
            $total++;
        }

        $condiciones['limit'] = $limit;
        if ($page > 1) {
            $condiciones['limit'] = (($page - 1) * $limit);
            $condiciones['limit2'] = ($limit);
        }
        $inicio = ($limit * ($page - 1)) + 1;
        $fin = ($limit * ($page));
        if($fin>$count) $fin=$count;

        $row = $class::getAll($where, $condiciones);
        foreach ($row as $k => $v) {
            $urltmp = $urledit;
            $urltmp[] = $v[0];
            $row[$k]['url_detalle'] = functions::generar_url($urltmp);
        }

        return array('row' => $row, 'page' => $page, 'total' => $total, 'limit' => $limit, 'search' => $search, 'count' => $count, 'inicio' => $inicio, 'fin' => $fin);
    }

    private function pagination($data)
    {

        $limits = array(
            10 => array('value' => 10,'text' => 10, 'active' => ''),
            25 => array('value' => 25,'text' => 25, 'active' => ''),
            100 => array('value' => 100,'text' => 100, 'active' => ''),
            500 => array('value' => 500,'text' => 500, 'active' => ''),
            1000 => array('value' => 1000,'text' => 1000, 'active' => ''),
            1000000 => array('value' => 1000000,'text' => 'Todos', 'active' => ''),
        );
        $limits[$data['limit']]['active'] = 'selected';
        $data['limits'] = $limits;

        $pagination = array();
        $rango = 5;
        $min = 1;
        $max = $data['total'];
        $sw = false;
        while ((($max - $min) + 1) > $rango) {
            if ($sw) {
                if ($min != $data['page'] && $min + 1 != $data['page']) {
                    $min++;
                }
            } else {
                if ($max != $data['page'] && $max - 1 != $data['page']) {
                    $max--;
                }
            }
            $sw = !$sw;
        }

        $_GET['page'] = $data['page'] - 1;
        $pagination[] = array(
            'class_page' => 'previous ' . (($data['page'] > 1) ? '' : 'disabled'),
            'url_page' => "?" . http_build_query($_GET),
            'text_page' => '<i class="fa fa-angle-left"> </i> Anterior',
        );

        for ($i = $min; $i <= $max; $i++) {
            $_GET['page'] = $i;
            $pagination[] = array(
                'class_page' => (($data['page'] == $i) ? 'active' : ''),
                'url_page' => "?" . http_build_query($_GET),
                'text_page' => $i,
            );
        }

        $_GET['page'] = $data['page'] + 1;
        $pagination[] = array(
            'class_page' => 'next ' . (($data['page'] < $data['total']) ? '' : 'disabled'),
            'url_page' => "?" . http_build_query($_GET),
            'text_page' => 'Siguiente <i class="fa fa-angle-right"> </i> ',
        );

        $data['pagination'] = $pagination;
        return $data;
    }

    private function field($th, $fila)
    {
        $type = $th['type'];
        switch ($type) {
            case 'active':
                $html = $this->templates[$type];
                $data = array(
                    'field' => $th['field'],
                    'active' => $fila[$th['field']],
                    'id' => $fila[0],
                    'class' => ($fila[$th['field']]) ? 'btn-success' : 'btn-danger',
                    'icon' => ($fila[$th['field']]) ? 'fa-check' : 'fa-close',
                );
                $content = view::render_template($data, $html);
                return $content;
                break;
            case 'delete':
                $html = $this->templates[$type];
                $data = array('id' => $fila[0]);
                $content = view::render_template($data, $html);
                return $content;
                break;
            case 'link':
                $html = $this->templates[$type];
                $data = array('text' => $th['title_th'], 'url' => $fila[$th['field']]);
                $content = view::render_template($data, $html);
                return $content;
                break;
            case 'image':
                if (isset($fila[$th['field']]) && is_array($fila[$th['field']])) {
                    $portada=image::portada($fila[$th['field']]);
                    $thumb_url = image::generar_url($portada, 'thumb');
                    $zoom_url = image::generar_url($portada, 'zoom');
                    $original_url = image::generar_url($portada, '');
                } else {
                    $thumb_url = $zoom_url = $original_url = '';
                }
                $html = $this->templates[$type];
                $data = array('title' => $th['title_th'], 'url' => $thumb_url, 'zoom' => $zoom_url, 'original' => $original_url, 'id' => $fila[0]);
                $content = view::render_template($data, $html);
                return $content;
                break;
            case 'action':
                $html = $this->templates[$type];
                $data = array(
                    'text' => $th['title_th'],
                    'id' => $fila[$th['field']],
                    'action' => $th['action'],
                    'mensaje' => $th['mensaje'],
                );
                $content = view::render_template($data, $html);
                return $content;
                break;
            case 'text':
            default:
                return $fila[$th['field']];
                break;
        }
    }
    public static function configuracion($modulo)
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
        if ($estados[$tipo_admin] != 'true') {
            functions::url_redirect(array('home'));
        }
        $th = array();
        foreach ($modulo['mostrar'] as $key => $m) {
            if ($m['estado'][$tipo_admin] == 'true') {
                $th[$m['field']] = array('title_th' => $m['titulo'], 'field' => $m['field'], 'type' => $m['tipo']);
            }
        }

        $menu = array();
        foreach ($modulo['menu'] as $key => $m) {
            if ($m['estado'][$tipo_admin] == 'true') {
                $menu[$m['field']] = true;
            } else {
                $menu[$m['field']] = false;
            }
        }
        return array('menu' => $menu, 'th' => $th);
    }

    public static function orden($class)
    {
        $campos = $_POST['campos'];
        $respuesta = array('exito' => false, 'mensaje' => '');
        $elementos = $campos['elementos'];
        foreach ($elementos as $key => $e) {
            $class::update($e);
        }
        $respuesta['exito'] = true;
        $respuesta['mensaje'] = "Orden actualizado correctamente";
        return $respuesta;
    }
    public static function estado($class)
    {
        $campos = $_POST['campos'];
        $respuesta = array('exito' => false, 'mensaje' => '');
        $set = array('id' => $campos['id'], $campos['campo'] => $campos['active']);
        $class::update($set);
        $respuesta['exito'] = true;
        $respuesta['mensaje'] = "Estado actualizado correctamente.";
        return $respuesta;
    }
    public static function eliminar($class)
    {
        $campos = $_POST['campos'];
        $respuesta = array('exito' => false, 'mensaje' => '');
        $class::delete($campos['id']);
        $respuesta['exito'] = true;
        $respuesta['mensaje'] = "Eliminado correctamente.";
        return $respuesta;
    }

    public static function copy($class)
    {
        $campos = $_POST['campos'];
        $respuesta = array('exito' => false, 'mensaje' => '');
        $id = $class::copy($campos['id']);
        $respuesta['exito'] = true;
        $respuesta['mensaje'] = "Copiado correctamente.";
        $respuesta['id'] = $id;
        $respuesta['refresh'] = true;
        return $respuesta;
    }
}
