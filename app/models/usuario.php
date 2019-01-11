<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\email;
use \core\functions;
use \core\view;

class usuario extends base_model
{
    public static $idname = 'idusuario',
    $table                = 'usuario';
    public static function insert(array $data, bool $log = true)
    {
        if (isset($data['pass']) && $data['pass'] != '') {
            if (isset($data['pass_repetir']) && $data['pass_repetir'] != '') {
                if ($data['pass'] != $data['pass_repetir']) {
                    return array('exito' => false, 'mensaje' => 'Contraseñas no coinciden');
                }
            } else {
                return array('exito' => false, 'mensaje' => 'Contraseña no existe');
            }
        } else {
            return array('exito' => false, 'mensaje' => 'Contraseña no existe');
        }
        $fields          = table::getByname(static::$table);
        $insert          = database::create_data($fields, $data);
        $insert['pass']  = database::encript($insert['pass']);
        $insert['email'] = strtolower($insert['email']);
        $connection      = database::instance();
        $row             = $connection->insert(static::$table, static::$idname, $insert);
        if (is_int($row) && $row>0) {
            $last_id = $row;
            if ($log) {
                log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            }
            return $last_id;
        } else {
            return $row;
        }
    }

    public static function update(array $data, bool $log = true)
    {
        if (!isset($data['id']) || $data['id'] == '' || $data['id'] == 0) {
            echo 'Error, ID perdida';
            return false;
        }
        $set = $data;
        if (isset($set['pass']) && $set['pass'] != '') {
            if (isset($set['pass_repetir']) && $set['pass_repetir'] != '') {
                if ($set['pass'] != $set['pass_repetir']) {
                    return array('exito' => false, 'mensaje' => 'Contraseñas no coinciden');
                } else {
                    $set['pass']   = database::encript($set['pass']);
                    $set['cookie'] = '';
                    unset($set['pass_repetir']);
                }
            } else {
                return array('exito' => false, 'mensaje' => 'Contraseña no existe');
            }
        } else {
            unset($set['pass']);
            unset($set['pass_repetir']);
        }

        if (isset($set['email'])) {
            $set['email'] = strtolower($set['email']);
        }

        $where = array(static::$idname => $data['id']);
        unset($set['id']);
        $connection = database::instance();
        $row        = $connection->update(static::$table, static::$idname, $set, $where);
        log::insert_log(static::$table, static::$idname, __FUNCTION__, array_merge($set,$where));

        if (is_bool($row) && $row) {
            $row = $where[static::$idname];
        }
        return $row;
    }

    public static function login_cookie(string $cookie)
    {
        $prefix_site = app::$prefix_site;
        $where       = array('cookie' => $cookie);
        $condiciones = array('limit' => 1);
        $row         = static::getAll($where, $condiciones);

        if (count($row) != 1) {
            return false;
        } else {
            $usuario = $row[0];
            if (!$usuario['estado']) {
                return false;
            } else {
                $profile = profile::getByTipo($usuario['tipo']);
                if (!isset($profile['tipo']) || $profile['tipo'] <= 0) {
                    return false;
                } else {
                    $_SESSION[static::$idname . $prefix_site] = $usuario[0];
                    $_SESSION["emailusuario" . $prefix_site]  = $usuario['email'];
                    $_SESSION["nombreusuario" . $prefix_site] = $usuario['nombre'];
                    $_SESSION["estadousuario" . $prefix_site] = $usuario['estado'];
                    $_SESSION["tipousuario" . $prefix_site]   = $usuario['tipo'];
                    log::insert_log(static::$table, static::$idname, __FUNCTION__, $usuario);
                    return true;
                }
            }
        }
        return false;
    }

    public static function login(string $email, string $pass, bool $recordar)
    {
        $prefix_site = app::$prefix_site;
        if ($email == '' || $pass == '') {
            return false;
        }

        $where = array(
            'email' => strtolower($email),
            'pass'  => database::encript($pass),
        );
        $condiciones = array('limit' => 1);
        $row         = static::getAll($where, $condiciones);

        if (count($row) != 1) {
            return false;
        } else {
            $usuario = $row[0];
            if (!$usuario['estado']) {
                return false;
            } else {
                $profile = profile::getByTipo($usuario['tipo']);
                if (!isset($profile['tipo']) || $profile['tipo'] <= 0) {
                    return false;
                } else {
                    $_SESSION[static::$idname . $prefix_site] = $usuario[0];
                    $_SESSION["emailusuario" . $prefix_site]  = $usuario['email'];
                    $_SESSION["nombreusuario" . $prefix_site] = $usuario['nombre'];
                    $_SESSION["estadousuario" . $prefix_site] = $usuario['estado'];
                    $_SESSION["tipousuario" . $prefix_site]   = $usuario['tipo'];
                    log::insert_log(static::$table, static::$idname, __FUNCTION__, $usuario);
                    if ($recordar == 'on') {
                        return static::update_cookie($usuario[0]);
                    } else {
                        return true;
                    }
                }
            }
        }
    }

    public static function registro(string $nombre, string $telefono, string $email, string $pass, string $pass_repetir)
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        if ($nombre == "" || $email == "" || $pass == "" || $pass_repetir == "") {
            $respuesta['mensaje'] = "Todos los datos son obligatorios";
            return $respuesta;
        }

        $where = array(
            'email' => strtolower($email),
        );
        $condiciones = array('limit' => 1);
        $row         = static::getAll($where, $condiciones);

