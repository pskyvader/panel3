<?php
namespace app\controllers\front\themes\gatografias;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\email;
use \core\functions;
use \core\view;

class enviar
{
    public function __construct()
    {
    }
    public function index()
    {
        $campos        = self::test_input($_POST['campos']);
        $respuesta     = array('exito' => true, 'mensaje' => '');
        $nombre_sitio  = app::$_title;
        $config        = app::getConfig();
        $secret        = $config['google_captcha_secret'];
        $email_empresa = $config['main_email'];

        if (empty($campos['nombre'])) {
            $respuesta['mensaje'] = '<strong>Error!</strong>&nbsp; Nombre vacío.';
        } elseif (empty($campos['email'])) {
            $respuesta['mensaje'] = '<strong>Error!</strong>&nbsp; Email vacío.';
        } elseif (!filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
            $respuesta['mensaje'] = '<strong>Error!</strong>&nbsp; Email no valido.';
        } elseif (empty($campos['mensaje'])) {
            $respuesta['mensaje'] = '<strong>Error!</strong>&nbsp; Mensaje vacío.';
        }/* elseif (!isset($campos['g-recaptcha-response']) || empty($campos['g-recaptcha-response'])) {
            $respuesta['mensaje'] = '<strong>Error!</strong>&nbsp; Error en captcha. Por favor completa el captcha.';
            $respuesta['captcha']=true;
        }*/

        if ($respuesta['mensaje'] != '') {
            $respuesta['exito'] = false;
        }

        /*if ($respuesta['exito']) {
            $url                  = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $campos['g-recaptcha-response'] . '&remoteip=' . $_SERVER['REMOTE_ADDR'];
            $file=file_get_contents($url);
            $captcha              = functions::decode_json($file);
            $respuesta['exito']   = ($captcha['success']);
            if (!$respuesta['exito']) {
                $respuesta['mensaje'] = '<strong>Error!</strong>&nbsp; Error en captcha. Por favor completa el captcha.';
            }
            $respuesta['captcha']=true;
            unset($campos['g-recaptcha-response']);
        }*/

        if ($respuesta['exito']) {
            $body_email = array(
                'body'     => view::get_theme() . 'mail/contacto.html',
                'titulo'   => "Formulario de " . $campos['titulo'],
                'cabecera' => "Estimado " . $campos['nombre'] . ", hemos recibido su correo, el cual será respondido a la brevedad por el centro de atención al cliente de " . $nombre_sitio,
            );
            $titulo                      = $campos['titulo'];
            $body_email['campos_largos'] = array('Mensaje' => nl2br($campos['mensaje']));
            unset($campos['accion'], $campos['titulo'], $campos['mensaje']);
            $body_email['campos'] = $campos;
            $imagenes             = array();

            $adjuntos = array();
            if (isset($_FILES)) {
                foreach ($_FILES as $key => $file) {
                    $adjuntos[] = array('archivo' => $file['tmp_name'], 'nombre' => $file['name']);
                }
            }
            $body      = email::body_email($body_email);
            $respuesta = email::enviar_email(array($campos['email'], $email_empresa), "Formulario de " . $titulo, $body, $adjuntos, $imagenes);
            if ($respuesta['exito']) {
                $respuesta['mensaje'] = "<strong>Gracias!</strong>&nbsp; Email enviado correctamente.";
                $respuesta['captcha']=true;
            } else {
                $respuesta['mensaje'] = "<strong>Error!</strong>&nbsp; No se puede enviar el email, por favor intente más tarde.<br/>" . $respuesta['mensaje'];
                $respuesta['captcha']=true;
            }
        }
        echo functions::encode_json($respuesta);
    }
    private static function test_input($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::test_input($value);
            }
        } else {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
        }
        return $data;
    }
}
