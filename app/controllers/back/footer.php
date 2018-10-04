<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use core\app;
use \core\view;

class footer
{
    public function normal()
    {
        if (!isset($_POST['ajax'])) {
            view::render('footer');
            view::js();
        }
    }

}
