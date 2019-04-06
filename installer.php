<?php
session_start();
$title       = "Instalacion";
$name        = "installer.php";
$folder      = dirname(__FILE__);
$version_min = "7.0.0";
$version_max = "7.4.0";
$paso        = (isset($_GET['paso'])) ? (int) $_GET['paso'] : 1;
$respuesta   = array('exito' => true, 'mensaje' => array());

$debug = true;
if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

switch ($paso) {
    case 1:
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
        } else {
            $modules = array();
        }
        $required_modules = array('mod_expires', 'mod_headers', 'mod_mime', 'mod_rewrite');
        $extensions       = get_loaded_extensions();
        //$required_extensions = array('date', 'ftp', 'json', 'mcrypt', 'session', 'zip', 'zlib', 'libxml', 'dom', 'PDO', 'openssl', 'SimpleXML', 'xml', 'xmlreader', 'xmlwriter', 'curl', 'gd', 'intl', 'mysqli', 'pdo_mysql', 'sockets', 'xmlrpc', 'mhash');
        $required_extensions = array('date', 'json', 'session', 'zip', 'zlib', 'libxml', 'dom', 'PDO', 'SimpleXML', 'xml', 'xmlreader', 'xmlwriter', 'curl', 'gd', 'intl', 'pdo_mysql','soap');

        if (basename(__FILE__) != $name) {
            $respuesta['mensaje'][] = 'El nombre de este archivo debe ser ' . $name;
        }
        if (!is_writable($folder)) {
            $respuesta['mensaje'][] = 'Este directorio debe tener permisos de escritura';
        }

        if (!version_compare(PHP_VERSION, $version_min, '>=')) {
            $respuesta['mensaje'][] = 'La version minima de php debe ser ' . $version_min;
        }

        if (!version_compare(PHP_VERSION, $version_max, '<=')) {
            $respuesta['mensaje'][] = 'La version maxima de php debe ser ' . $version_max;
        }
        if (function_exists('apache_get_modules')) {
            foreach ($required_modules as $key => $r) {
                if (!in_array($r, $modules)) {
                    $respuesta['mensaje'][] = 'Debe activar el modulo ' . $r;
                }
            }
        } else {
            if ($debug) {
                foreach ($required_modules as $key => $r) {
                    $respuesta['mensaje'][] = 'Debe Comprobar manualmente la existencia del modulo ' . $r;
                }
            }
        }

        foreach ($required_extensions as $key => $r) {
            if (!in_array($r, $extensions)) {
                $respuesta['mensaje'][] = 'Debe activar la extension ' . $r;
            }
        }

        if (count($respuesta['mensaje']) > 0) {
            $respuesta['exito'] = false;
        } else {
            header("Location: " . $name . '?paso=2');
            exit();
        }
        break;
    case 2:
        $configuracion = array(
            array('titulo' => 'Base de datos', 'subtitulo' => '* Debes crear una base de datos y llenar la informacion en los siguientes campos vacíos.<br/>(Opcionalmente puedes modificar cualquier campo):'),
            "host"                  => array('name' => "host", 'value' => 'localhost', 'required' => true, 'visible' => true, 'title' => 'Host', 'fill' => false),
            "database"              => array('name' => "database", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Nombre de la base de datos', 'fill' => false),
            "user"                  => array('name' => "user", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Usuario de la base de datos', 'fill' => false),
            "password"              => array('name' => "password", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Contraseña de la base de datos', 'fill' => false),
            "prefix"                => array('name' => "prefix", 'value' => 'seo', 'required' => true, 'visible' => false, 'title' => 'Prefijo de las tablas de la base de datos'),
            array('boton' => 'Probar conexion'),
            array('titulo' => 'Sitio'),
            "www"                   => array('name' => "www", 'value' => '0', 'required' => true, 'visible' => true, 'title' => 'Dominio con WWW', 'type' => 'active'),
            "https"                 => array('name' => "https", 'value' => '0', 'required' => true, 'visible' => true, 'title' => 'Sitio seguro (debe instalar certificado SSL)', 'type' => 'active'),
            "cache"                 => array('name' => "cache", 'value' => '1', 'required' => true, 'visible' => true, 'title' => 'Activar cache de paginas (mejora velocidad del sitio, desactivar al hacer pruebas)', 'type' => 'active'),
            "theme"                 => array('name' => "theme", 'value' => '', 'required' => true, 'visible' => false),
            "dir"                   => array('name' => "dir", 'value' => strtolower(trim(substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], $name)), '/')), 'required' => false, 'visible' => true, 'title' => 'Sub directorio (si existe)', 'fill' => false),
            "title"                 => array('name' => "title", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Titulo del sitio'),
            "short_title"           => array('name' => "short_title", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Titulo corto del sitio (maximo 12 caracteres)'),
            "debug"                 => array('name' => "debug", 'value' => '0', 'required' => true, 'visible' => false, 'fill' => false),
            "domain"                => array('name' => "domain", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Nombre del dominio'),
            array('titulo' => 'Utilidades'),
            "color_primario"        => array('name' => "color_primario", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Color principal del sitio', 'type' => 'color'),
            "color_secundario"      => array('name' => "color_secundario", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Color secundario del sitio', 'type' => 'color'),
            "google_captcha"        => array('name' => "google_captcha", 'value' => '', 'required' => false, 'visible' => true, 'title' => 'Codigo de captcha google'),
            "google_captcha_secret" => array('name' => "google_captcha_secret", 'value' => '', 'required' => false, 'visible' => true, 'title' => 'Codigo secreto de captcha google'),
            "googlemaps_key"        => array('name' => "googlemaps_key", 'value' => '', 'required' => false, 'visible' => true, 'title' => 'Codigo de google maps'),
            "instagram_token"       => array('name' => "instagram_token", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'token de instagram'),
            "admin"                 => array('name' => "admin", 'value' => 'admin', 'required' => true, 'visible' => true, 'title' => 'Nombre del panel de administracion'),
            "theme_back"            => array('name' => "theme_back", 'value' => 'paper', 'required' => true, 'visible' => false),
            array('titulo' => 'Email'),
            "email_debug"           => array('name' => "email_debug", 'value' => '0', 'required' => true, 'visible' => false, 'fill' => false),
            "email_smtp"            => array('name' => "email_smtp", 'value' => '0', 'required' => false, 'visible' => true, 'title' => 'Es SMTP?', 'type' => 'active'),
            "email_host"            => array('name' => "email_host", 'value' => '', 'required' => false, 'visible' => true, 'title' => 'Host de email (Solo smtp)'),
            "email_port"            => array('name' => "email_port", 'value' => '', 'required' => false, 'visible' => true, 'title' => 'Puerto de email (Solo smtp)'),
            "email_user"            => array('name' => "email_user", 'value' => '', 'required' => false, 'visible' => true, 'title' => 'Usuario de email (Solo smtp)'),
            "email_pass"            => array('name' => "email_pass", 'value' => '', 'required' => false, 'visible' => true, 'title' => 'Contraseña de email (Solo smtp)'),
            "main_email"            => array('name' => "main_email", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Email para formulario de contacto'),
            "email_from"            => array('name' => "email_from", 'value' => '', 'required' => true, 'visible' => true, 'title' => 'Email de envio (generalmente "noreply@...")'),
        );

        $zip                    = new \ZipArchive();
        $respuesta['exito']     = false;
        $respuesta['mensaje'][] = 'No se encontro un archivo zip valido, o Debe dar permisos 777 a todos los archivos.';
        foreach (scandir($folder) as $key => $files) {
            if (strpos($files, '.zip') !== false) {
                $file = $files;
                break;
            }
        }
        if (isset($file)) {
            if ($zip->open($file) === true) {
                $config = $zip->getFromName('app\\config\\config.json');
                if (is_bool($config)) {
                    $config = $zip->getFromName('app/config/config.json');
                }

                if (!is_bool($config)) {
                    $config = json_decode($config, true);
                    if (is_array($config)) {
                        foreach ($config as $key => $c) {
                            if (isset($configuracion[$key]) && (!isset($configuracion[$key]['fill']) || $configuracion[$key]['fill'])) {
                                if ('' != $c) {
                                    $configuracion[$key]['value'] = $c;
                                }
                            }
                        }
                        $respuesta['exito'] = true;
                    }
                }
            }
        }
        break;
    case 3:
        try {
            $connection = new \PDO('mysql:host=' . $_POST['host'] . '; dbname=' . $_POST['database'], $_POST['user'], $_POST['password']);
            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $connection->exec("SET CHARACTER SET utf8");
        } catch (\PDOException $e) {
            $respuesta['mensaje'] = $e->getMessage();
            $respuesta['exito']   = false;
        }
        echo json_encode($respuesta);
        exit();
        break;
    case 4:
        $tiempo    = time();
        $inicio    = (isset($_POST['inicio'])) ? ((int) $_POST['inicio'] - 1) : 0;
        $respuesta = array('exito' => false, 'mensaje' => '', 'errores' => array());
        foreach (scandir($folder) as $key => $files) {
            if (strpos($files, '.zip') !== false) {
                $file = $files;
                break;
            }
        }
        if (isset($file)) {
            $archivo_log = $folder . '/progress-installer.json';
            $file        = $folder . '/' . $file;
            $zip         = new \ZipArchive();
            if ($zip->open($file) === true) {
                $total = $zip->numFiles;
                for ($i = $inicio; $i < $total; $i++) {
                    $nombre = $zip->getNameIndex($i);
                    if ($nombre != $name) {
                        //$exito  = true;
                        $exito        = $zip->extractTo($folder, array($nombre));
                        $nombre_final = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $folder . "/" . $nombre);
                        rename($folder . "/" . $nombre, $nombre_final);
                        if (!$exito) {
                            $respuesta['errores'][] = $nombre;
                        }
                        if ($i % 100 == 0) {
                            $n = substr(strip_tags($nombre), 0, 30);
                            if (strlen(strip_tags($nombre)) > 30) {
                                $n .= "...";
                            }
                            $log = array('mensaje' => 'Instalando ' . $n . ' (' . ($i + 1) . '/' . $total . ')', 'porcentaje' => ((($i + 1) / $total) * 90));
                            file_put_contents($archivo_log, json_encode($log));
                        }
                        if (time() - $tiempo > 20) {
                            $respuesta['inicio'] = $i;
                            break;
                        }
                    }
                }
                $zip->close();
                $respuesta['exito'] = true;
                if (!isset($respuesta['inicio'])) {
                    if (file_exists($folder . '/bdd.sql')) {
                        $log = array('mensaje' => 'Restaurando Base de datos', 'porcentaje' => 95);
                        file_put_contents($archivo_log, json_encode($log));
                        try {
                            $connection = new \PDO('mysql:host=' . $_POST['host'] . '; dbname=' . $_POST['database'], $_POST['user'], $_POST['password']);
                            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                            $connection->exec("SET CHARACTER SET utf8");

                            $sql   = file_get_contents($folder . '/bdd.sql');
                            $query = $connection->prepare($sql);
                            $query->execute();
                        } catch (\PDOException $e) {
                            $respuesta['mensaje'] = $e->getMessage();
                            $respuesta['exito']   = false;
                        }
                    } else {
                        $respuesta['mensaje']   = 'No existe base de datos';
                        $respuesta['errores'][] = 'bdd.sql';
                    }
                }
            } else {
                $respuesta['mensaje'] = 'Error al abrir archivo';
            }
        } else {
            $respuesta['mensaje'] = 'archivo no encontrado';
        }
        if (!isset($respuesta['inicio'])) {
            $log = array('mensaje' => 'Restauracion finalizada', 'porcentaje' => 100);
            file_put_contents($archivo_log, json_encode($log));
        }
        echo json_encode($respuesta);
        exit();
        break;
    case 5:
        $config_folder = $folder . '/app/config/';
        if (count($_POST) > 0) {
            $config = $_POST;
            foreach ($config as $key => $c) {
                $config[$key] = trim($c);
            }
            $url           = 'http://' . $_SERVER['HTTP_HOST'] . '/' . strtolower(trim(substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], $name)), '/'));
            $url_admin     = $url . "/" . $_POST['admin'] . '/';
            $url_restaurar = $url_admin . "configuracion_administrador/json_update";

            if (file_exists('admin')) {
                rename("admin", $config['admin']);
            }
            file_put_contents($config_folder . 'config.json', json_encode($config));
        } else {
            header("Location: " . $name . '?paso=1');
            exit();
        }
        break;
    case 6:
        if (count($_POST) > 0) {
            try {
                $connection = new \PDO('mysql:host=' . $_POST['host'] . '; dbname=' . $_POST['database'], $_POST['user'], $_POST['password']);
                $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $connection->exec("SET CHARACTER SET utf8");

                $nombre = trim($_POST['nombre']);
                $email  = strtolower(trim($_POST['email']));

                $password = trim($_POST['pass']);
                $part1 = hash('sha256', $password);
                $part2 = hash('sha256', $part1);
                $password= $part1 . $part2;

                $sql = "INSERT INTO " . $_POST['prefix'] . "_administrador (idadministrador,tipo,email,pass,nombre,estado,foto)";
                $sql .= " VALUES ('','2','" . $email . "','" . $password . "','" . $nombre . "',TRUE,'".json_encode(array(array()))."')";
                $query = $connection->prepare($sql);
                $query->execute();

                $url       = 'http://' . $_SERVER['HTTP_HOST'] . '/' . strtolower(trim(substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], $name)), '/'));
                $url_admin = $url . "/" . $_POST['admin'] . '/';

            } catch (\PDOException $e) {
                $respuesta['mensaje'][] = $e->getMessage();
                $respuesta['exito']     = false;
                $paso                   = 5;
            }
        } else {
            header("Location: " . $name . '?paso=1');
            exit();
        }
        break;
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        <?php echo $title; ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</head>

