<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
class ping
{
    public function index($url=array())
    {
        //http_response_code(404);
        echo "true";
    }
}
