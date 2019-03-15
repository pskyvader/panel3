<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\comuna as comuna_model;
use \app\models\mediopago as mediopago_model;
//use \app\models\moduloconfiguracion as moduloconfiguracion_model;
//use \app\models\modulo as modulo_model;
use \app\models\pedido as pedido_model;
use \app\models\pedidodireccion as pedidodireccion_model;
use \app\models\pedidoestado as pedidoestado_model;
use \app\models\pedidoproducto as pedidoproducto_model;
use \app\models\producto as producto_model;
use \app\models\region as region_model;
use \app\models\table;
use \app\models\usuario as usuario_model;
use \app\models\usuariodireccion as usuariodireccion_model;
use \core\database;
use \core\functions;
use \core\image;

class pedido extends base
{
    protected $url        = array('pedido');
    protected $metadata   = array('title' => 'pedido', 'modulo' => 'pedido');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new pedido_model);
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

        $list          = new lista($this->metadata); //controlador de lista
        $configuracion = $list->configuracion($this->metadata['modulo']);

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

        if (isset($_GET['idpedidoestado']) && $_GET['idpedidoestado'] != 0) {
            $where['idpedidoestado'] = $_GET['idpedidoestado'];
        }
        $condiciones   = array('order' => 'fecha_pago DESC,fecha_creacion DESC');
        $url_detalle   = $this->url;
        $url_detalle[] = 'detail';
        $respuesta     = $list->get_row($class, $where, $condiciones, $url_detalle); //obtener unicamente elementos de la pagina actual

        if (isset($configuracion['th']['copy'])) {
            $configuracion['th']['copy']['action']  = $configuracion['th']['copy']['field'];
            $configuracion['th']['copy']['field']   = 0;
            $configuracion['th']['copy']['mensaje'] = 'Copiando';
        }

        if (isset($configuracion['th']['idpedidoestado'])) {
            $pe           = pedidoestado_model::getAll();
            $pedidoestado = array();
            foreach ($pe as $key => $p) {
                $pedidoestado[$p[0]] = array('background' => $p['color'], 'text' => $p['titulo'],'color' => functions::getContrastColor($p['color']));
            }

            foreach ($respuesta['row'] as $k => $v) {
                $respuesta['row'][$k]['idpedidoestado'] = $pedidoestado[$v['idpedidoestado']];
            }
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
            'breadcrumb'  => $this->breadcrumb,
            'th'          => $configuracion['th'],
            'current_url' => functions::generar_url($this->url),
            'new_url'     => functions::generar_url($url_detalle),
        );
        $data = array_merge($data, $respuesta, $configuracion['menu']);

        $list->normal($data);
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

        if (isset($configuracion['campos']['idusuario'])) {
            if ($id == 0 || $row['idusuario'] == 0) {
                $usuarios = usuario_model::getAll(array(), array('order' => 'nombre ASC'));
                foreach ($usuarios as $key => $u) {
                    $usuarios[$key]['titulo'] = $u['nombre'] . ' (' . $u['email'] . ')' . ((!$u['estado']) ? ': desactivado' : '');
                }
                $configuracion['campos']['idusuario']['parent'] = $usuarios;
            } else {
                $configuracion['campos']['idusuario']['type'] = 'hidden';
            }
        }



        if (isset($configuracion['campos']['idpedidoestado'])) {
            $estados                                             = pedidoestado_model::getAll(array('tipo' => $_GET['tipo']));
            $configuracion['campos']['idpedidoestado']['parent'] = $estados;
        }
        if (isset($configuracion['campos']['idmediopago'])) {
            $estados                                          = mediopago_model::getAll();
            $configuracion['campos']['idmediopago']['parent'] = $estados;
        }

        if (isset($configuracion['campos']['cookie_pedido']) && $id != 0) {
            $configuracion['campos']['cookie_pedido']['type'] = 'text';
        }
        
        if (isset($configuracion['campos']['direcciones'])) {
            $com     = comuna_model::getAll();
            $comunas = array();
            foreach ($com as $key => $c) {
                if ($c['precio'] > 1) {
                    $r           = region_model::getById($c['idregion']);
                    $c['precio'] = $r['precio'];
                }
                $comunas[$c[0]] = $c;
            }

            $configuracion['campos']['direcciones']['direccion_entrega'] = array();
            $lista_productos                                             = producto_model::getAll(array('tipo' => 1), array('order' => 'titulo ASC'));
            foreach ($lista_productos as $key => $lp) {
                $portada               = image::portada($lp['foto']);
                $thumb_url             = image::generar_url($portada, 'cart');
                $lista_productos[$key] = array('titulo' => $lp['titulo'], 'idproducto' => $lp['idproducto'], 'foto' => $thumb_url, 'precio' => $lp['precio_final'], 'stock' => $lp['stock']);
            }
            $configuracion['campos']['direcciones']['lista_productos'] = $lista_productos;

            $lista_atributos = producto_model::getAll(array('tipo' => 2), array('order' => 'titulo ASC'));
            foreach ($lista_atributos as $key => $lp) {
                $portada               = image::portada($lp['foto']);
                $thumb_url             = image::generar_url($portada, 'cart');
                $lista_atributos[$key] = array('titulo' => $lp['titulo'], 'idproducto' => $lp['idproducto'], 'foto' => $thumb_url);
            }
            $configuracion['campos']['direcciones']['lista_atributos'] = $lista_atributos;

            if ($id != 0) {
                if (isset($row['idusuario']) && $row['idusuario'] != '') {
                    $direcciones_entrega = usuariodireccion_model::getAll(array('idusuario' => $row['idusuario']));
                    foreach ($direcciones_entrega as $key => $de) {
                        $direcciones_entrega[$key]['precio'] = $comunas[$de['idcomuna']]['precio'];
                        $direcciones_entrega[$key]['titulo'] = $de['titulo'] . ' (' . $de['direccion'] . ')';
                    }
                    $configuracion['campos']['direcciones']['direccion_entrega'] = $direcciones_entrega;
                }

                $dir         = pedidodireccion_model::getAll(array('idpedido' => $id));
                $direcciones = array();
                foreach ($dir as $key => $d) {
                    $new_d     = array('idpedidodireccion' => $d['idpedidodireccion'], 'idusuariodireccion' => $d['idusuariodireccion'], 'precio' => $d['precio'], 'fecha_entrega' => $d['fecha_entrega']);
                    $prod      = pedidoproducto_model::getAll(array('idpedido' => $id, 'idpedidodireccion' => $d[0]));
                    $productos = array();
                    foreach ($prod as $v => $p) {
                        $portada     = image::portada($p['foto']);
                        $thumb_url   = image::generar_url($portada, '');
                        $new_p       = array('idpedidoproducto' => $p['idpedidoproducto'], 'idproductoatributo' => $p['idproductoatributo'], 'titulo' => $p['titulo'], 'mensaje' => $p['mensaje'], 'idproducto' => $p['idproducto'], 'foto' => $thumb_url, 'precio' => $p['precio'], 'cantidad' => $p['cantidad'], 'total' => $p['total']);
                        $productos[] = $new_p;
                    }
                    $new_d['productos'] = $productos;
                    $new_d['cantidad']  = count($productos);
                    if ($new_d['cantidad'] == 0) {
                        $new_d['cantidad'] = '';
                    }

                    $direcciones[] = $new_d;
                }
                $row['direcciones'] = $direcciones;
            }
        }
        if(isset($row['fecha_pago']) && $row['fecha_pago']==0){
            $row['fecha_pago']='';
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

        $detalle->normal($data);
    }
    public function get_usuario()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST);
        if (isset($campos['idusuario'])) {
            $usuario = usuario_model::getById($campos['idusuario']);
            if (count($usuario) > 0) {
                $com     = comuna_model::getAll();
                $comunas = array();
                foreach ($com as $key => $c) {
                    if ($c['precio'] > 1) {
                        $r           = region_model::getById($c['idregion']);
                        $c['precio'] = $r['precio'];
                    }
                    $comunas[$c[0]] = $c;
                }
                $usuario              = array($usuario[0], 'nombre' => $usuario['nombre'], 'email' => $usuario['email'], 'telefono' => $usuario['telefono']);
                $respuesta['usuario'] = $usuario;
                $direcciones          = usuariodireccion_model::getAll(array('idusuario' => $usuario[0]));
                foreach ($direcciones as $key => $d) {
                    $direcciones[$key]['precio'] = $comunas[$d['idcomuna']]['precio'];
                }
                $respuesta['direcciones'] = $direcciones;
                $respuesta['exito']       = true;
            } else {
                $respuesta['mensaje'] = 'El usuario seleccionado no existe o esta desactivado';
            }
        } else {
            $respuesta['mensaje'] = 'No se ha seleccionado un usuario';
        }
        echo json_encode($respuesta);
    }

    /**
     * guardar
     * guarda pedido, direcciones de pedido y productos del pedido
     *
     * @return JSON
     */
    public function guardar()
    {
        $class     = $this->class;
        $campos    = $_POST['campos'];
        $respuesta = array('exito' => false, 'mensaje' => '');
        if (isset($campos['datos_direcciones'])) {
            $direcciones = $campos['datos_direcciones'];
            unset($campos['datos_direcciones']);
        }
        $campos['total_original'] = $campos['total'];

        if ($campos['id'] == '') {
            $respuesta['id']      = $class::insert($campos);
            $respuesta['mensaje'] = "Creado correctamente";
        } else {
            $respuesta['id']      = $class::update($campos);
            $respuesta['mensaje'] = "Actualizado correctamente";
        }
        if (is_array($respuesta['id'])) {
            echo json_encode($respuesta['id']);
            exit;
        }
        $respuesta['exito'] = true;
        $pedido             = pedido_model::getById($respuesta['id']);

        $com     = comuna_model::getAll();
        $comunas = array();
        foreach ($com as $key => $c) {
            if ($c['precio'] > 1) {
                $r           = region_model::getById($c['idregion']);
                $c['precio'] = $r['precio'];
            }
            $comunas[$c[0]] = $c;
        }

        $dp                 = pedidodireccion_model::getAll(array('idpedido' => $pedido[0]));
        $direcciones_pedido = array();
        foreach ($dp as $key => $d) {
            $direcciones_pedido[$d[0]] = $d;
        }

        $du                  = usuariodireccion_model::getAll(array('idusuario' => $pedido['idusuario']));
        $direcciones_usuario = array();
        foreach ($du as $key => $d) {
            $d['comuna']                = $comunas[$d['idcomuna']];
            $direcciones_usuario[$d[0]] = $d;
        }

        $pa                 = pedidoproducto_model::getAll(array('idpedido' => $pedido[0]));
        $productos_antiguos = array();
        foreach ($pa as $key => $p) {
            $productos_antiguos[$p[0]] = $p;
        }

        $total_pedido = 0;

        //procesar direcciones
        foreach ($direcciones as $key => $d) {
            if (!isset($direcciones_usuario[$d['iddireccion']])) {
                $respuesta['exito']   = false;
                $respuesta['mensaje'] = 'Una dirección no es valida, por favor recarga la pagina e intenta nuevamente';
                break;
            } else {
                $du = $direcciones_usuario[$d['iddireccion']];
            }
            $productos = $d['productos'];
            if (isset($direcciones_pedido[$d['iddireccionpedido']])) {
                $existe_direccion  = true;
                $fields            = table::getByname(pedidodireccion_model::$table);
                $new_d             = database::create_data($fields, $direcciones_pedido[$d['iddireccionpedido']]);
                $iddirecionpeddido = $direcciones_pedido[$d['iddireccionpedido']][0];
                unset($direcciones_pedido[$d['iddireccionpedido']]);
                $new_d['fecha_entrega'] = $d['fecha_entrega'];
            } else {
                $existe_direccion            = false;
                $new_d                       = $d;
                $new_d['cookie_direccion']   = $pedido['cookie_pedido'] . '-' . functions::generar_pass(2);
                $new_d['idpedido']           = $pedido[0];
                $new_d['idusuariodireccion'] = $du[0];
            }
            //4= pedido pagado
            if ($pedido['idpedidoestado'] != 4) {
                //9=envio no pagado aun
                $new_d['idpedidoestado'] = 9;
            } else {
                //5=preparango producto
                $new_d['idpedidoestado'] = 5;
            }

            $new_d['precio'] = $du['comuna']['precio'];

            $new_d['nombre']             = $du['nombre'];
            $new_d['telefono']           = $du['telefono'];
            $new_d['referencias']        = $du['referencias'];
            $new_d['direccion_completa'] = $du['direccion'] . ', ' . $du['comuna']['titulo'] . ';';
            $new_d['direccion_completa'] .= ($du['villa'] != '') ? ', villa ' . $du['villa'] : '';
            $new_d['direccion_completa'] .= ($du['edificio'] != '') ? ', edificio ' . $du['edificio'] : '';
            $new_d['direccion_completa'] .= ($du['departamento'] != '') ? ', departamento ' . $du['departamento'] : '';
            $new_d['direccion_completa'] .= ($du['condominio'] != '') ? ', condominio ' . $du['condominio'] : '';
            $new_d['direccion_completa'] .= ($du['casa'] != '') ? ', casa ' . $du['casa'] : '';
            $new_d['direccion_completa'] .= ($du['empresa'] != '') ? ', empresa ' . $du['empresa'] : '';

            if ($existe_direccion) {
                $new_d['id']       = $iddirecionpeddido;
                $idpedidodireccion = pedidodireccion_model::update($new_d);
            } else {
                $idpedidodireccion = pedidodireccion_model::insert($new_d);
            }
            $total_pedido += $new_d['precio'];

            foreach ($productos as $key => $p) {
                $cantidad_antigua = 0;
                if (isset($productos_antiguos[$p['idproductopedido']])) {
                    $existe = true;
                    $fields = table::getByname(pedidoproducto_model::$table);
                    $new_p  = database::create_data($fields, $productos_antiguos[$p['idproductopedido']]);
                    unset($productos_antiguos[$p['idproductopedido']]);
                    if ($new_p['idproducto'] != $p['idproducto']) {
                        $change = true;
                    } else {
                        $change = false;
                    }
                    $cantidad_antigua            = $new_p['cantidad'];
                    $new_p['idproducto']         = $p['idproducto'];
                    $new_p['cantidad']           = $p['cantidad'];
                    $new_p['idproductoatributo'] = $p['idproductoatributo'];
                    $new_p['mensaje']            = $p['mensaje'];
                } else {
                    $existe            = false;
                    $change            = true;
                    $new_p             = $p;
                    $new_p['idpedido'] = $pedido[0];
                    unset($new_p['idproductopedido']);
                }

                $producto_detalle = producto_model::getById($new_p['idproducto']);
                if (count($producto_detalle) == 0) {
                    $respuesta['exito']   = false;
                    $respuesta['mensaje'] = 'Un producto no es valido, por favor recarga la pagina e intenta nuevamente';
                    break 2; // salir de ambos foreach
                }
                $producto_detalle['stock'] -= ($new_p['cantidad'] - $cantidad_antigua);

                if ($change) {
                    $new_p['titulo'] = $producto_detalle['titulo'];
                    $new_p['precio'] = $producto_detalle['precio_final'];
                }

                $new_p['idpedidodireccion'] = $idpedidodireccion;
                $atributo                   = producto_model::getById($new_p['idproductoatributo']);
                $new_p['titulo_atributo']   = $atributo['titulo'];
                $new_p['total']             = $new_p['precio'] * $new_p['cantidad'];

                if ($existe) {
                    $new_p['id']      = $p['idproductopedido'];
                    $new_p['foto']    = json_encode($new_p['foto']);
                    $idpedidoproducto = pedidoproducto_model::update($new_p);
                } else {
                    $idpedidoproducto = pedidoproducto_model::insert($new_p);
                }

                if ($change) {
                    $new_p['id'] = $idpedidoproducto;
                    $portada     = image::portada($producto_detalle['foto']);
                    $copiar      = image::copy($portada, $new_p['id'], pedidoproducto_model::$table, '', '', 'cart');
                    if ($copiar['exito']) {
                        $new_p['foto']    = json_encode($copiar['file']);
                        $idpedidoproducto = pedidoproducto_model::update($new_p);
                    } else {
                        $respuesta = $copiar;
                        break 2; // salir de ambos foreach
                    }
                }

                producto_model::update(array('id' => $new_p['idproducto'], 'stock' => $producto_detalle['stock']));
                $total_pedido += $new_p['total'];
            }
        }

        //borrar productos y direcciones si fueron eliminados en la vista
        foreach ($productos_antiguos as $key => $pa) {
            $producto_detalle = producto_model::getById($pa['idproducto']);
            $producto_detalle['stock'] += ($pa['cantidad']);
            $producto_detalle['id'] = $pa['idproducto'];
            producto_model::update(array('id' => $pa['idproducto'], 'stock' => $producto_detalle['stock']));
            pedidoproducto_model::delete($pa[0]);
        }
        foreach ($direcciones_pedido as $key => $dp) {
            pedidodireccion_model::delete($dp[0]);
        }

        if ($pedido['total'] != $total_pedido) {
            $campos['total_original'] = $total_pedido;
            $campos['id']             = $pedido[0];
            $respuesta['id']          = $class::update($campos);
        }

        echo json_encode($respuesta);
    }

}
