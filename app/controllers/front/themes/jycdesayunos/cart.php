<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\pedido as pedido_model;
use \app\models\pedidodireccion as pedidodireccion_model;
use \app\models\pedidoproducto as pedidoproducto_model;
use \app\models\producto as producto_model;
use \app\models\usuario as usuario_model;
use \app\models\seo as seo_model;
use \core\app;
use \core\image;
use \core\functions;

class cart extends base
{
    private $tipo        = 1;
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo'], false);
    }
    public function index() {
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
    
    /**
     * current_cart
     * busca el carro actual, si no existe devuelve un array vacio
     * si $return es TRUE, retorna array
     * si es FALSE, retorna JSON
     *
     * @param  bool $return
     *
     * @return mixed
     */
    public static function current_cart($return = false)
    {
        $prefix_site = functions::url_amigable(app::$_title);
        if (!isset($_SESSION['cookie_pedido' . $prefix_site]) || $_SESSION['cookie_pedido' . $prefix_site] == '') {
            $logueado = usuario_model::verificar_sesion();
            if (!$logueado) {
                if (isset($_COOKIE['cookieusuario' . $prefix_site])) {
                    $logueado = usuario_model::login_cookie($_COOKIE['cookieusuario' . $prefix_site]);
                }
            }

            if ($logueado) {
                $cart = pedido_model::getByIdusuario($_SESSION[usuario_model::$idname . $prefix_site]);
                if (count($cart) > 0) {
                    $_SESSION['cookie_pedido' . $prefix_site] = $cart['cookie_pedido'];
                }
            }
        }

        if (isset($_SESSION['cookie_pedido' . $prefix_site]) && $_SESSION['cookie_pedido' . $prefix_site] != '') {
            $cart = self::get_cart($_SESSION['cookie_pedido' . $prefix_site]);
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
            return array();
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
    private static function get_cart(string $cookie_pedido): array 
    {
        $pedido = pedido_model::getByCookie($cookie_pedido);
        if (count($pedido) > 0) {
            $prod      = pedidoproducto_model::getAll(array('idpedido' => $pedido[0]));
            $productos = array();
            $seo_producto=seo_model::getById(8);
            foreach ($prod as $v => $p) {
                $portada     = image::portada($p['foto']);
                $thumb_url   = image::generar_url($portada, '');
                $producto=producto_model::getById($p['idproducto']);
                $url_producto= functions::url_seccion(array($seo_producto['url'], 'detail'), $producto);
                $new_p       = array(
                    'idpedidoproducto' => $p['idpedidoproducto'], 
                    'titulo' => $p['titulo'], 
                    'idproducto' => $p['idproducto'], 
                    'foto' => $thumb_url, 
                    'precio' => functions::formato_precio($p['precio']), 
                    'cantidad' => $p['cantidad'], 
                    'total' => $p['total'], 
                    'url'         => $url_producto
                );
                $productos[] = $new_p;
            }
            $pedido              = array(
                'idpedido'=>$pedido[0],
                'total' => functions::formato_precio( $pedido['total']), 
                'total_original' => functions::formato_precio( $pedido['total_original']), 
                'descuento' => functions::formato_precio( $pedido['total_original'] - $pedido['total'])
            );
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
    private static function new_cart()
    {
        $prefix_site = functions::url_amigable(app::$_title);
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
        if (isset($_SESSION[usuario_model::$idname . $prefix_site])) {
            $usuario=usuario_model::getById($_SESSION[usuario_model::$idname . $prefix_site]);
            if(count($usuario)>0){
                $insert['idusuario'] = $usuario[0];
                $insert['nombre'] = $usuario['nombre'];
                $insert['email'] = $usuario['email'];
                $insert['telefono'] = $usuario['telefono'];
            }
        }
        $idpedido = pedido_model::insert($insert);
        if (is_int($idpedido)) {
            $_SESSION['cookie_pedido' . $prefix_site] = $cookie_pedido;
            return self::get_cart($cookie_pedido);
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
        $cart      = self::current_cart(true);
        if (count($cart) == 0) {
            $cart = self::new_cart();
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
        if($producto['stock']<1 || $cantidad_final<0){
            $respuesta['mensaje'] = 'No hay suficientes productos disponibles';
            echo json_encode($respuesta);
            exit;
        }
        
        $existe=false;
        /*foreach ($cart['productos'] as $key => $p) {
            if($p['idproducto']==$producto[0]){
                $p['cantidad']+=$cantidad;
                $p['precio']+=$producto['precio_final'];
                $p['total']=$cantidad*$p['precio_final'];
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
                'precio'=>$producto['precio_final'],
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

            //comentar esto para funcionamiento tipico de actualizar cantidades en lugar de agregar siempre algo nuevo
            for ($i=0; $i < $cantidad; $i++) {
                $insert['cantidad']=1;
                $insert['total']=$producto['precio_final']*1;
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
            //comentar esto para funcionamiento tipico de actualizar cantidades en lugar de agregar siempre algo nuevo
        }

        self::update_cart($cart['idpedido']);




        $actualizar_producto=array('id'=>$producto[0],'stock'=>$cantidad_final);
        producto_model::update($actualizar_producto);
        $respuesta['carro']= self::current_cart(true);
        $respuesta['mensaje']= $producto['titulo'].' agregado al carro';
        $respuesta['exito']= true;
        echo json_encode($respuesta);
        exit;
    }



    /**
     * remove_cart
     * quita un producto del carro
     *
     * @param  POST $id
     *
     * @return json
     */
    public function remove_cart()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST);
        if(!isset($campos['id'])){
            $respuesta['mensaje'] = 'No has agregado un producto valido';
            echo json_encode($respuesta);
            exit;
        }
        $id=$campos['id'];
        $cart      = self::current_cart(true);
        if (count($cart) == 0) {
            $cart = self::new_cart();
            if (!is_array($cart)) {
                $respuesta['mensaje'] = 'Hubo un error al eliminar del carro, por favor actualiza la pagina e intenta nuevamente';
                echo json_encode($respuesta);
                exit;
            }
        }

        $cantidad=0;
        foreach ($cart['productos'] as $key => $p) {
            if($p['idpedidoproducto']==$id){
                $cantidad= $p['cantidad'];
                $producto=producto_model::getById($p['idproducto']);
                if(!isset($producto['precio']) || $producto['precio']<=0){
                    $respuesta['mensaje'] = 'No se encontro el producto que estas buscando, por favor actualiza la pagina e intenta nuevamente';
                    echo json_encode($respuesta);
                    exit;
                }
                pedidoproducto_model::delete($p['idpedidoproducto']);
                break;
            }
        }


        $cantidad_final=$producto['stock']+$cantidad;
        self::update_cart($cart['idpedido']);

        $actualizar_producto=array('id'=>$producto[0],'stock'=>$cantidad_final);
        producto_model::update($actualizar_producto);
        $respuesta['carro']= self::current_cart(true);
        $respuesta['mensaje']= $producto['titulo'].' eliminado del carro';
        $respuesta['exito']= true;
        echo json_encode($respuesta);
        exit;
    }











    /**
     * update_cart
     * Actualiza la informacion del pedido, segun un id de pedido enviado
     *
     * @param  int $idpedido
     *
     * @return void
     */
    public static function update_cart(int $idpedido){
        $prefix_site = functions::url_amigable(app::$_title);
        $pedido=pedido_model::getById($idpedido);
        $total=0;
        $productos= pedidoproducto_model::getAll(array('idpedido' => $pedido[0]));
        foreach ($productos as $key => $p) {
            $total+=$p['total'];
        }
        $direcciones= pedidodireccion_model::getAll(array('idpedido' => $pedido[0]));
        foreach ($direcciones as $key => $d) {
            $total+=$d['precio'];
        }

        $update=array('total'=>$total,'total_original'=>$total);
        if (isset($_SESSION[usuario_model::$idname . $prefix_site])) {
            $usuario=usuario_model::getById($_SESSION[usuario_model::$idname . $prefix_site]);
            if(count($usuario)>0){
                $update['idusuario'] = $usuario[0];
                $update['nombre'] = $usuario['nombre'];
                $update['email'] = $usuario['email'];
                $update['telefono'] = $usuario['telefono'];
            }
        }
        $update['id']=$pedido[0];
        pedido_model::update($update);
    }

}
