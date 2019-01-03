<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\comuna as comuna_model;
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
        3,
        4,
        5,
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
            $this->url        = array('cuenta', 'login');
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
            $class = "step" . $current_step;
            self::$class($carro, $this->url);
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

    private static function step2($carro, $url)
    {
        $sidebar = self::sidebar($carro);

        $atributos = producto_model::getAll(array('tipo' => 2), array('order' => 'titulo ASC'));
        foreach ($carro['productos'] as $key => $p) {
            foreach ($atributos as $k => $a) {
                if ($a['idproducto'] == $p['idproductoatributo']) {
                    $carro['productos'][$key]['atributo'] = $a['titulo'];
                    break;
                }
            }
        }

        $com     = comuna_model::getAll();
        $comunas = array();
        foreach ($com as $key => $c) {
            if ($c['precio'] > 1) {
                $r           = region_model::getById($c['idregion']);
                $c['precio'] = $r['precio'];
            }
            $comunas[$c[0]] = $c;
        }
        $direcciones_entrega = usuariodireccion_model::getAll(array('idusuario' => $_SESSION[usuario_model::$idname . app::$prefix_site]));
        foreach ($direcciones_entrega as $key => $de) {
            $direcciones_entrega[$key]['precio'] = $comunas[$de['idcomuna']]['precio'];
            $direcciones_entrega[$key]['titulo'] = $de['titulo'] . ' (' . $de['direccion'] . ')';
        }

        $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $carro['idpedido']));
        if (count($direcciones_pedido) == 0) {
            $du    = reset($direcciones_entrega);
            $new_d = array(
                'idpedido'           => $carro['idpedido'],
                'idusuariodireccion' => $du[0],
                'idpedidoestado'     => 9, //pedido no pagado, porque esta en el carro
                'precio'             => $du['precio'],
                'cookie_direccion'   => $carro['cookie_pedido'] . '-' . functions::generar_pass(2),
            );

            $new_d['nombre']             = $du['nombre'];
            $new_d['telefono']           = $du['telefono'];
            $new_d['referencias']        = $du['referencias'];
            $new_d['direccion_completa'] = $du['direccion'] . ', ' . $comunas[$du['idcomuna']]['titulo'] . ';';
            $new_d['direccion_completa'] .= ('' != $du['villa']) ? ', villa ' . $du['villa'] : '';
            $new_d['direccion_completa'] .= ('' != $du['edificio']) ? ', edificio ' . $du['edificio'] : '';
            $new_d['direccion_completa'] .= ('' != $du['departamento']) ? ', departamento ' . $du['departamento'] : '';
            $new_d['direccion_completa'] .= ('' != $du['condominio']) ? ', condominio ' . $du['condominio'] : '';
            $new_d['direccion_completa'] .= ('' != $du['casa']) ? ', casa ' . $du['casa'] : '';
            $new_d['direccion_completa'] .= ('' != $du['empresa']) ? ', empresa ' . $du['empresa'] : '';
            $idnew_d = pedidodireccion_model::insert($new_d);
            foreach ($carro['productos'] as $key => $p) {
                $update = array('id' => $p['idpedidoproducto'], 'idpedidodireccion' => $idnew_d);
                pedidoproducto_model::update($update);
                $carro['productos'][$key]['idpedidodireccion'] = $idnew_d;
            }
            $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $carro['idpedido']));
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

            $d = array(
                'productos'         => $lista_productos,
                'direccion_entrega' => $direcciones_entrega,
                'fecha_entrega'     => $dp['fecha_entrega'],
                'precio'            => functions::formato_precio($dp['precio']),
            );
            foreach ($d['direccion_entrega'] as $key => $dir) {
                if ($dir[0] == $dp['idusuariodireccion']) {
                    $d['direccion_entrega'][$key]['selected'] = true;
                } else {
                    $d['direccion_entrega'][$key]['selected'] = false;
                }
            }
            $direcciones[] = $d;
        }

        view::set('direcciones', $direcciones);
        view::set('sidebar', $sidebar);
        $seo_cuenta = seo_model::getById(9);
        view::set('url_new', functions::generar_url(array($seo_cuenta['url'], 'direccion')));
        $seo_producto = seo_model::getById(8);
        view::set('url_product', functions::generar_url(array($seo_producto['url'])));
        view::set('url_next', functions::generar_url(array($url[0], 'step', 3)));
    }
}
