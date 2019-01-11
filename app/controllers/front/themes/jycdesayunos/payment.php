<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\mediopago as mediopago_model;
use \app\models\pedido as pedido_model;
use \app\models\pedidodireccion as pedidodireccion_model;
use \app\models\seo as seo_model;
use \core\app;
use \core\email;
use \core\functions;
use \core\view;
use \Transbank\Webpay\Configuration;
use \Transbank\Webpay\Webpay;

class payment extends base
{
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo']);
    }

    public function index()
    {
        $seo_home = seo_model::getById(1);
        functions::url_redirect(array($seo_home['url']));
    }
    public function medio($var = array())
    {
        $this->meta($this->seo);
        $this->url[] = 'medio';

        if (isset($var[0]) && isset($var[1])) {
            $idmedio    = functions::test_input($var[0]);
            $medio_pago = mediopago_model::getById($idmedio);
            if (isset($medio_pago['estado'])) {
                $this->url[] = $idmedio;
                $cookie      = functions::test_input($var[1]);
                $this->url[] = $cookie;
            } else {
                $seo_home  = seo_model::getById(1);
                $this->url = array($seo_home['url']);
            }
        } else {
            $seo_home  = seo_model::getById(1);
            $this->url = array($seo_home['url']);
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Pago via ' . $medio_pago['titulo'], $this->metadata['title']);

        $error   = false;
        $mensaje = '';
        if (!$medio_pago['estado']) {
            $error   = true;
            $mensaje = 'Medio de pago no disponible, Por favor intenta con otro medio de pago';
        } else {
            $pedido = pedido_model::getByCookie($cookie, false);
            if (!isset($pedido['cookie_pedido'])) {
                $error   = true;
                $mensaje = 'Pedido no valido, Por favor ingresa a tu cuenta y selecciona un pedido valido';
            } elseif (3 != $pedido['idpedidoestado'] && 7 != $pedido['idpedidoestado']) {
                $error   = true;
                $mensaje = 'Este pedido no se puede procesar, ya está pagado o aún no se ha completado.';
            }
        }

        if ($error) {
            view::set('mensaje', $mensaje);
            view::render('order/error');
        } else {
            if (2 == $medio_pago[0]) { //  WEBPAY
                $transaction = (new Webpay(Configuration::forTestingWebpayPlusNormal()))->getNormalTransaction();
                $amount      = 1000;
                // Identificador que será retornado en el callback de resultado:
                $sessionId = $cookie;
                // Identificador único de orden de compra:
                $buyOrder   = strval(rand(100000, 999999999));
                $returnUrl  = "https://callback/resultado/de/transaccion";
                $finalUrl   = "https://callback/final/post/comprobante/webpay";
                $initResult = $transaction->initTransaction(
                    $amount, $buyOrder, $sessionId, $returnUrl, $finalUrl);

                $formAction = $initResult->url;
                $tokenWs    = $initResult->token;
                var_dump($formAction);
                var_dump($tokenWs);

            }
            $seo_cuenta = seo_model::getById(9);
            view::set('title', $medio_pago['titulo']);
            view::set('description', $medio_pago['resumen']);
            view::set('url_back', functions::generar_url(array($seo_cuenta['url'], 'pedido', $cookie)));
            view::set('url_next', functions::generar_url(array($this->url[0], 'pago' . $medio_pago[0], $cookie)));
            view::render('payment/resumen');
        }
        $footer = new footer();
        $footer->normal();
    }

    public function pago1($var = array())
    {
        $this->meta($this->seo);
        $this->url[] = 'pago1';

        if (isset($var[0])) {
            $idmedio    = functions::test_input(1);
            $medio_pago = mediopago_model::getById($idmedio);
            if (isset($medio_pago['estado'])) {
                $cookie      = functions::test_input($var[0]);
                $this->url[] = $cookie;
            } else {
                $seo_home  = seo_model::getById(1);
                $this->url = array($seo_home['url']);
            }
        } else {
            $seo_home  = seo_model::getById(1);
            $this->url = array($seo_home['url']);
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Pago via ' . $medio_pago['titulo'], $this->metadata['title']);

        $error   = false;
        $mensaje = '';
        if (!$medio_pago['estado']) {
            $error   = true;
            $mensaje = 'Medio de pago no disponible, Por favor intenta con otro medio de pago';
        } else {
            $pedido = pedido_model::getByCookie($cookie, false);
            if (!isset($pedido['cookie_pedido'])) {
                $error   = true;
                $mensaje = 'Pedido no valido, Por favor ingresa a tu cuenta y selecciona un pedido valido';
            } elseif (3 != $pedido['idpedidoestado'] && 7 != $pedido['idpedidoestado']) {
                $error   = true;
                $mensaje = 'Este pedido no se puede procesar, ya está pagado o aún no se ha completado.';
            }
        }

        if ($error) {
            view::set('mensaje', $mensaje);
            view::render('order/error');
        } else {
            $lista_direcciones = pedidodireccion_model::getAll(array('idpedido' => $pedido[0]));
            $update_pedido     = array('id' => $pedido[0], 'idpedidoestado' => 10, 'idmediopago' => $medio_pago[0]); // estado de pedido: esperando transferencia
            pedido_model::update($update_pedido);
            foreach ($lista_direcciones as $key => $direccion) {
                $update_pedido = array('id' => $direccion[0], 'idpedidoestado' => 9); // estado de direccion: pago pendiente
                pedidodireccion_model::update($update_pedido);
            }
            $seo_cuenta                  = seo_model::getById(9);
            $url_back                    = functions::generar_url(array($seo_cuenta['url'], 'pedido', $cookie));
            $titulo                      = "Pedido " . $pedido['cookie_pedido'] . " Esperando transferencia";
            $cabecera                    = "Estimado " . $pedido['nombre'] . ", " . $medio_pago['descripcion'];
            $campos                      = array();
            $campos['Código de pedido'] = $cookie;
            $campos['Total del pedido']  = functions::formato_precio($pedido['total']);

            $respuesta = self::email($pedido, $titulo, $cabecera, $campos, $url_back);

            view::set('title', $medio_pago['titulo']);
            view::set('description', $medio_pago['descripcion']);
            view::set('url_back', $url_back);
            view::render('payment/confirmation');
        }
        $footer = new footer();
        $footer->normal();
    }

    private static function email($pedido, $titulo = '', $cabecera = '', $campos = array(), $url_pedido)
    {
        $nombre_sitio  = app::$_title;
        $config        = app::getConfig();
        $email_empresa = $config['main_email'];
        $body_email    = array(
            'body'     => view::get_theme() . 'mail/pedido.html',
            'titulo'   => "Pedido " . $pedido['cookie_pedido'] . " Pago realizado",
            'cabecera' => "Estimado " . $pedido['nombre'] . ", aquí enviamos su información de pago. Si tiene alguna duda, no dude en contactarse con el centro de atención al cliente de " . $nombre_sitio,
        );
        if ('' != $cabecera) {
            $body_email['cabecera'] = $cabecera;
        }
        if ('' != $titulo) {
            $body_email['titulo'] = $titulo;
        }

        $body_email['campos_largos'] = array('' => 'Puedes ver el detalle de tu pedido <a href="' . $url_pedido . '"><b>haciendo click aquí</b></a>');
        $body_email['campos']        = $campos;
        $imagenes                    = array();

        $adjuntos = array();
        /*if (isset($_FILES)) {
        foreach ($_FILES as $key => $file) {
        $adjuntos[] = array('archivo' => $file['tmp_name'], 'nombre' => $file['name']);
        }
        }*/
        $body      = email::body_email($body_email);
        $respuesta = email::enviar_email(array($pedido['email'], $email_empresa), $body_email['titulo'], $body, $adjuntos, $imagenes);
        return $respuesta;
    }
}
