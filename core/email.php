<?php namespace core;

defined("APPPATH") or die("Access denied");
use \app\models\logo;
use \core\app;
use \core\image;
use \core\view;
use \PHPMailer;

class email
{
    /*body_email()
    $body_email (array), contiene:

    body(string): archivo template del email
    titulo(string): titulo del email (contacto, cotizacion, etc)
    cabecera(string): subtitulo (hemos recibido su email...)
    campos(array): nombre,email,direccion, etc (input).
    campos largos(array): mensaje (textarea).
     */
    public static function body_email($body_email)
    {
        $config           = app::getConfig();
        $dominio          = $config['domain'];
        $email_empresa    = $config['main_email'];
        $from             = $config['email_from'];
        $nombre_sitio     = $config['title'];
        $color_primario   = $config['color_primario'];
        $color_secundario = $config['color_secundario'];
        $logo             = 'cid:logo';

        $body = file_get_contents($body_email['body']);

        $body = str_replace('{logo}', $logo, $body);
        $body = str_replace('{email_empresa}', $email_empresa, $body);
        $body = str_replace('{dominio}', $dominio, $body);
        $body = str_replace('{color_primario}', $color_primario, $body);
        $body = str_replace('{color_secundario}', $color_secundario, $body);
        $body = str_replace('{nombre_sitio}', $nombre_sitio, $body);
        $body = str_replace('{titulo}', $body_email['titulo'], $body);
        $body = str_replace('{cabecera}', $body_email['cabecera'], $body);

        $c1 = "";
        if (isset($body_email['campos']) && count($body_email['campos']) > 0) {
            $campos = $body_email['campos'];
            $c      = file_get_contents(view::get_theme() . 'mail/campos.html');
            $c      = str_replace('{color_primario}', $color_primario, $c);
            $c      = str_replace('{color_secundario}', $color_secundario, $c);
            foreach ($campos as $key => $value) {
                $c1 .= str_replace('{value}', $value, str_replace('{key}', $key, $c));
            }
        }
        $body = str_replace('{campos}', $c1, $body);

        $c1 = "";
        if (isset($body_email['campos_largos']) && count($body_email['campos_largos']) > 0) {
            $campos_largos = $body_email['campos_largos'];
            $c             = file_get_contents(view::get_theme() . 'mail/campos_largos.html');
            $c             = str_replace('{color_primario}', $color_primario, $c);
            $c             = str_replace('{color_secundario}', $color_secundario, $c);
            foreach ($campos_largos as $key => $value) {
                $c1 .= str_replace('{value}', $value, str_replace('{key}', $key, $c));
            }
        }
        $body = str_replace('{campos_largos}', $c1, $body);
        return $body;
    }

    //array email, arrray adjuntos,array imagenes[url,tag]
    public static function enviar_email($email, $asunto, $body, $adjuntos = array(), $imagenes = array())
    {
        $config       = app::getConfig();
        $from         = $config['email_from'];
        $nombre_sitio = $config['title'];
        require_once PROJECTPATH . '/phpmailer/PHPMailerAutoload.php';
        $mail         = new PHPMailer;
        $asunto       = utf8_decode($asunto);
        $body         = utf8_decode($body);
        $nombre_sitio = utf8_decode($nombre_sitio);

        if ($config['email_smtp']) {
            $mail->isSMTP();
            $mail->SMTPDebug   = $config['email_debug'];
            $mail->Debugoutput = 'html';
            $mail->Host        = $config['email_host'];
            $mail->Port        = $config['email_port'];
            $mail->SMTPAuth    = $config['email_smtp'];
            $mail->Username    = $config['email_user'];
            $mail->Password    = $config['email_pass'];
        }

        $mail->setFrom($from, $nombre_sitio . ', ' . $asunto);
        foreach ($email as $key => $e) {
            if ($key == 0) {
                $mail->addAddress($e);
            } else {
                $mail->addBCC($e);
            }
        }

        $mail->Subject = $asunto;
        $mail->msgHTML($body);
        if (count($adjuntos) > 0) {
            foreach ($adjuntos as $adjunto) {
                $mail->addAttachment($adjunto['archivo'], $adjunto['nombre']);
            }
        }
        if (count($imagenes) > 0) {
            foreach ($imagenes as $imagen) {
                $mail->AddEmbeddedImage($imagen['url'], $imagen['tag']);
            }
        }

        $logo = logo::getById(8);
        $mail->AddEmbeddedImage(image::generar_dir($logo['foto'][0], 'email'), 'logo');

        $respuesta = array('exito' => false, 'mensaje' => '');
        if (!$mail->send()) {
            $respuesta['mensaje'] = "Mailer Error: " . $mail->ErrorInfo;
        } else {
            $respuesta['exito'] = true;
        }
        return $respuesta;
    }

}
