<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\comuna as comuna_model;
use \app\models\pedido as pedido_model;
use \app\models\pedidodireccion as pedidodireccion_model;
use \app\models\pedidoproducto as pedidoproducto_model;
use \app\models\producto as producto_model;
use \app\models\region as region_model;
use \app\models\seo as seo_model;
use \app\models\usuario as usuario_model;
use \app\models\usuariodireccion as usuariodireccion_model;
use \core\app;
use \core\functions;
use \core\image;
use \core\view;

class order extends base
{
    private static $steps = array(
        1 => 'Paso 1: resumen del carro',
        2 => 'Paso 2: Direcciones',
        3 => 'Paso 3: Confirmación',
    );
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo'], false);
    }
    public function index()
    {
        $this->meta($this->seo);
        $this->url[] = 'step';
        $this->url[] = '1';
        functions::url_redirect($this->url);
    }
    public function step($var = array())
    {
        $error   = false;
        $mensaje = '';
        $this->meta($this->seo);
        $this->url[]  = 'step';
        $current_step = 1;
        if (isset($var[0]) && array_key_exists($var[0], self::$steps)) {
            $current_step = $var[0];
        }
        $this->url[] = $current_step;
        $logueado    = user::verificar(true);
        $carro       = cart::current_cart(true);
        if (!$logueado['exito']) {
            $_GET['next_url'] = implode('/', $this->url);
            $this->url        = array('cuenta', 'registro');
        }
        if (2 == $current_step) {
            $direcciones = usuariodireccion_model::getAll(array('idusuario' => $_SESSION[usuario_model::$idname . app::$prefix_site]));
            if (count($direcciones) == 0) {
                $seo_usuario      = seo_model::getById(9);
                $_GET['next_url'] = implode('/', $this->url);
                $this->url        = array($seo_usuario['url'], 'direccion');
            }
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'], self::$steps[$current_step]);
        if (count($carro) == 0 || count($carro['productos']) == 0) {
            $mensaje = "Tu carro está vacío. Por favor agrega productos para continuar tu compra";
            $error   = true;
        }

        if ($error) {
            view::set('mensaje', $mensaje);
            view::render('order/error');
        } else {
            $steps = self::steps($current_step, $this->url);
            $class = "step" . $current_step;
            self::$class($carro, $this->url);
            view::set('steps', $steps);
            view::render('order/' . $current_step);
        }

        $footer = new footer();
        $footer->normal();
    }

    private static function sidebar($carro)
    {
        view::set('subtotal', $carro['subtotal']);
        view::set('total_direcciones', $carro['total_direcciones']);
        view::set('total', $carro['total']);
        return view::render('order/sidebar', false, true);
    }
    private static function steps($current_step, $url)
    {
        $steps = array();
        foreach (self::$steps as $key => $s) {
            $active   = ($current_step == $key);
            $disabled = ($current_step < $key);
            $url_step = functions::generar_url(array($url[0], 'step', $key));
            $steps[]  = array('title' => $s, 'active' => $active, 'disabled' => $disabled, 'url' => $url_step);
        }
        view::set('steps', $steps);
        return view::render('order/steps', false, true);
    }
    private static function step1($carro, $url)
    {
        $attr = producto_model::getAll(array('tipo' => 2), array('order' => 'titulo ASC'));
        foreach ($attr as $key => $lp) {
            $portada    = image::portada($lp['foto']);
            $thumb_url  = image::generar_url($portada, 'cart');
            $attr[$key] = array('titulo' => $lp['titulo'], 'idproducto' => $lp['idproducto'], 'foto' => $thumb_url);
        }
        foreach ($carro['productos'] as $key => $p) {
            $atributos = $attr;
            foreach ($atributos as $k => $a) {
                if ($a['idproducto'] == $p['idproductoatributo']) {
                    $atributos[$k]['selected'] = true;
                } else {
                    $atributos[$k]['selected'] = false;
                }
            }
            $carro['productos'][$key]['atributos'] = $atributos;
        }

        $sidebar = self::sidebar($carro);
        view::set_array($carro);
        view::set('sidebar', $sidebar);
        $seo_producto = seo_model::getById(8);
        view::set('url_product', functions::generar_url(array($seo_producto['url'])));
        $direcciones = usuariodireccion_model::getAll(array('idusuario' => $_SESSION[usuario_model::$idname . app::$prefix_site]));
        if (count($direcciones) > 0) {
            view::set('url_next', functions::generar_url(array($url[0], 'step', 2)));
        } else {
            $seo_usuario = seo_model::getById(9);
            view::set('url_next', functions::generar_url(array($seo_usuario['url'], 'direccion'), array('next_url' => implode('/', array($url[0], 'step', 2)))));
        }
    }

    /**
     * step2
     * NO AGREGAR ELEMENTOS AL CARRO antes de update_cart
     *
     * @param  array $carro
     * @param  array $url
     *
     * @return void
     */
    private static function step2(array $carro, array $url)
    {
        $horarios_entrega = array();
        $hora_minima      = strtotime("08:00");
        $hora_maxima      = strtotime("12:00");
        $hora_corte       = strtotime("18:00");

        $hora_actual = $hora_minima;
        do {
            $hora1                    = strftime("%R", $hora_actual);
            $hora2                    = strftime("%R", strtotime("+1 hours", $hora_actual));
            $horarios_entrega[$hora1] = array('hora' => $hora1, 'titulo' => $hora1 . '  -   ' . $hora2);
            $hora_actual              = strftime(strtotime("+15 minutes", $hora_actual));
        } while (strtotime($hora2) < $hora_maxima);

        $fechas_bloqueadas   = array();
        $fechas_bloqueadas[] = array('fecha' => '2019-01-22', 'texto' => 'Cerrado por Vacaciones');
        $fechas_bloqueadas[] = array('fecha' => '2019-01-23', 'texto' => 'Cerrado por Vacaciones');
        $fechas_bloqueadas[] = array('fecha' => '2019-01-24', 'texto' => 'Cerrado por Vacaciones');

        if (time() > $hora_corte) {
            $fechas_bloqueadas[] = array('fecha' => functions::formato_fecha(strtotime("+1 day"), '%F'), 'texto' => '');
        }

        $fechas_especiales   = array();
        $fechas_especiales[] = array('fecha' => '2019-02-14', 'texto' => 'Dia de los enamorados');
        $fechas_especiales[] = array('fecha' => '2019-02-13', 'texto' => 'Dia de los enamorados');

        $comunas             = self::get_comunas();
        $direcciones_entrega = usuariodireccion_model::getAll(array('idusuario' => $_SESSION[usuario_model::$idname . app::$prefix_site]));
        foreach ($direcciones_entrega as $key => $de) {
            $direcciones_entrega[$key]['precio'] = $comunas[$de['idcomuna']]['precio'];
            $direcciones_entrega[$key]['titulo'] = $de['titulo'] . ' (' . $de['direccion'] . " , " . $comunas[$de['idcomuna']]['titulo'] . ')';
        }

        $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $carro['idpedido']));
        if (count($direcciones_pedido) == 0) {
            $du                        = reset($direcciones_entrega);
            $new_d                     = self::set_direccion($du, $comunas);
            $new_d['idpedido']         = $carro['idpedido'];
            $new_d['idpedidoestado']   = 9; //pedido no pagado, porque esta en el carro
            $new_d['cookie_direccion'] = $carro['cookie_pedido'] . '-' . functions::generar_pass(2);

            pedidodireccion_model::insert($new_d);
            cart::update_cart($carro['idpedido']);
            $carro              = cart::current_cart(true);
            $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $carro['idpedido']));
        }
        
        $attr = producto_model::getAll(array('tipo' => 2), array('order' => 'titulo ASC'));
        $atributos=array();
        foreach ($attr as $key => $at) {
            $atributos[$at[0]]=$at;
        }

        $iddireccion = reset($direcciones_pedido);
        $iddireccion = $iddireccion[0];

        foreach ($carro['productos'] as $key => $p) {
            $carro['productos'][$key]['atributo'] = $atributos[$p['idproductoatributo']]['titulo'];
            if (0 == $p['idpedidodireccion']) {
                $update = array('id' => $p['idpedidoproducto'], 'idpedidodireccion' => $iddireccion);
                pedidoproducto_model::update($update);
                $carro['productos'][$key]['idpedidodireccion'] = $iddireccion;
            }
        }

        $direcciones = array();
        foreach ($direcciones_pedido as $key => $dp) {
            $lista_productos = array();
            foreach ($carro['productos'] as $k => $p) {
                if ($p['idpedidodireccion'] == $dp[0]) {
                    $lista_productos[] = $p;
                    unset($carro['productos'][$k]);
                }
            }
            $fecha_entrega = (strtotime($dp['fecha_entrega']) < time()) ? "" : functions::formato_fecha(strtotime($dp['fecha_entrega']), '%F');
            $hora_entrega  = (strtotime($dp['fecha_entrega']) < time()) ? "" : functions::formato_fecha(strtotime($dp['fecha_entrega']), '%R');
            $d             = array(
                'idpedidodireccion' => $dp['idpedidodireccion'],
                'productos'         => $lista_productos,
                'direccion_entrega' => $direcciones_entrega,
                'fecha_entrega'     => $fecha_entrega,
                'horarios_entrega'  => $horarios_entrega,
                'precio'            => functions::formato_precio($dp['precio']),
            );
            foreach ($d['direccion_entrega'] as $key => $dir) {
                if ($dir[0] == $dp['idusuariodireccion']) {
                    $d['direccion_entrega'][$key]['selected'] = true;
                } else {
                    $d['direccion_entrega'][$key]['selected'] = false;
                }
            }
            foreach ($d['horarios_entrega'] as $key => $h) {
                if ($hora_entrega == $key) {
                    $d['horarios_entrega'][$key]['selected'] = true;
                } else {
                    $d['horarios_entrega'][$key]['selected'] = false;
                }
            }
            $direcciones[] = $d;
        }

        $sidebar = self::sidebar($carro);
        view::set('direcciones', $direcciones);
        view::set('fechas_bloqueadas', json_encode($fechas_bloqueadas));
        view::set('fechas_especiales', json_encode($fechas_especiales));
        view::set('sidebar', $sidebar);
        $seo_cuenta = seo_model::getById(9);
        view::set('url_direcciones', functions::generar_url(array($seo_cuenta['url'], 'direcciones'), array('next_url' => implode('/', array($url[0], 'step', 2)))));
        $seo_producto = seo_model::getById(8);
        view::set('url_product', functions::generar_url(array($seo_producto['url'])));
        view::set('url_next', functions::generar_url(array($url[0], 'step', 3)));
    }

    private static function step3($carro, $url)
    {
        $sidebar   = self::sidebar($carro);
        $atributos = producto_model::getAll(array('tipo' => 2), array('order' => 'titulo ASC'));
        foreach ($carro['productos'] as $key => $p) {
            $carro['productos'][$key]['mensaje'] = nl2br($p['mensaje']);
            foreach ($atributos as $k => $a) {
                if ($a['idproducto'] == $p['idproductoatributo']) {
                    $carro['productos'][$key]['atributo'] = $a['titulo'];
                    break;
                }
            }
        }

        $comunas             = self::get_comunas();
        $direcciones_entrega = usuariodireccion_model::getAll(array('idusuario' => $_SESSION[usuario_model::$idname . app::$prefix_site]));
        foreach ($direcciones_entrega as $key => $de) {
            $direcciones_entrega[$key]['precio'] = $comunas[$de['idcomuna']]['precio'];
            $direcciones_entrega[$key]['titulo'] = $de['titulo'] . ' (' . $de['direccion'] . " , " . $comunas[$de['idcomuna']]['titulo'] . ')';
        }

        $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $carro['idpedido']));

        $direcciones = array();
        foreach ($direcciones_pedido as $key => $dp) {
            $lista_productos = array();
            foreach ($carro['productos'] as $k => $p) {
                if ($p['idpedidodireccion'] == $dp[0]) {
                    $lista_productos[] = $p;
                    unset($carro['productos'][$k]);
                }
            }
            $fecha_entrega = (strtotime($dp['fecha_entrega']) < time()) ? "" : functions::formato_fecha(strtotime($dp['fecha_entrega']), '%F');
            $hora_entrega  = (strtotime($dp['fecha_entrega']) < time()) ? "" : functions::formato_fecha(strtotime($dp['fecha_entrega']), '%R');

            foreach ($direcciones_entrega as $key => $dir) {
                if ($dir[0] == $dp['idusuariodireccion']) {
                    $direccion_entrega = $dir['titulo'];
                    break;
                }
            }

            $d = array(
                'idpedidodireccion' => $dp['idpedidodireccion'],
                'productos'         => $lista_productos,
                'direccion_entrega' => $direccion_entrega,
                'fecha_entrega'     => $fecha_entrega,
                'hora_entrega'      => $hora_entrega,
                'precio'            => functions::formato_precio($dp['precio']),
            );
            $direcciones[] = $d;
        }

        view::set('direcciones', $direcciones);
        view::set('sidebar', $sidebar);
        $seo_cuenta = seo_model::getById(9);
        view::set('url_direcciones', functions::generar_url(array($seo_cuenta['url'], 'direcciones'), array('next_url' => implode('/', array($url[0], 'step', 2)))));
        $seo_producto = seo_model::getById(8);
        view::set('url_product', functions::generar_url(array($seo_producto['url'])));
        view::set('url_next', '');
    }

    /**
     * set_direccion
     * crea un array con la direccion para guardar en pedidodireccion
     *
     * @param  array $direccion
     * @param  array $comunas
     *
     * @return array
     */
    private static function set_direccion(array $direccion, array $comunas): array
    {
        $new_d                       = array();
        $new_d['idusuariodireccion'] = $direccion[0];
        $new_d['precio']             = $direccion['precio'];
        $new_d['nombre']             = $direccion['nombre'];
        $new_d['telefono']           = $direccion['telefono'];
        $new_d['referencias']        = $direccion['referencias'];
        $new_d['direccion_completa'] = $direccion['direccion'] . ', ' . $comunas[$direccion['idcomuna']]['titulo'];
        $extra                       = '';
        $extra .= ('' != $direccion['villa']) ? ', villa ' . $direccion['villa'] : '';
        $extra .= ('' != $direccion['edificio']) ? ', edificio ' . $direccion['edificio'] : '';
        $extra .= ('' != $direccion['departamento']) ? ', departamento ' . $direccion['departamento'] : '';
        $extra .= ('' != $direccion['condominio']) ? ', condominio ' . $direccion['condominio'] : '';
        $extra .= ('' != $direccion['casa']) ? ', casa ' . $direccion['casa'] : '';
        $extra .= ('' != $direccion['empresa']) ? ', empresa ' . $direccion['empresa'] : '';

        if ('' != $extra) {
            $extra = substr($extra, 1);
            $new_d['direccion_completa'] .= ';' . $extra;
        }

        return $new_d;
    }

    /**
     * get_comunas
     * retorna array de comunas con id como key, y precio
     *
     * @return array
     */
    private static function get_comunas(): array
    {
        $com     = comuna_model::getAll();
        $comunas = array();
        foreach ($com as $key => $c) {
            if ($c['precio'] < 1) {
                $r           = region_model::getById($c['idregion']);
                $c['precio'] = $r['precio'];
            }
            $comunas[$c[0]] = $c;
        }
        return $comunas;
    }

    /**
     * change_productodireccion
     * cambia el mensaje en el producto correspondiente al pedido actual
     * si el producto no corresponde al pedido, lanza error
     *
     * @param  POST $id
     * @param  POST $cantidad
     *
     * @return json
     */

    public static function change_productodireccion()
    {
        $respuesta = array('exito' => false, 'mensaje' => 'No has modificado un producto valido. Por favor recarga la pagina e intenta nuevamente');
        $campos    = functions::test_input($_POST);
        if (isset($campos['idfinal']) && isset($campos['idpedidoproducto'])) {
            $carro = cart::current_cart(true);
            if (isset($carro['productos'])) {
                foreach ($carro['productos'] as $key => $p) {
                    if ($p['idpedidoproducto'] == $campos['idpedidoproducto']) {
                        $update             = array('id' => $p['idpedidoproducto'], 'idpedidodireccion' => ($campos['idfinal']));
                        $idpedidoproducto   = pedidoproducto_model::update($update);
                        $respuesta['exito'] = true;
                        break;
                    }
                }
            }
        }
        echo json_encode($respuesta);
        exit;
    }

    /**
     * change_direccion
     * cambia la direccion en el grupo de productos
     *
     * @param  POST $idusuariodireccion
     * @param  POST $idpedidodireccion
     *
     * @return json
     */

    public static function change_direccion()
    {
        $respuesta = array('exito' => false, 'mensaje' => 'No has modificado una direccion valida. Por favor recarga la pagina e intenta nuevamente');
        $campos    = functions::test_input($_POST);
        if (isset($campos['idusuariodireccion']) && isset($campos['idpedidodireccion'])) {
            $carro              = cart::current_cart(true);
            $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $carro['idpedido']));
            $comunas            = self::get_comunas();

            foreach ($direcciones_pedido as $key => $d) {
                if ($d['idpedidodireccion'] == $campos['idpedidodireccion']) {
                    $usuario_direccion           = usuariodireccion_model::getById($campos['idusuariodireccion']);
                    $usuario_direccion['precio'] = $comunas[$usuario_direccion['idcomuna']]['precio'];
                    $update                      = self::set_direccion($usuario_direccion, $comunas);
                    $update['id']                = $d['idpedidodireccion'];
                    $idpedidoproducto            = pedidodireccion_model::update($update);
                    $respuesta['exito']          = true;
                    $respuesta['precio']         = $usuario_direccion['precio'];
                    break;
                }
            }
        }
        cart::update_cart($carro['idpedido']);
        echo json_encode($respuesta);
        exit;
    }

    /**
     * change_fecha
     * cambia la fecha en el grupo de productos
     *
     * @param  POST $idusuariodireccion
     * @param  POST $fecha
     * @param  POST $hora
     *
     * @return json
     */

    public static function change_fecha()
    {
        $respuesta = array('exito' => false, 'mensaje' => 'No has modificado una direccion valida. Por favor recarga la pagina e intenta nuevamente');
        $campos    = functions::test_input($_POST);
        if (isset($campos['idpedidodireccion']) && isset($campos['fecha']) && isset($campos['hora'])) {
            $carro              = cart::current_cart(true);
            $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $carro['idpedido']));

            foreach ($direcciones_pedido as $key => $d) {
                if ($d['idpedidodireccion'] == $campos['idpedidodireccion']) {
                    $update             = array('fecha_entrega' => $campos['fecha'] . ' ' . $campos['hora']);
                    $update['id']       = $d['idpedidodireccion'];
                    $idpedidoproducto   = pedidodireccion_model::update($update);
                    $respuesta['exito'] = true;
                    break;
                }
            }
        }
        echo json_encode($respuesta);
        exit;
    }

    /**
     * new_direccion
     * Crea un nuevo grupo de productos a un pedido
     *
     * @return json
     */
    public static function new_direccion(): json
    {
        $respuesta = array('exito' => false, 'mensaje' => 'No has modificado una direccion valida. Por favor recarga la pagina e intenta nuevamente');
        $campos    = functions::test_input($_POST);
        $carro     = cart::current_cart(true);

        $comunas             = self::get_comunas();
        $direcciones_entrega = usuariodireccion_model::getAll(array('idusuario' => $_SESSION[usuario_model::$idname . app::$prefix_site]));
        foreach ($direcciones_entrega as $key => $de) {
            $direcciones_entrega[$key]['precio'] = $comunas[$de['idcomuna']]['precio'];
            $direcciones_entrega[$key]['titulo'] = $de['titulo'] . ' (' . $de['direccion'] . " , " . $comunas[$de['idcomuna']]['titulo'] . ')';
        }

        $du                        = reset($direcciones_entrega);
        $new_d                     = self::set_direccion($du, $comunas);
        $new_d['idpedido']         = $carro['idpedido'];
        $new_d['idpedidoestado']   = 9; //pedido no pagado, porque esta en el carro
        $new_d['cookie_direccion'] = $carro['cookie_pedido'] . '-' . functions::generar_pass(2);

        $id_new                          = pedidodireccion_model::insert($new_d);
        $id_direccion                    = $du[0];
        $respuesta['exito']              = true;
        $respuesta['idpedidodireccion']  = $id_new;
        $respuesta['idusuariodireccion'] = $id_direccion;
        cart::update_cart($carro['idpedido']);

        echo json_encode($respuesta);
        exit;
    }

    /**
     * remove_direccion
     * quita una direccion del pedido
     *
     * @param  POST $id
     *
     * @return json
     */
    public function remove_direccion(): json
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST);
        if (!isset($campos['id'])) {
            $respuesta['mensaje'] = 'No has seleccionado una direccion valida, por favor actualiza la pagina e intenta nuevamente';
            echo json_encode($respuesta);
            exit;
        }
        $carro = cart::current_cart(true);

        $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $carro['idpedido']));

        foreach ($direcciones_pedido as $key => $d) {
            if ($d['idpedidodireccion'] == $campos['id']) {
                pedidodireccion_model::delete($d['idpedidodireccion']);
                $respuesta['exito'] = true;
                break;
            }
        }

        $cantidad = 0;
        foreach ($carro['productos'] as $key => $p) {
            if ($p['idpedidodireccion'] == $campos['id']) {
                $producto = producto_model::getById($p['idproducto']);
                if (isset($producto['precio']) && $producto['precio'] > 0) {
                    $cantidad            = $p['cantidad'];
                    $cantidad_final      = $producto['stock'] + $cantidad;
                    $actualizar_producto = array('id' => $producto[0], 'stock' => $cantidad_final);
                    producto_model::update($actualizar_producto);
                }
                pedidoproducto_model::delete($p['idpedidoproducto']);
            }
        }

        cart::update_cart($carro['idpedido']);

        $respuesta['mensaje'] = 'Direccion eliminada';
        $respuesta['exito']   = true;
        echo json_encode($respuesta);
        exit;
    }

    /**
     * crear_pedido
     * Modifica el estado del pedido actual. Esto elimina el carro actual.
     * actualiza el precio y los datos basicos del pedido
     * genera la url para ver el detalle del pedido y pagarlo
     *
     * @return json
     */
    public function crear_pedido(): json
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $carro     = cart::current_cart(true);
        if (count($carro) == 0) {
            $respuesta['mensaje'] = 'Tu pedido ya fue guardado, por favor ve a la seccion "Mis pedidos" en tu cuenta';
            echo json_encode($respuesta);
            exit;
        } else {
            $attr      = producto_model::getAll(array('tipo' => 2), array('order' => 'titulo ASC'));
            $atributos = array();
            foreach ($attr as $key => $lp) {
                $atributos[$lp['idproducto']] = $lp['titulo'];
            }
            foreach ($carro['productos'] as $key => $p) {
                $update = array('id' => $p['idproducto'], 'titulo_atributo' => $atributos[$p['idproductoatributo']]);
                pedidoproducto_model::update($update);
            }
            $update = array('idpedidoestado' => 3, 'id' => $carro['idpedido']); //estado PAGO PENDIENTE
            pedido_model::update($update);
            cart::update_cart($carro['idpedido']);
            $seo_cuenta         = seo_model::getById(9);
            $url                = functions::generar_url(array($seo_cuenta['url'], 'pedido', $carro['cookie_pedido']));
            $respuesta['url']   = $url;
            $respuesta['exito'] = true;
            echo json_encode($respuesta);
            exit;

        }
    }

}
