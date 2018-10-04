<?php
namespace app\controllers\front;
defined("APPPATH") OR die("Acceso denegado");
use core\functions,
\core\view,
\app\models\user;

class home
{
    private $url=array('home');
    public function index(){
        functions::url_redirect($this->url);
        echo "hola";
        return 'aaaa';
    }
    public function saludo($nombre)
    {
        $this->url[]='saludo';
        $this->url[]=$nombre;
        functions::url_redirect($this->url);

        $var=array(
            'name'=>$nombre,
            'title'=>'Custom MVC'
        );
        view::set_array($var);
        view::render("home");
    }
    public function users($params=array()){
        $this->url[]='users';
        $this->url=array_merge($this->url,$params);
        functions::url_redirect($this->url);
        $users = User::getAll();
        View::set("users", $users);
        View::set("title", 'usuarios');
        view::render("users");
    }
}