        if (count($row) > 0) {
            $respuesta['mensaje'] = "Este email ya existe. Puede recuperar la contraseña en el boton correspondiente";
        } else {
            $data = array('nombre' => $nombre, 'telefono' => $telefono, 'email' => $email, 'pass' => $pass, 'pass_repetir' => $pass_repetir, 'tipo' => 1, 'estado' => true);
            $id   = self::insert($data);
            if (!is_array($id)) {
                $respuesta['exito'] = true;
            } else {
                $respuesta = $id;
            }
        }
        return $respuesta;
    }

    public static function actualizar(array $datos)
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        if ($datos['nombre'] == "" || $datos['telefono'] == "" || $datos['email'] == "") {
            $respuesta['mensaje'] = "Todos los datos son obligatorios";
            return $respuesta;
        }
        $usuario     = static::getById($_SESSION[static::$idname . app::$prefix_site]);

        if ($usuario['email'] != $datos['email']) {
            $where = array(
                'email' => strtolower($datos['email']),
            );
            $condiciones = array('limit' => 1);
            $row         = static::getAll($where, $condiciones);
            if (count($row) > 0) {
                $respuesta['mensaje'] = "Este email ya existe. No puedes modificar tu email.";
                return $respuesta;
            } else {
                $respuesta['redirect'] = true;
            }
        }
        $datos['id'] = $usuario[0];
        $id          = self::update($datos);
        if (isset($id['exito'])) {
            $respuesta = $id;
        } else {
            $respuesta['exito'] = true;
        }
        return $respuesta;
    }

    private static function update_cookie(int $id)
    {
        $cookie      = uniqid(app::$prefix_site);
        $data        = array('id' => $id, 'cookie' => $cookie);
        $exito       = static::update($data);
        if ($exito) {
            functions::set_cookie('cookieusuario' . app::$prefix_site, $cookie, time() + (31536000));
        }
        return $exito;
    }

    public static function logout()
    {
        $prefix_site = app::$prefix_site;
        unset($_SESSION[static::$idname . $prefix_site]);
        unset($_SESSION["emailusuario" . $prefix_site]);
        unset($_SESSION["nombreusuario" . $prefix_site]);
        unset($_SESSION["estadousuario" . $prefix_site]);
        unset($_SESSION["tipousuario" . $prefix_site]);
        unset($_SESSION["cookie_pedido" . $prefix_site]);
        functions::set_cookie('cookieusuario' . $prefix_site, 'aaa', time() + (31536000));
    }

    
    public static function verificar_sesion()
    {
        $prefix_site = app::$prefix_site;
        if (isset($_SESSION[static::$idname . $prefix_site]) && $_SESSION[static::$idname . $prefix_site] != '') {
            $usuario = static::getById($_SESSION[static::$idname . $prefix_site]);
            if (isset($usuario[0]) && $usuario[0] != $_SESSION[static::$idname . $prefix_site]) {
                return false;
            } elseif ($usuario['email'] != $_SESSION["emailusuario" . $prefix_site]) {
                return false;
            } elseif ($usuario['estado'] != $_SESSION["estadousuario" . $prefix_site] || !$_SESSION["estadousuario" . $prefix_site]) {
                return false;
            } elseif ($usuario['tipo'] != $_SESSION["tipousuario" . $prefix_site] || !$_SESSION["tipousuario" . $prefix_site]) {
                return false;
            } else {
                return true;
            }
        }

        if (isset($_COOKIE['cookieusuario' . $prefix_site]) && $_COOKIE['cookieusuario' . $prefix_site] != '' && $_COOKIE['cookieusuario' . $prefix_site] != 'aaa') {
            return self::login_cookie($_COOKIE['cookieusuario' . $prefix_site]);
        }

        return false;
    }

    public static function recuperar(string $email)
    {
        $respuesta    = array('exito' => false, 'mensaje' => '');
        $nombre_sitio = app::$_title;
        if ($email == '') {
            $respuesta['mensaje'] = 'Debes llenar tu email';
            return $respuesta;
        }

        $where       = array('email' => strtolower($email));
        $condiciones = array('limit' => 1);
        $row         = static::getAll($where, $condiciones);

        if (count($row) < 1) {
            $respuesta['mensaje'] = 'Este email no existe, puedes registrarte en el link correspondiente';
            return $respuesta;
        } else {
            $usuario = $row[0];
            if (!$usuario['estado']) {
                $respuesta['mensaje'] = 'Tu usuario existe pero ha sido desactivado. Por favor envia un mensaje en el formulario de contacto.';
                return $respuesta;
            } else {
                $pass = functions::generar_pass();
                $data = array('id' => $usuario[0], 'pass' => $pass, 'pass_repetir' => $pass);
                $row  = static::update($data);

                if ($row) {
                    $body_email = array(
                        'body'          => view::get_theme() . 'mail/recuperar_password.html',
                        'titulo'        => "Recuperación de contraseña",
                        'cabecera'      => "Estimado " . $usuario["nombre"] . ", se ha solicitado la recuperación de contraseña en " . $nombre_sitio,
                        'campos'        => array('Contraseña (sin espacios)' => $pass),
                        'campos_largos' => array(),
                    );
                    $body      = email::body_email($body_email);
                    $respuesta = email::enviar_email(array($email), 'Recuperación de contraseña', $body);

                    log::insert_log(static::$table, static::$idname, __FUNCTION__, $usuario);
                    return $respuesta;
                } else {
                    $respuesta['mensaje'] = 'Ha ocurrido un error al recuperar tu contraseña, por favor intentalo nuevamente en unos minutos.';
                    return $respuesta;
                }

            }
        }
    }
}
