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
        $verificar = $this->verificar(true);
        if (!$verificar['exito']) {
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title']);
        $sidebar   = array();
        $sidebar[] = array('title' => "Mis datos", 'active' => '', 'url' => functions::generar_url(array('cuenta', 'datos')));
        $sidebar[] = array('title' => "Mis direcciones", 'active' => '', 'url' => functions::generar_url(array('cuenta', 'direcciones')));
        $sidebar[] = array('title' => "Mis pedidos", 'active' => '', 'url' => functions::generar_url(array('cuenta', 'pedidos')));

        view::set('sidebar_user', $sidebar);
        $sidebar=view::render('user/sidebar', false, true);
        view::set('sidebar',$sidebar);
        view::render('user/detail');

        $footer = new footer();
        $footer->normal();
    }

    public function datos()
    {
        $prefix_site = functions::url_amigable(app::$_title);
        $this->meta($this->seo);
        $verificar = $this->verificar(true);
        if ($verificar['exito']) {
            $this->url[] = 'datos';
        } else {
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title']);
        $sidebar   = array();
        $sidebar[] = array('title' => "Mis datos", 'active' => 'active', 'url' => functions::generar_url(array('cuenta', 'datos')));
        $sidebar[] = array('title' => "Mis direcciones", 'active' => '', 'url' => functions::generar_url(array('cuenta', 'direcciones')));
        $sidebar[] = array('title' => "Mis pedidos", 'active' => '', 'url' => functions::generar_url(array('cuenta', 'pedidos')));

        view::set('sidebar_user', $sidebar);
        $sidebar=view::render('user/sidebar', false, true);
        view::set('sidebar',$sidebar);
        
        $usuario= usuario_model::getById($_SESSION[usuario_model::$idname . $prefix_site]);
        view::set('nombre',$usuario['nombre']);
        view::set('telefono',$usuario['telefono']);
        view::set('email',$usuario['email']);
        $token                      = sha1(uniqid(microtime(), true));
        $_SESSION['datos_token'] = array('token' => $token, 'time' => time());
        view::set('token', $token);

        view::render('user/datos');

        $footer = new footer();
        $footer->normal();
    }
    
    public function datos_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST['campos']);

        if (isset($campos['nombre']) && isset($campos['telefono']) && isset($campos['email']) && isset($campos['token'])) {
            if (isset($_SESSION['datos_token']['token']) && $_SESSION['datos_token']['token'] == $campos['token']) {
                if (time() - $_SESSION['datos_token']['time'] <= 120) {
                    $datos=array(
                        'nombre'=>$campos['nombre'],
                        'telefono'=>$campos['telefono'],
                        'email'=>$campos['email'],
                        'pass'=>(isset($campos['pass']) && $campos['pass']!='')?$campos['pass']:'',
                        'pass_repetir'=>(isset($campos['pass_repetir']) && $campos['pass_repetir']!='')?$campos['pass_repetir']:'',
                    );
                    $respuesta = usuario_model::actualizar($datos);
                    if ($respuesta['exito']) {
                        $respuesta['mensaje'] = "Datos modificados correctamente";
                    }
                } else {
                    $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
                }
            } else {
                $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
            }
        } else {
            $respuesta['mensaje'] = 'Debes llenar los campos obligatorios';
        }

        echo json_encode($respuesta);
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
        view::render('user/registro');

        $footer = new footer();
        $footer->normal();
    }
    public function registro_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST['campos']);

        if (isset($campos['nombre']) && isset($campos['telefono']) && isset($campos['email']) && isset($campos['pass']) && isset($campos['pass_repetir']) && isset($campos['token'])) {
            if (isset($_SESSION['registro_token']['token']) && $_SESSION['registro_token']['token'] == $campos['token']) {
                if (time() - $_SESSION['registro_token']['time'] <= 120) {
                    $respuesta = usuario_model::registro($campos['nombre'], $campos['telefono'], $campos['email'], $campos['pass'], $campos['pass_repetir']);
                    if ($respuesta['exito']) {
                        $respuesta['exito'] = usuario_model::login($campos['email'], $campos['pass'], isset($campos['recordar']));
                        if (!$respuesta['exito']) {
                            $respuesta['mensaje'] = "Cuenta creada correctamente, pero ha ocurrido un error al ingresar. Intenta loguearte";
                        } else {
                            $respuesta['mensaje'] = "Bienvenido";
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
        view::render('user/login');

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
                        $respuesta['mensaje'] = "Ha ocurrido un error al ingresar. Revisa tus datos e intenta nuevamente";
                    } else {
                        $respuesta['mensaje'] = "Bienvenido";
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
    public function verificar($return = false)
    {
        $respuesta   = array('exito' => false, 'mensaje' => '');
        $prefix_site = functions::url_amigable(app::$_title);
        $logueado    = usuario_model::verificar_sesion();
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
        if ($return) {
            return $respuesta;
        } else {
            echo json_encode($respuesta);
        }
    }
    public function logout()
    {
        usuario_model::logout();
        echo json_encode(array('exito' => true, 'mensaje' => 'Gracias por visitar nuestro sitio'));
    }
}
