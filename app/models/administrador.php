<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\email;
use \core\functions;
use \core\view;

class administrador extends base_model
{
    public static $idname = 'idadministrador',
    $table                = 'administrador';
    public $cookie;

    public static function getAll($where = array(), $condiciones = array(), $select = "")
    {
        $connection = database::instance();
        if (!isset($where['estado']) && app::$_front) {
            $where['estado'] = true;
        }

        /*if (!isset($condiciones['order'])) {
        $condiciones['order'] = 'orden ASC';
        }*/

        if($select=='total'){
            $return_total=true;
        }
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
            foreach ($row as $key => $value) {
                if(isset($row[$key]['foto'])){
                    $row[$key]['foto'] = functions::decode_json($row[$key]['foto']);
                }
            }
        
        if(isset($return_total)){
            return count($row);
        }
        return $row;
    }

    public static function insert($data,$log=true)
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
        if ($row) {
            $last_id = $connection->get_last_insert_id();
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            return $last_id;
        } else {
            return $row;
        }
    }

    public static function update($data, $log = true)
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
        log::insert_log(static::$table, static::$idname, __FUNCTION__, $where);
        return $row;
    }

    public static function login_cookie($cookie)
    {
        $prefix_site = functions::url_amigable(app::$_title);
        $where       = array('cookie' => $cookie);
        $condiciones = array('limit' => 1);
        $row         = static::getAll($where, $condiciones);

        if (count($row) != 1) {
            return false;
        } else {
            $admin = $row[0];
            if (!$admin['estado']) {
                return false;
            } else {
                $profile = profile::getByTipo($admin['tipo']);
                if (!isset($profile['tipo']) || $profile['tipo'] <= 0) {
                    return false;
                } else {
                    $_SESSION[static::$idname . $prefix_site] = $admin[0];
                    $_SESSION["email" . $prefix_site]         = $admin['email'];
                    $_SESSION["nombre" . $prefix_site]        = $admin['nombre'];
                    $_SESSION["estado" . $prefix_site]        = $admin['estado'];
                    $_SESSION["tipo" . $prefix_site]          = $admin['tipo'];
                    $_SESSION['prefix_site']                  = $prefix_site;
                    log::insert_log(static::$table, static::$idname, __FUNCTION__, $admin);
                    return true;
                }
            }
        }
        return false;
    }

    public static function login($email, $pass, $recordar)
    {
        $connection  = database::instance();
        $prefix_site = functions::url_amigable(app::$_title);
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
            $admin = $row[0];
            if (!$admin['estado']) {
                return false;
            } else {
                $profile = profile::getByTipo($admin['tipo']);
                if (!isset($profile['tipo']) || $profile['tipo'] <= 0) {
                    return false;
                } else {
                    $_SESSION[static::$idname . $prefix_site] = $admin[0];
                    $_SESSION["email" . $prefix_site]         = $admin['email'];
                    $_SESSION["nombre" . $prefix_site]        = $admin['nombre'];
                    $_SESSION["estado" . $prefix_site]        = $admin['estado'];
                    $_SESSION["tipo" . $prefix_site]          = $admin['tipo'];
                    $_SESSION['prefix_site']                  = $prefix_site;
                    log::insert_log(static::$table, static::$idname, __FUNCTION__, $admin);
                    if ($recordar == 'on') {
                        return static::update_cookie($admin[0]);
                    } else {
                        return true;
                    }
                }
            }
        }
    }

    private static function update_cookie($id)
    {
        $prefix_site = functions::url_amigable(app::$_title);
        $cookie      = uniqid($prefix_site);
        $data        = array('id' => $id, 'cookie' => $cookie);
        $exito       = static::update($data);
        if ($exito) {
            functions::set_cookie('cookieadmin' . $prefix_site, $cookie, time() + (31536000));
        }
        return $exito;
    }

    public static function logout()
    {
        $prefix_site = functions::url_amigable(app::$_title);
        unset($_SESSION[static::$idname . $prefix_site]);
        unset($_SESSION["email" . $prefix_site]);
        unset($_SESSION["nombre" . $prefix_site]);
        unset($_SESSION["estado" . $prefix_site]);
        unset($_SESSION["tipo" . $prefix_site]);
        unset($_SESSION['prefix_site']);
        functions::set_cookie('cookieadmin' . $prefix_site, 'aaa', time() + (31536000));
    }

    public static function verificar_sesion()
    {
        $prefix_site = functions::url_amigable(app::$_title);
        if (!isset($_SESSION[static::$idname . $prefix_site]) || $_SESSION[static::$idname . $prefix_site] == '') {
            return false;
        }

        $admin = static::getById($_SESSION[static::$idname . $prefix_site]);

        if (!isset($admin[0])) {
            return false;
        } elseif ($admin[0] != $_SESSION[static::$idname . $prefix_site]) {
            return false;
        } elseif ($admin['email'] != $_SESSION["email" . $prefix_site]) {
            return false;
        } elseif ($admin['estado'] != $_SESSION["estado" . $prefix_site] || !$_SESSION["estado" . $prefix_site]) {
            return false;
        } elseif ($admin['tipo'] != $_SESSION["tipo" . $prefix_site] || !$_SESSION["tipo" . $prefix_site]) {
            return false;
        } else {
            $profile = profile::getByTipo($admin['tipo']);
            if (!isset($profile['tipo']) || $profile['tipo'] <= 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    public static function recuperar($email)
    {
        $nombre_sitio = app::$_title;
        if ($email == '') {
            return false;
        }

        $where       = array('email' => strtolower($email));
        $condiciones = array('limit' => 1);
        $row         = static::getAll($where, $condiciones);

        if (count($row) != 1) {
            return false;
        } else {
            $admin = $row[0];
            if (!$admin['estado']) {
                return false;
            } else {
                $pass = functions::generar_pass();
                $data = array('id' => $admin[0], 'pass' => $pass, 'pass_repetir' => $pass);
                $row  = static::update($data);

                if ($row) {
                    $body_email = array(
                        'body'          => view::get_theme() . 'mail/recuperar_password.html',
                        'titulo'        => "Recuperación de contraseña",
                        'cabecera'      => "Estimado usuario, se ha solicitado la recuperación de contraseña en " . $nombre_sitio,
                        'campos'        => array('Contraseña' => $pass),
                        'campos_largos' => array(),
                    );
                    $body      = email::body_email($body_email);
                    $respuesta = email::enviar_email(array($email), 'Recuperación de contraseña', $body);

                    log::insert_log(static::$table, static::$idname, __FUNCTION__, $admin);
                    return $respuesta;
                } else {
                    return false;
                }

            }
        }
    }
}
