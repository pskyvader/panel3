<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\logo as logo_model;
use \app\models\table as table_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \app\models\modulo as modulo_model;
use \core\app;
use \core\database;
use \core\functions;
use \core\view;

class configuracion_administrador extends base
{
    protected $url = array('configuracion_administrador');
    protected $metadata = array('title' => 'Configuracion de administrador', 'modulo' => 'configuracion_administrador');
    public function __construct()
    {
        parent::__construct(null);

    }
    public function index()
    {
        if (!administrador_model::verificar_sesion()) {
            $this->url = array('login', 'index', 'home');
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        $aside = new aside();
        $aside->normal();

        $vaciar = table_model::getAll(array('truncate' => true), array(), 'tablename');
        view::set('vaciar', $vaciar);
        view::set('breadcrumb', $this->breadcrumb);
        view::set('title', $this->metadata['title']);
        view::set('save_url', functions::generar_url(array_merge($this->url, array('vaciar'))));
        view::set('list_url', functions::generar_url($this->url));
        view::render('configuracion_administrador');

        $footer = new footer();
        $footer->normal();
    }

    public function vaciar()
    {
        if (isset($_POST['campos'])) {
            $campos = $_POST['campos'];
            $respuesta = table_model::truncate($campos);
        } else {
            $respuesta = array('exito' => false, 'mensaje' => 'Debe seleccionar una tabla para vaciar');
        }
        echo json_encode($respuesta);
    }
    public function json()
    {
        $respuesta = array('exito' => true, 'mensaje' => 'JSON generado correctamente');
        $dir = APPPATH . '/config/';
        $row = table_model::getAll();
        $campos = array();
        foreach ($row as $key => $tabla) {
            $a = array(
                'tablename' => $tabla['tablename'],
                'idname' => $tabla['idname'],
                'fields' => $tabla['fields'],
                'truncate' => $tabla['truncate'],
            );
            $campos[] = $a;
        }
        file_put_contents($dir . 'bdd.json', functions::encode_json($campos, true));

        
        $row = moduloconfiguracion_model::getAll();
        $campos = array();
        foreach ($row as $key => $tabla) {
            $fields = table_model::getByname('moduloconfiguracion');
            $a = database::create_data($fields, $tabla);
            $row_hijo = modulo_model::getAll(array('idmoduloconfiguracion'=>$tabla[0]));
            $h=array();
            
            $fields_hijo = table_model::getByname('modulo');
            foreach ($row_hijo as $key => $hijos) {
                $h[] = database::create_data($fields_hijo, $hijos);
            }
            $a['hijo']=$h;
            $campos[] = $a;
        }
        file_put_contents($dir . 'moduloconfiguracion.json', functions::encode_json($campos, true));
        echo json_encode($respuesta);
    }

    public function json_update()
    {
        $respuesta = array('exito' => true, 'mensaje' => array('JSON actualizado correctamente'));
        $dir = APPPATH . '/config/';
        $campos = functions::decode_json(file_get_contents($dir . 'bdd.json'));

        foreach ($campos as $key => $tabla) {
            $tablename = $tabla['tablename'];
            if ($key == 0) { //primero es siempre la tabla "tablas", se crea inmediatamente para guardar las siguientes configuraciones
                $existe = table_model::table_exists($tablename);
                if (!$existe) {
                    $fields = $tabla['fields'];
                    array_unshift($fields, array('titulo' => $tabla['idname'], 'tipo' => 'int(11)', 'primary' => true));
                    foreach ($fields as $key => $value) {
                        if (!isset($fields[$key]['primary'])) {
                            $fields[$key]['primary'] = false;
                        }
                    }
                    $connection = database::instance();
                    $connection->create($tablename, $fields);
                }
            }
            $table = table_model::getAll(array('tablename' => $tablename));
            
            $tabla['fields'] = functions::encode_json($tabla['fields']);
            if (count($table) == 1) {
                $tabla['id'] = $table[0][0];
                table_model::update($tabla, false);
            } else {
                table_model::insert($tabla, false);
            }
        }

        $tablas = table_model::getAll();

        foreach ($tablas as $key => $tabla) {
            $mensajes = table_model::validate($tabla[0], false);
            if (!$mensajes['exito']) {
                $respuesta = $mensajes;
                break;
            } else {
                $respuesta['mensaje'] = array_merge($respuesta['mensaje'], $mensajes['mensaje']);
            }
        }

        $row = administrador_model::getAll(array('email' => 'admin@mysitio.cl'));
        if (count($row) == 0) {
            $insert_admin = array(
                'pass' => 12345678,
                'pass_repetir' => 12345678,
                'nombre' => 'Admin',
                'email' => 'admin@mysitio.cl',
                'tipo' => 1,
                'estado' => true,
            );
            administrador_model::insert($insert_admin);
        }

        $row = logo_model::getAll();
        if (count($row) == 0) {
            $insert_logo = array(
                array('titulo' => 'favicon', 'orden' => 1),
                array('titulo' => 'Logo login', 'orden' => 2),
                array('titulo' => 'Logo panel grande', 'orden' => 3),
                array('titulo' => 'Logo panel pequeño', 'orden' => 4),
                array('titulo' => 'Logo Header sitio', 'orden' => 5),
                array('titulo' => 'Logo Footer sitio', 'orden' => 6),
                array('titulo' => 'Manifest', 'orden' => 7),
                array('titulo' => 'Email', 'orden' => 8),
            );
            foreach ($insert_logo as $key => $logos) {
                logo_model::insert($logos);
            }
        }

        
        $campos = functions::decode_json(file_get_contents($dir . 'moduloconfiguracion.json'));
        foreach ($campos as $key => $moduloconfiguracion) {
            $row=moduloconfiguracion_model::getAll(array('module'=>$moduloconfiguracion['module']),array('limit'=>1));
            $hijo=$moduloconfiguracion['hijo'];
            unset($moduloconfiguracion['hijo']);
            $moduloconfiguracion['mostrar'] = functions::encode_json($moduloconfiguracion['mostrar']);
            $moduloconfiguracion['detalle'] = functions::encode_json($moduloconfiguracion['detalle']);
            if (count($row) == 1) {
                $moduloconfiguracion['id'] = $row[0][0];
                moduloconfiguracion_model::update($moduloconfiguracion, false);
            } else {
                $id=moduloconfiguracion_model::insert($moduloconfiguracion, false);
                foreach ($hijo as $key => $h) {
                    $h['idmoduloconfiguracion'] = $id;
                    $h['menu'] = functions::encode_json($h['menu']);
                    $h['mostrar'] = functions::encode_json($h['mostrar']);
                    $h['detalle'] = functions::encode_json($h['detalle']);
                    $h['recortes'] = functions::encode_json($h['recortes']);
                    $h['estado'] = functions::encode_json($h['estado']);
                    modulo_model::insert($h, false);
                }
            }
        }

        echo json_encode($respuesta);
    }

}
