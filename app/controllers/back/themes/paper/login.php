<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use core\functions;
use \core\view;
use \core\image;
use \app\models\logo as logo_model;
use \app\models\administrador as administrador_model;

class login
{
    private $url = array('login','index');
    private $metadata = array('title' => 'Login','modulo'=>'login');
    public function index($url=array())
    {
        $this->url=array_merge($this->url,$url);
        if (isset($_COOKIE['cookieadmin' . app::$prefix_site])) {
            $logueado = administrador_model::login_cookie($_COOKIE['cookieadmin' . app::$prefix_site]);
            if ($logueado) {
                if(empty($url)) $this->url = array('home');
                else $this->url=$url;
            }
        }


        if(isset($_SESSION['bloqueo_administrador']) && $_SESSION['bloqueo_administrador']>time()){
            exit("IP Bloqueada por intentos fallidos. Intente más tarde. tiempo: ".(intval(time())-intval($_SESSION['bloqueo_administrador']))." segundos");
        }
        
        if(isset($_SESSION['intento_administrador']) && $_SESSION['intento_administrador']%5==0){
            $_SESSION['bloqueo_administrador']=time()+60*(intval($_SESSION['intento_administrador'])/5);
            if($_SESSION['intento_administrador']>=15) bloquear_ip(getRealIP());
            $_SESSION['intento_administrador']++;
        }

        $error_login=false;
        if(isset($_POST['email']) && isset($_POST['pass']) && isset($_POST['token'])){
            if($_SESSION['login_token']['token']==$_POST['token']){
                if(time()-$_SESSION['login_token']['time']<=120){
                    if(!isset($_POST['recordar'])) $_POST['recordar']='';
                    $logueado=administrador_model::login($_POST['email'],$_POST['pass'],$_POST['recordar']);
                    if($logueado) {
                        if(isset($_SESSION['intento_administrador'])) $_SESSION['intento_administrador']=0;
                        if(empty($url)) $this->url = array('home');
                        else $this->url=$url;
                    }else {
                        $error_login=true;
                        if(!isset($_SESSION['intento_administrador'])) $_SESSION['intento_administrador']=0;
                        $_SESSION['intento_administrador']++;
                    }
                }else{
                    $error_login=true;
                }
            }else{
                $error_login=true;
                if(!isset($_SESSION['intento_administrador'])) $_SESSION['intento_administrador']=0;
                $_SESSION['intento_administrador']+=5;
            }
        }

        functions::url_redirect($this->url);


        
        $token = sha1(uniqid(microtime(), true));
        $_SESSION['login_token'] = array('token' => $token, 'time' => time());
        $head = new head($this->metadata);
        $head->normal();
        view::set('logo', '');
        view::set('error_login', $error_login);
        view::set('token', $token);
        view::set('url_recuperar', functions::generar_url(array("recuperar")));
        $logo=logo_model::getById(2);
        view::set('logo', image::generar_url($logo['foto'][0], 'login'));
        view::render('login');
        
        $footer = new footer();
        $footer->normal();
    }
}