<body>
    <?php if (!$respuesta['exito']) {?>
    <div class="container-fluid py-3 p-sm-5">
        <div class="alert alert-danger" role="alert">
            <b>Debes corregir los siguientes errores antes de continuar:</b>
            <br />
            <?php echo implode('<br/>', $respuesta['mensaje']); ?>
            <hr>
            <p>Despues de solucionar los errores recargue la pagina</p>
        </div>
        <a href="?paso=2" class="btn btn-primary">Continuar de cualquier forma (Puede provocar errores inesperados)</a>
    </div>
    <?php exit;}?>
    <?php if (2 == $paso) {?>
    <div class="fixed-top">
        <div class="progress" id="progreso" style="opacity:0;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 1%">1%</div>
        </div>
    </div>
    <div class="container p-sm-5">
        <div class="row justify-content-md-center">
            <div class="col ">
                <h1 class="">
                    <?php echo $title; ?>
                </h1>
                <form action="?paso=5" method="POST" class="jumbotron ">
                    <div class="row">
                        <?php foreach ($configuracion as $key => $c) {
    if (!isset($c['type'])) {
        $c['type'] = 'text';
    }

    if (isset($c['visible']) && $c['visible']) {?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php if ('active' != $c['type']) {?>
                                <label for="<?php echo $c['name']; ?>">
                                    <?php echo $c['title']; ?>
                                    <b style="color:red;">
                                        <?php if ($c['required']) {echo "*";}?>
                                    </b>
                                </label>
                                <input type="<?php echo $c['type']; ?>" class="form-control" name="<?php echo $c['name']; ?>" id="<?php echo $c['name']; ?>" placeholder="<?php echo $c['title']; ?>" value="<?php echo $c['value']; ?>" <?php if ($c['required']) {echo "required";}?>
                                <?php if ('short_title' == $c['name']) {
        echo "maxlength='12'";
    }
        ?> >
                                <?php } else {?>
                                <label>
                                    <?php echo $c['title']; ?>
                                </label><br>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="<?php echo $c['name']; ?>SI" name="<?php echo $c['name']; ?>" class="custom-control-input" value="1" <?php if ('1' == $c['value']) {echo 'checked';}?>>
                                    <label class="custom-control-label" for="<?php echo $c['name']; ?>SI">SI</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="<?php echo $c['name']; ?>NO" name="<?php echo $c['name']; ?>" class="custom-control-input" value="0" <?php if ('0' == $c['value']) {echo 'checked';}?>>
                                    <label class="custom-control-label" for="<?php echo $c['name']; ?>NO">NO</label>
                                </div>
                                <?php }?>
                            </div>
                        </div>
                        <?php } elseif (isset($c['titulo'])) {?>
                        <div class="col-12 py-3">
                            <h3>
                                <?php echo $c['titulo']; ?>
                                <hr>
                            </h3>
                            <p>
                                <?php echo (isset($c['subtitulo'])) ? $c['subtitulo'] : ""; ?>
                            </p>
                        </div>
                        <?php } elseif (isset($c['boton'])) {?>
                        <div class="col-12 conexion">
                            <button id="probar_conexion" type="button" class="btn btn-info" disabled="disabled">Probar conexion</button>
                            <button id="editar" type="button" class="btn btn-secondary" style="display:none;">Volver a Editar informacion de conexion</button>
                        </div>
                        <div class="col-12 extraccion" style="display:none">
                            <div class="card">
                                <div class="card-body">
                                    <p class="card-text">Ahora puedes comenzar la instalacion. Mientras tanto, puedes seguir completando la información restante.</p>
                                    <button id="extraer" type="button" class="btn btn-success">Comenzar instalacion</button>
                                </div>
                            </div>
                        </div>
                        <?php } elseif (isset($c['visible'])) {?>
                        <input type="hidden" name="<?php echo $c['name']; ?>" value="<?php echo $c['value']; ?>" <?php if ($c['required']) {echo "required";}?>>
                        <?php }?>
                        <?php }?>
                        <div class="col-12 continuar">
                            <div class="alert alert-warning" role="alert">
                                <b> Antes de continuar Debes probar la conexion y comenzar la instalacion</b>
                            </div>
                            <button type="submit" id="submit" class="btn btn-primary" disabled="disabled">Continuar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php } elseif (5 == $paso) {?>
    <div class="container p-sm-5">
        <div class="row justify-content-md-center">
            <div class="col ">
                <h1 class="">
                    Configuracion de administrador
                </h1>
                <form action="?paso=6" method="POST" class="jumbotron">
                    <input type="hidden" name="host" value="<?php echo $_POST['host']; ?>">
                    <input type="hidden" name="database" value="<?php echo $_POST['database']; ?>">
                    <input type="hidden" name="user" value="<?php echo $_POST['user']; ?>">
                    <input type="hidden" name="password" value="<?php echo $_POST['password']; ?>">
                    <input type="hidden" name="prefix" value="<?php echo $_POST['prefix']; ?>">
                    <input type="hidden" name="admin" value="<?php echo $_POST['admin']; ?>">
                    <h3>Para finalizar, ingresa tus datos de administrador</h3>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre
                                    <b style="color:red;">* </b>
                                </label>
                                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Tu nombre" value="" required="required">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email
                                    <b style="color:red;">* </b>
                                </label>
                                <input type="email" class="form-control" name="email" id="email" placeholder="Tu email" value="" required="required">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pass">Contraseña (Al menos 6 caracteres)
                                    <b style="color:red;">* </b>
                                </label>
                                <input type="password" class="form-control" name="pass" id="pass" placeholder="Tu Contraseña" value="" required="required" minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pass_repetir">Repite Contraseña
                                    <b style="color:red;">* </b>
                                </label>
                                <input type="password" class="form-control" name="pass_repetir" id="pass_repetir" placeholder="Tu Contraseña" value="" required="required" minlength="6">
                            </div>
                        </div>
                        <div class="col-12 continuar">
                            <p> <b>* Dentro del panel de administracion podrás editar o agregar información, haciendo click en tu nombre.</b></p>
                            <div class="w-100 pb-2"></div>
                            <button type="submit" id="submit" class="btn btn-primary">Continuar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php } elseif (6 == $paso) {?>
    <div class="container p-sm-5">
        <div class="row justify-content-md-center">
            <div class="col">
                <h1 class="">
                    Instalacion completada
                </h1>
                <div class="jumbotron">
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success mt-2" role="alert">
                                <svg style="height: 20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M186.301 339.893L96 249.461l-32 30.507L186.301 402 448 140.506 416 110z" />
                                </svg>
                                Has completado la instalacion. Ahora puedes acceder a tu sitio y a tu panel de administración.
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mt-2" role="alert">
                                <p>Las url de tu sitio son:</p>
                                <ul>
                                    <li>Sitio:
                                        <a href="<?php echo $url; ?>" target="_blank">
                                            <?php echo $url; ?>
                                        </a>
                                    </li>
                                    <li>Panel de administracion:
                                        <a href=" <?php echo $url_admin; ?>" target="_blank">
                                            <?php echo $url_admin; ?>
                                        </a>
                                    </li>
                                </ul>
                                <p><b>Recuerda siempre guardar estas url</b></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-warning mt-2" role="alert">
                                <p>Se recomienda eliminar los siguientes archivos del servidor: </p>
                                <ul>
                                    <li>installer.php</li>
                                    <li>progress-installer.json</li>
                                    <li>tu archivo zip de instalacion</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
</body>
<?php if (2 == $paso) {?>
<script>
    var porcentaje = 1;
    var progreso = $('#progreso');
    var finalizado = false;
    $('form').on('submit', function() {
        $('input', $(this)).prop('disabled', false);
    });

    $('.form-control#host,.form-control#database,.form-control#user,.form-control#password').on('blur change', function() {
        if ($('.form-control#host').val() != '' &&
            $('.form-control#database').val() != '' &&
            $('.form-control#user').val() != '' &&
            $('.form-control#password').val() != '') {
            $('button#probar_conexion').prop('disabled', false);
        }
    });
    $('button#probar_conexion').on('click', function() {
        $('.conexion .alert').remove();
        var data = {
            host: $('.form-control#host').val(),
            database: $('.form-control#database').val(),
            user: $('.form-control#user').val(),
            password: $('.form-control#password').val(),
        };
        $.post("?paso=3", data, function(respuesta) {
            try {
                respuesta = JSON.parse(respuesta);
            } catch (e) {
                respuesta = {
                    mensaje: respuesta,
                    exito: false
                };
            }
            if (respuesta.exito) {
                var mensaje = '<svg style="height: 20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M186.301 339.893L96 249.461l-32 30.507L186.301 402 448 140.506 416 110z"/></svg>';
                mensaje += " Conexion exitosa";
                $('.conexion').append($('<div class="alert alert-success mt-2" role="alert">').html(mensaje));
                $('.form-control#host,.form-control#database,.form-control#user,.form-control#password,button#probar_conexion').prop('disabled', true);
                $('.extraccion,#editar').slideDown();
            } else {
                var mensaje = "Se ha encontrado el siguiente error:<br/><b>" + respuesta.mensaje + "</b><br/>Por favor corrige este error e intentalo nuevamente";
                $('.conexion').append($('<div class="alert alert-danger mt-2" role="alert">').html(mensaje));
            }
        });
        return false;
    });
    $('button#editar').on('click', function() {
        $('.conexion .alert').remove();
        $('.extraccion,#editar').slideUp();
        $('.form-control#host,.form-control#database,.form-control#user,.form-control#password,button#probar_conexion').prop('disabled', false);
    });
    $('button#extraer').on('click', function() {
        finalizado = false;
        $('.continuar .alert').remove();
        $('button#extraer,#editar').prop('disabled', true);
        progreso.css('opacity', 1);
        porcentaje = 0;
        var data = {
            host: $('.form-control#host').val(),
            database: $('.form-control#database').val(),
            user: $('.form-control#user').val(),
            password: $('.form-control#password').val()
        };
        marcar_progreso(porcentaje);
        $.post('?paso=4', data, fin_extraccion);
        setTimeout(get_progress, 1000);
    });

    function fin_extraccion(respuesta) {
        try {
            respuesta = JSON.parse(respuesta);
        } catch (e) {
            respuesta = {
                mensaje: respuesta,
                exito: false
            };
        }
        if (respuesta.exito) {
            if (respuesta.inicio) {
                var data = {
                    host: $('.form-control#host').val(),
                    database: $('.form-control#database').val(),
                    user: $('.form-control#user').val(),
                    password: $('.form-control#password').val(),
                    inicio: respuesta.inicio
                };
                $.post('?paso=4', data, fin_extraccion);
            } else {
                console.log(respuesta);
                // var mensaje = '<svg style="height: 20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M186.301 339.893L96 249.461l-32 30.507L186.301 402 448 140.506 416 110z"/></svg>';
                // mensaje += " Instalacion exitosa";
                //$('.continuar').append($('<div class="alert alert-success mt-2" role="alert">').html(mensaje));
                $('button#submit').prop('disabled', false);
                marcar_progreso(100);
            }
        } else {
            var mensaje = "Se ha encontrado el siguiente error:<br/><b>" + respuesta.mensaje + "</b><br/>Por favor corrige este error e intentalo nuevamente";
            $('.continuar').append($('<div class="alert alert-danger mt-2" role="alert">').html(mensaje));
            $('button#extraer,#editar').prop('disabled', false);
            finalizado = true;
            progreso.css('opacity', 0);
            marcar_progreso(0);
            $('html,body').animate({
                scrollTop: $('.continuar').first().offset().top
            }, 300);
        }

    }


    function get_progress() {
        if (!finalizado) {
            $.ajax({
                cache: false,
                url: 'progress-installer.json',
                success: function(data) {
                    if (typeof(data) == 'object') {
                        if (data.porcentaje) {
                            porcentaje = data.porcentaje;
                            marcar_progreso(porcentaje);
                        }
                        setTimeout(get_progress, 500);
                    } else {
                        setTimeout(get_progress, 500);
                    }
                },
                error: function() {
                    setTimeout(get_progress, 1000);
                },
                timeout: 500 //in milliseconds
            });
        }
    }

    function marcar_progreso(p) {
        var pr = $('.progress-bar', progreso);
        if (!finalizado) {
            if (p >= 100 || p < 0) {
                pr.css("width", "100%");
                pr.text("Completado");
                finalizado = true;
            } else {
                pr.css("width", p + "%");
                pr.text(parseInt(p) + "%");
            }
        }
    }
</script>
<?php }?>
<?php if (5 == $paso) {?>
<script>
    $.post("<?php echo $url_restaurar ?>", function(respuesta) {
        console.log(respuesta);
    });

    $('form').on('submit', function() {
        if ($("#pass").val() == "" || $("#pass").val() != $("#pass_repetir").val()) {
            var mensaje = "Las contraseñas no coinciden";
            $(this).append($('<div class="alert alert-danger mt-2" role="alert">').html(mensaje));
            return false;
        }
    });
</script>
<?php }?>

</html>