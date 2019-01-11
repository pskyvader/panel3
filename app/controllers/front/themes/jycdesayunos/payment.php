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
    private $cookie               = '';
    private $configuration_webpay = null;
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo'], false);
        $this->configuration_webpay = Configuration::forTestingWebpayPlusNormal();
    }

    public function index()
    {
        $seo_home = seo_model::getById(1);
        functions::url_redirect(array($seo_home['url']));
    }
    /**
     * verificar_medio_pago
     * comprueba si existe el medio de pago, sino vuelve al home
     *
     *
     * @param  string $cookie
     * @param  int $idmedio
     *
     * @return mixed
     */
    private function verificar_medio_pago(string $cookie = '', int $idmedio)
    {
        $medio_pago = null;
        if ('' != $cookie) {
            $mp = mediopago_model::getById($idmedio);
            if (isset($mp['estado'])) {
                $cookie       = functions::test_input($cookie);
                $this->cookie = $cookie;
                $this->url[]  = $cookie;
                $medio_pago   = $mp;
            } else {
                $seo_home  = seo_model::getById(1);
                $this->url = array($seo_home['url']);
            }
        } else {
            $seo_home  = seo_model::getById(1);
            $this->url = array($seo_home['url']);
        }
        return $medio_pago;
    }

    /**
     * verificar_pedido
     * comprueba si el pedido existe y es valido para pagos, sino devuelve null
     *
     * @param  mixed $medio_pago
     *
     * @return mixed
     */
    private function verificar_pedido(array $medio_pago)
    {
        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Pago via ' . $medio_pago['titulo'], $this->metadata['title']);

        $mensaje = '';
        if (!$medio_pago['estado']) {
            $mensaje = 'Medio de pago no disponible, Por favor intenta con otro medio de pago';
        } else {
            $pedido = pedido_model::getByCookie($this->cookie, false);
            if (!isset($pedido['cookie_pedido'])) {
                $mensaje = 'Pedido no valido, Por favor ingresa a tu cuenta y selecciona un pedido valido';
            } elseif (3 != $pedido['idpedidoestado'] && 7 != $pedido['idpedidoestado']) {
                $mensaje = 'Este pedido no se puede procesar, ya está pagado o aún no se ha completado.';
            }
        }

        if ('' != $mensaje) {
            view::set('mensaje', $mensaje);
            view::render('order/error');
            return null;
        } else {
            return $pedido;
        }

    }
    public function medio($var = array())
    {
        $this->meta($this->seo);
        $this->url[] = 'medio';
        $idmedio     = -1;
        if (isset($var[0])) {
            $idmedio     = functions::test_input($var[0]);
            $this->url[] = $idmedio;
        }
        $medio_pago = $this->verificar_medio_pago($var[1], $idmedio);
        functions::url_redirect($this->url);

        $pedido  = $this->verificar_pedido($medio_pago);
        $is_post = false;
        $action  = '';
        $form    = array();

        if (null != $pedido) {
            if (2 == $medio_pago[0]) { //  WEBPAY
                try {
                    $transaction = (new Webpay($this->configuration_webpay))->getNormalTransaction();
                    $amount      = $pedido['total'];
                    // Identificador que será retornado en el callback de resultado:
                    $sessionId = $pedido['cookie_pedido'];
                    // Identificador único de orden de compra:
                    // $buyOrder   = strval(rand(100000, 999999999));
                    $buyOrder   = $pedido['cookie_pedido'];
                    $returnUrl  = functions::generar_url(array($this->url[0], 'process' . $medio_pago[0], $pedido['cookie_pedido']));
                    $finalUrl   = functions::generar_url(array($this->url[0], 'pago' . $medio_pago[0], $pedido['cookie_pedido']));
                    $initResult = $transaction->initTransaction($amount, $buyOrder, $sessionId, $returnUrl, $finalUrl);

                    $formAction = $initResult->url;
                    $tokenWs    = $initResult->token;
                    $is_post    = true;
                    $action     = $formAction;
                    $form[]     = array('field' => 'token_ws', 'value' => $tokenWs);
                } catch (\Exception $e) {
                    $mensaje = "Hubo un error al inicial el proceso webpay. Por favor intenta más tarde.";
                    if (error_reporting()) {
                        $mensaje .= '<br/><br/><br/><br/>' . $e;
                    }
                    view::set('mensaje', $mensaje);
                    view::render('order/error');
                    $pedido = null;
                }
            }
        }

        if (null != $pedido) {
            $seo_cuenta = seo_model::getById(9);
            view::set('title', $medio_pago['titulo']);
            view::set('description', $medio_pago['resumen']);
            view::set('is_post', $is_post);
            view::set('action', $action);
            view::set('form', $form);
            view::set('url_back', functions::generar_url(array($seo_cuenta['url'], 'pedido', $pedido['cookie_pedido'])));
            view::set('url_next', functions::generar_url(array($this->url[0], 'pago' . $medio_pago[0], $pedido['cookie_pedido'])));
            view::render('payment/resumen');
        }
        $footer = new footer();
        $footer->normal();
    }

    public function pago1($var = array())
    {
        $this->meta($this->seo);
        $this->url[] = 'pago1';
        $idmedio     = 1;
        $medio_pago  = $this->verificar_medio_pago($var[0], $idmedio);
        functions::url_redirect($this->url);

        $pedido = $this->verificar_pedido($medio_pago);
        if (null != $pedido) {
            $lista_direcciones = pedidodireccion_model::getAll(array('idpedido' => $pedido[0]));
            $update_pedido     = array('id' => $pedido[0], 'idpedidoestado' => 10, 'idmediopago' => $medio_pago[0]); // estado de pedido: esperando transferencia
            pedido_model::update($update_pedido);
            foreach ($lista_direcciones as $key => $direccion) {
                $update_pedido = array('id' => $direccion[0], 'idpedidoestado' => 9); // estado de direccion: pago pendiente
                pedidodireccion_model::update($update_pedido);
            }
            $seo_cuenta                  = seo_model::getById(9);
            $url_back                    = functions::generar_url(array($seo_cuenta['url'], 'pedido', $pedido['cookie_pedido']));
            $titulo                      = "Pedido " . $pedido['cookie_pedido'] . " Esperando transferencia";
            $cabecera                    = "Estimado " . $pedido['nombre'] . ", " . $medio_pago['descripcion'];
            $campos                      = array();
            $campos['Código de pedido'] = $pedido['cookie_pedido'];
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

    public function process2($var = array())
    {
        $error   = true;
        $mensaje = '';
        $campos  = functions::test_input($_POST);
        if (isset($campos['token_ws'])) {
            $token = $campos['token_ws'];

            $transaction = (new Webpay($this->configuration_webpay))->getNormalTransaction();
            $result      = $transaction->getTransactionResult($token);
            $output      = $result->detailOutput;

            if (0 == $output->responseCode) {
                $error = false;
                echo 'Exito, guardar datos y cambiar estado y enviar correo';
            } else {
                $result->sessionId;
                $result->transactionDate;
                $result->urlRedirection;

                $output->authorizationCode;
                $output->responseCode;
                $output->amount;
                $output->buyOrder;
                $error   = true;
                $mensaje = 'Hubo un error al procesar tu pago, por favor intenta más tarde o selecciona otro medio de pago.';
            }
        }

        if ($error) {
            $head = new head($this->metadata);
            $head->normal();

            $header = new header();
            $header->normal();

            $banner = new banner();
            $banner->individual($this->seo['banner'], 'Pago via ' . $medio_pago['titulo'], $this->metadata['title']);
            view::set('mensaje', $mensaje);
            view::render('order/error');
            $footer = new footer();
            $footer->normal();
        }

    }

    private static function email($pedido, $titulo = '', $cabecera = '', $campos = array(), $url_pedido)
    {
        $nombre_sitio                = app::$_title;
        $config                      = app::getConfig();
        $email_empresa               = $config['main_email'];
        $body_email                  = array('body' => view::get_theme() . 'mail/pedido.html');
        $body_email['cabecera']      = $cabecera;
        $body_email['titulo']        = $titulo;
        $body_email['campos_largos'] = array('' => 'Puedes ver el detalle de tu pedido <a href="' . $url_pedido . '"><b>haciendo click aquí</b></a>');
        $body_email['campos']        = $campos;
        $imagenes                    = array();
        $adjuntos                    = array();
        $body                        = email::body_email($body_email);
        $respuesta                   = email::enviar_email(array($pedido['email'], $email_empresa), $body_email['titulo'], $body, $adjuntos, $imagenes);
        return $respuesta;
    }
}
