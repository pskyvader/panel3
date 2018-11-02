<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use core\functions;
use \app\models\administrador as administrador_model;
use \core\view;
use \core\image;
use \app\models\logo as logo_model;

class recuperar
{
    private $url = array('recuperar');
    private $metadata = array('title' => 'Recuperar Contraseña','modulo'=>'recuperar');
    public function index($url=array())
    {
        if(isset($_SESSION['bloqueo']) && $_SESSION['bloqueo']>time()){
            exit("IP Bloqueada por intentos fallidos. Intente más tarde. tiempo: ".(intval(time())-intval($_SESSION['bloqueo']))." segundos");
        }

        if(isset($_SESSION['intento']) && $_SESSION['intento']%5==0){
            $_SESSION['bloqueo']=time()+60*(intval($_SESSION['intento'])/5);
            if($_SESSION['intento']>=15) bloquear_ip(getRealIP());
        }

        $error_login=false;
        $exito=false;
        if(isset($_POST['email']) && isset($_POST['token'])){
            if($_SESSION['recuperar_token']['token']==$_POST['token']){
                if(time()-$_SESSION['recuperar_token']['time']<=120){
                    $recuperar=administrador_model::recuperar($_POST['email']);
                    if($recuperar["exito"]){
                        if(isset($_SESSION['intento'])) $_SESSION['intento']=0;
                        $exito=true;
                    }else{
                        $error_login=true;
                        if(!isset($_SESSION['intento'])) $_SESSION['intento']=0;
                        $_SESSION['intento']++;
                    }
                }else{
                    $error_login=true;
                }
            }else{
                $error_login=true;
                if(!isset($_SESSION['intento'])) $_SESSION['intento']=0;
                $_SESSION['intento']+=5;
            }
        }


        functions::url_redirect($this->url);


        
        $token = sha1(uniqid(microtime(), true));
        $_SESSION['recuperar_token'] = array('token' => $token, 'time' => time());
        $head = new head($this->metadata);
        $head->normal();
        view::set('logo', '');
        view::set('error_login', $error_login);
        view::set('token', $token);
        view::set('exito', $exito);
        view::set('url_login', functions::generar_url(array("login","index")));
        $logo=logo_model::getById(2);
        view::set('logo', image::generar_url($logo['foto'][0], 'login'));
        view::render('recuperar');
        $footer = new footer();
        $footer->normal();
    }
}
