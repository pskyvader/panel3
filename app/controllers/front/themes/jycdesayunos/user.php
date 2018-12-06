<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\usuario as usuario_model;
use \core\app;
use \core\functions;
use \core\view;

class user extends base
{
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo'], false);
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
        $banner->individual($this->seo['banner'], $this->metadata['title']);

        $footer = new footer();
        $footer->normal();
    }
    public function registro()
    {
        $this->meta($this->seo);
        $this->url[] = 'registro';
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Registro');

        $token                      = sha1(uniqid(microtime(), true));
        $_SESSION['registro_token'] = array('token' => $token, 'time' => time());
        view::set('token', $token);
        view::render('user-registro');

        $footer = new footer();
        $footer->normal();
    }
    public function registro_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST['campos']);

        if (isset($campos['nombre']) && isset($campos['email']) && isset($campos['pass']) && isset($campos['pass_repetir']) && isset($campos['token'])) {
            if (isset($_SESSION['registro_token']['token']) && $_SESSION['registro_token']['token'] == $campos['token']) {
                if (time() - $_SESSION['registro_token']['time'] <= 120) {
                    $respuesta = usuario_model::registro($campos['nombre'], $campos['email'], $campos['pass'], $campos['pass_repetir']);
                    if ($respuesta['exito']) {
                        $respuesta['exito'] = usuario_model::login($campos['email'], $campos['pass'], isset($campos['recordar']));
                        if (!$respuesta['exito']) {
                            $respuesta['mensaje'] = "Cuenta creada correctamente, pero ha ocurrido un error al ingresar. Intenta loguearte";
                        }
                    }
                } else {
                    $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
                }
            } else {
                $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
            }
        } else {
            $respuesta['mensaje'] = 'Debes llenar todos los campos';
        }

        echo json_encode($respuesta);
    }
    public function login()
    {
        $this->meta($this->seo);
        $this->url[] = 'login';
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Login');

        $token                   = sha1(uniqid(microtime(), true));
        $_SESSION['login_token'] = array('token' => $token, 'time' => time());
        view::set('token', $token);
        view::render('user-login');

        $footer = new footer();
        $footer->normal();
    }
    public function login_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST['campos']);

        if (isset($campos['email']) && isset($campos['pass']) && isset($campos['token'])) {
            if (isset($_SESSION['login_token']['token']) && $_SESSION['login_token']['token'] == $campos['token']) {
                if (time() - $_SESSION['login_token']['time'] <= 120) {
                    $respuesta['exito'] = usuario_model::login($campos['email'], $campos['pass'], isset($campos['recordar']));
                    if (!$respuesta['exito']) {
                        $respuesta['mensaje'] = "Cuenta creada correctamente, pero ha ocurrido un error al ingresar. Intenta loguearte";
                    }
                } else {
                    $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
                }
            } else {
                $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
            }
        } else {
            $respuesta['mensaje'] = 'Debes llenar todos los campos';
        }

        echo json_encode($respuesta);
    }
    public function verificar()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $prefix_site = functions::url_amigable(app::$_title);
        $logueado  = usuario_model::verificar_sesion();
        if (!$logueado) {
            if (isset($_COOKIE['cookieusuario' . $prefix_site])) {
                $logueado = usuario_model::login_cookie($_COOKIE['cookieusuario' . $prefix_site]);
            }
        }
        $respuesta['exito'] = $logueado;
        if ($logueado) {
            $nombre               = explode(" ", $_SESSION['nombreusuario' . $prefix_site]);
            $respuesta['mensaje'] = $nombre[0];
        }
        echo json_encode($respuesta);
    }
    public function logout(){
        usuario_model::logout();
        echo json_encode(array('exito' => true, 'mensaje' => 'Gracias por visitar nuestro sitio'));
    }
}
