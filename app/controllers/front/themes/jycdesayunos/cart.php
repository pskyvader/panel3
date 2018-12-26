<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\pedido as pedido_model;
use \app\models\pedidoproducto as pedidoproducto_model;
use \app\models\producto as producto_model;
use \app\models\usuario as usuario_model;
use \core\app;
use \core\image;
use \core\functions;

class cart extends base
{
    private $tipo        = 1;
    private $prefix_site = "";
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo'], false);
        $this->prefix_site = functions::url_amigable(app::$_title);
    }
    public function index()
    {
        $this->meta($this->seo);
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'], $this->seo['subtitulo']);
        echo "asdfafdfsda";

        $footer = new footer();
        $footer->normal();
    }
    public function current_cart($return = false)
    {
        if (!isset($_SESSION['cookie_pedido' . $this->prefix_site]) || $_SESSION['cookie_pedido' . $this->prefix_site] == '') {
            $logueado = usuario_model::verificar_sesion();
            if (!$logueado) {
                if (isset($_COOKIE['cookieusuario' . $this->prefix_site])) {
                    $logueado = usuario_model::login_cookie($_COOKIE['cookieusuario' . $this->prefix_site]);
                }
            }

            if ($logueado) {
                $cart = pedido_model::getByIdusuario($_SESSION[usuario_model::$idname . $this->prefix_site]);
                if (count($cart) > 0) {
                    $_SESSION['cookie_pedido' . $this->prefix_site] = $cart['cookie_pedido'];
                }
            }
        }

        if (isset($_SESSION['cookie_pedido' . $this->prefix_site]) && $_SESSION['cookie_pedido' . $this->prefix_site] != '') {
            $cart = $this->get_cart($_SESSION['cookie_pedido' . $this->prefix_site]);
            if (count($cart) > 0) {
                if ($return) {
                    return $cart;
                } else {
                    echo json_encode($cart);
                    exit;
                }
            }
        }
        if ($return) {
            return $cart;
        } else {
            echo json_encode(array());
            exit;
        }
    }

    
    /**
     * get_cart
     * genera un array que contiene los datos del producto, y dentro un array de productos con datos procesados para ser mostrados.
     *
     * @param  string $cookie_pedido
     *
     * @return array
     */
    private function get_cart(string $cookie_pedido): array 
    {
        $pedido = pedido_model::getByCookie($cookie_pedido);
        if (count($pedido) > 0) {
            $prod      = pedidoproducto_model::getAll(array('idpedido' => $pedido[0]));
            $productos = array();
            foreach ($prod as $v => $p) {
                $portada     = image::portada($p['foto']);
                $thumb_url   = image::generar_url($portada, '');
                $new_p       = array('idpedidoproducto' => $p['idpedidoproducto'], 'titulo' => $p['titulo'], 'idproducto' => $p['idproducto'], 'foto' => $thumb_url, 'precio' => $p['precio'], 'cantidad' => $p['cantidad'], 'total' => $p['total']);
                $productos[] = $new_p;
            }
            $pedido              = array('idpedido'=>$pedido[0],'total' => $pedido['total'], 'total_original' => $pedido['total_original'], 'descuento' => $pedido['total_original'] - $pedido['total']);
            $pedido['productos'] = $productos;
            return $pedido;
        }
        return array();
    }
    /**
     * new_cart
     * crea un nuevo carro
     *
     * @return mixed
     */
    private function new_cart()
    {
        $cookie_pedido = functions::generar_pass();
        $insert        = array(
            'tipo'           => 1,
            'idpedidoestado' => 1,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'total'          => 0,
            'total_original' => 0,
            'pedido_manual'  => false,
            'cookie_pedido'  => $cookie_pedido,
        );
        if (isset($_SESSION[usuario_model::$idname . $this->prefix_site])) {
            $usuario=usuario_model::getById($_SESSION[usuario_model::$idname . $this->prefix_site]);
            if(count($usuario)>0){
                $insert['idusuario'] = $usuario[0];
                $insert['nombre'] = $usuario['nombre'];
                $insert['email'] = $usuario['email'];
                $insert['telefono'] = $usuario['telefono'];
            }
        }
        $idpedido = pedido_model::insert($insert);
        if (is_int($idpedido)) {
            $_SESSION['cookie_pedido' . $this->prefix_site] = $cookie_pedido;
            return $this->get_cart($cookie_pedido);
        }
        return false;
    }

    /**
     * add_cart
     * agrega un producto al carro
     * si no existe, crea un carro nuevo
     * EN ESTA VERSION, AGREGA PRODUCTOS REPETIDOS CON CANTIDAD 1
     * para actualizar elementos en vez de agregar con cantidad 1, se debe descomentar las lineas comentadas 
     * y desactivar el for que recorre la cantidad, dejando solo una linea para agregar el producto al carro.
     *
     * @param  POST $id
     * @param  POST $cantidad
     *
     * @return json
     */
    public function add_cart()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST);
        if(!isset($campos['id']) || !isset($campos['cantidad'])){
            $respuesta['mensaje'] = 'No has agregado un producto valido';
            echo json_encode($respuesta);
            exit;
        }
        $id=$campos['id'];
        $cantidad=$campos['cantidad'];
        $cart      = $this->current_cart(true);
        if (count($cart) == 0) {
            $cart = $this->new_cart();
            if (!is_array($cart)) {
                $respuesta['mensaje'] = 'Hubo un error al crear el carro, por favor intenta nuevamente';
                echo json_encode($respuesta);
                exit;
            }
        }

        $producto=producto_model::getById($id);
        if(!isset($producto['precio']) || $producto['precio']<=0){
            $respuesta['mensaje'] = 'No se encontro el producto que estas buscando, por favor actualiza la pagina e intenta nuevamente';
            echo json_encode($respuesta);
            exit;
        }

        $cantidad_final=$producto['stock']-$cantidad;
        if($producto['stock']<1 || $cantidad_final<1){
            $respuesta['mensaje'] = 'No hay suficientes productos disponibles';
            echo json_encode($respuesta);
            exit;
        }
        $existe=false;
        /*foreach ($cart['productos'] as $key => $p) {
            if($p['idproducto']==$producto[0]){
                $p['cantidad']+=$cantidad;
                $p['precio']+=$producto['precio_final'];
                $p['total']=$cantidad*$p['precio'];
                $cart['productos'][$key]=$p;
                $p['id']=$p['idpedidoproducto'];
                $existe=true;
                unset($p['foto']);
                pedidoproducto_model::update($p);
                break;
            }
        }*/

        if(!$existe){
            $insert=array(
                'idpedido'=>$cart['idpedido'],
                'idproducto'=>$producto[0],
                'titulo'=>$producto['titulo'],
                'precio'=>$producto['precio'],
            );

            /*
            $insert['cantidad']=$cantidad;
            $insert['total']=$producto['precio']*$cantidad;
            $idpedidoproducto=pedidoproducto_model::insert($insert);
            $new_p=array();
            $new_p['id'] = $idpedidoproducto;
            $portada     = image::portada($producto['foto']);
            $copiar      = image::copy($portada, $new_p['id'], pedidoproducto_model::$table, '', '', 'cart');
            if ($copiar['exito']) {
                $new_p['foto']    = json_encode($copiar['file']);
                $idpedidoproducto = pedidoproducto_model::update($new_p);
            }*/

            for ($i=0; $i < $cantidad; $i++) {
                $insert['cantidad']=1;
                $insert['total']=$producto['precio']*1;
                $idpedidoproducto=pedidoproducto_model::insert($insert);
                $new_p=array();
                $new_p['id'] = $idpedidoproducto;
                $portada     = image::portada($producto['foto']);
                $copiar      = image::copy($portada, $new_p['id'], pedidoproducto_model::$table, '', '', 'cart');
                if ($copiar['exito']) {
                    $new_p['foto']    = json_encode($copiar['file']);
                    $idpedidoproducto = pedidoproducto_model::update($new_p);
                }
            }
        }

        $actualizar_producto=array('id'=>$producto[0],'stock'=>$cantidad_final);
        producto_model::update($actualizar_producto);
        $respuesta['carro']= $this->current_cart(true);
        $respuesta['mensaje']= $producto['titulo'].' agregado al carro';
        echo json_encode($respuesta);
        exit;
    }
}
