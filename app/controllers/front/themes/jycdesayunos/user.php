<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\comuna as comuna_model;
use \app\models\modulo as modulo_model;
use \app\models\mediopago as mediopago_model;
use \app\models\moduloconfiguracion as moduloconfiguracion_model;
use \app\models\pedido as pedido_model;
use \app\models\pedidodireccion as pedidodireccion_model;
use \app\models\pedidoestado as pedidoestado_model;
use \app\models\pedidoproducto as pedidoproducto_model;
use \app\models\usuario as usuario_model;
use \app\models\usuariodireccion as usuariodireccion_model;
use \app\models\seo as seo_model;
use \core\app;
use \core\image;
use \core\functions;
use \core\view;

class user extends base
{
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo'], false);
    }
    public function index()
    {
        $this->meta($this->seo);
        $verificar = self::verificar(true);
        if (!$verificar['exito']) {
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title']);
        $sidebar   = array();
        $sidebar[] = array('title' => "Mis datos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'datos')));
        $sidebar[] = array('title' => "Mis direcciones", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'direcciones')));
        $sidebar[] = array('title' => "Mis pedidos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'pedidos')));

        view::set('sidebar_user', $sidebar);
        $sidebar=view::render('user/sidebar', false, true);
        view::set('sidebar',$sidebar);
        view::render('user/detail');

        $footer = new footer();
        $footer->normal();
    }

    public function datos()
    {
        $this->meta($this->seo);
        $verificar = self::verificar(true);
        if ($verificar['exito']) {
            $this->url[] = 'datos';
        } else {
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'],'Mis datos');
        $sidebar   = array();
        $sidebar[] = array('title' => "Mis datos", 'active' => 'active', 'url' => functions::generar_url(array($this->url[0], 'datos')));
        $sidebar[] = array('title' => "Mis direcciones", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'direcciones')));
        $sidebar[] = array('title' => "Mis pedidos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'pedidos')));

        view::set('sidebar_user', $sidebar);
        $sidebar=view::render('user/sidebar', false, true);
        view::set('sidebar',$sidebar);
        
        $usuario= usuario_model::getById($_SESSION[usuario_model::$idname . app::$prefix_site]);
        view::set('nombre', $usuario['nombre']);
        view::set('telefono',$usuario['telefono']);
        view::set('email',$usuario['email']);
        $token                      = sha1(uniqid(microtime(), true));
        $_SESSION['datos_token'] = array('token' => $token, 'time' => time());
        view::set('token', $token);

        view::render('user/datos');

        $footer = new footer();
        $footer->normal();
    }
    
    /**
     * datos_process
     * procesa el POST para modificacion de datos
     *
     * @return json
     * 
     */
    public function datos_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $verificar = self::verificar(true);
        if(!$verificar['exito']){
            $respuesta['mensaje']='Debes ingresar a tu cuenta';
            return $respuesta;
        }
        $campos    = functions::test_input($_POST['campos']);

        if (isset($campos['nombre']) && isset($campos['telefono']) && isset($campos['email']) && isset($campos['token'])) {
            if (isset($_SESSION['datos_token']['token']) && $_SESSION['datos_token']['token'] == $campos['token']) {
                if (time() - $_SESSION['datos_token']['time'] <= 120) {
                    $datos=array(
                        'nombre'=>$campos['nombre'],
                        'telefono'=>$campos['telefono'],
                        'email'=>$campos['email'],
                        'pass'=>(isset($campos['pass']) && $campos['pass']!='')?$campos['pass']:'',
                        'pass_repetir'=>(isset($campos['pass_repetir']) && $campos['pass_repetir']!='')?$campos['pass_repetir']:'',
                    );
                    $respuesta = usuario_model::actualizar($datos);
                    if ($respuesta['exito']) {
                        $respuesta['mensaje'] = "Datos modificados correctamente";
                    }
                } else {
                    $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
                }
            } else {
                $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
            }
        } else {
            $respuesta['mensaje'] = 'Debes llenar los campos obligatorios';
        }

        echo json_encode($respuesta);
    }

    /**
     * lista de direcciones
     *
     * @return void
     */
    public function direcciones(){
        $this->meta($this->seo);
        $verificar = self::verificar(true);
        if ($verificar['exito']) {
            $this->url[] = 'direcciones';
        } else {
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'],'Mis Direcciones');
        $sidebar   = array();
        $sidebar[] = array('title' => "Mis datos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'datos')));
        $sidebar[] = array('title' => "Mis direcciones", 'active' => 'active', 'url' => functions::generar_url(array($this->url[0], 'direcciones')));
        $sidebar[] = array('title' => "Mis pedidos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'pedidos')));

        view::set('sidebar_user', $sidebar);
        $sidebar=view::render('user/sidebar', false, true);
        view::set('sidebar',$sidebar);
        $dir=usuariodireccion_model::getAll(array('idusuario'=>$_SESSION[usuario_model::$idname . app::$prefix_site]));
        $direcciones=array();
        foreach ($dir as $key => $d) {
            $direcciones[]=array(
                'title'=>$d['titulo'],
                'nombre'=>$d['nombre'],
                'direccion'=>$d['direccion'],
                'telefono'=>$d['telefono'],
                'url'=>functions::generar_url(array($this->url[0], 'direccion',$d[0]))
            );
        }
        view::set('direcciones',$direcciones);
        view::set('url_new',functions::generar_url(array($this->url[0], 'direccion')));
        
        view::render('user/direcciones-lista');

        $footer = new footer();
        $footer->normal();
    }


    /**
     * modificar o crear direccion
     *
     * @param  mixed $var
     *
     * @return void
     */
    public function direccion($var=array()){
        $this->meta($this->seo);
        $verificar = self::verificar(true);
        if ($verificar['exito']) {
            if(isset($var[0])){
                $direccion=usuariodireccion_model::getById($var[0]);
                if($direccion['idusuario']==$_SESSION[usuario_model::$idname . app::$prefix_site]){
                    $this->url[] = 'direccion';
                    $this->url[] = $var[0];
                }else{
                    $this->url[] = 'direcciones';
                }
            }else{
                $this->url[] = 'direccion';
            }
        } else {
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'],'Modificar dirección');
        $sidebar   = array();
        $sidebar[] = array('title' => "Mis datos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'datos')));
        $sidebar[] = array('title' => "Mis direcciones", 'active' => 'active', 'url' => functions::generar_url(array($this->url[0], 'direcciones')));
        $sidebar[] = array('title' => "Mis pedidos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'pedidos')));

        view::set('sidebar_user', $sidebar);
        $sidebar=view::render('user/sidebar', false, true);
        view::set('sidebar',$sidebar);

        $moduloconfiguracion = moduloconfiguracion_model::getByModulo('usuariodireccion');
        if (isset($moduloconfiguracion[0])) {
            $modulo= modulo_model::getAll(array('idmoduloconfiguracion' => $moduloconfiguracion[0], 'tipo' =>1));
            $modulo=$modulo[0]['detalle'];
        }else{
            $modulo=array();
        }


        $com=comuna_model::getAll(array(),array('order'=>'titulo ASC'));
        $comunas=array();
        foreach ($com as $key => $c) {
            $comunas[]=array('title'=>$c['titulo'],'value'=>$c[0],'selected'=>(isset($direccion) && $direccion['idcomuna']==$c[0]));
        }

        $campos_requeridos=array();
        $campos_opcionales=array();
        foreach ($modulo as $key => $m) {
            if(in_array(true,$m['estado'])){
                unset($m['estado']);
                if($m['field']=='idcomuna'){
                    $m['options']=$comunas;
                }else{
                    $m['value']=(isset($direccion))?$direccion[$m['field']]:'';
                }
                $m['is_text']=($m['tipo']=='text');
                if($m['required']){
                    $campos_requeridos[]=$m;
                }else{
                    $campos_opcionales[]=$m;
                }
            }
        }
        view::set('campos_requeridos',$campos_requeridos);
        view::set('campos_opcionales',$campos_opcionales);
        view::set('title',isset($direccion)?$direccion['titulo']:'Nueva dirección');
        view::set('id',isset($direccion)?$direccion[0]:'');


        
        $token                      = sha1(uniqid(microtime(), true));
        $_SESSION['direccion_token'] = array('token' => $token, 'time' => time());
        view::set('token', $token);

        view::render('user/direcciones-detalle');

        $footer = new footer();
        $footer->normal();
    }

    
    /**
     * direccion_process
     * procesa el POST para modificacion de direccion
     *
     * @return json
     * 
     */
    public function direccion_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $verificar = self::verificar(true);
        if(!$verificar['exito']){
            $respuesta['mensaje']='Debes ingresar a tu cuenta';
            return $respuesta;
        }
        $campos    = functions::test_input($_POST['campos']);

        if (isset($campos['token']) && isset($campos['id'])) {
            if (isset($_SESSION['direccion_token']['token']) && $_SESSION['direccion_token']['token'] == $campos['token']) {
                if (time() - $_SESSION['direccion_token']['time'] <= 360) {
                    unset($campos['token']);
                    $campos['idusuario']=$_SESSION[usuario_model::$idname . app::$prefix_site];
                    $campos['tipo']=1;
                    if($campos['id']!=''){
                        $respuesta['exito'] = usuariodireccion_model::update($campos);
                    }else{
                        $respuesta['exito'] = usuariodireccion_model::insert($campos);
                    }
                    if ($respuesta['exito']) {
                        $respuesta['mensaje'] = "Direccion guardada correctamente";
                        
                        if(isset($_GET['next_url'])){
                            $respuesta['next_url'] = $_GET['next_url'];
                        }
                    }else{
                        $respuesta['mensaje'] = "Hubo un error al guardar la direccion, comprueba los campos obligatorios e intentalo nuevamente";
                    }
                } else {
                    $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
                }
            } else {
                $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
            }
        } else {
            $respuesta['mensaje'] = 'Debes llenar los campos obligatorios';
        }

        echo json_encode($respuesta);
    }



    
    /**
     * lista de pedidos
     *
     * @return void
     */
    public function pedidos(){
        $this->meta($this->seo);
        $verificar = self::verificar(true);
        if ($verificar['exito']) {
            $this->url[] = 'pedidos';
        } else {
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'],'Mis Pedidos');
        $sidebar   = array();
        $sidebar[] = array('title' => "Mis datos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'datos')));
        $sidebar[] = array('title' => "Mis direcciones", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'direcciones')));
        $sidebar[] = array('title' => "Mis pedidos", 'active' => 'active', 'url' => functions::generar_url(array($this->url[0], 'pedidos')));

        view::set('sidebar_user', $sidebar);
        $sidebar=view::render('user/sidebar', false, true);
        view::set('sidebar',$sidebar);
        $ep=pedidoestado_model::getAll(array('tipo'=>1));
        $estados_pedido=array();
        foreach ($ep as $key => $e) {
            $estados_pedido[$e[0]]=$e;
        }


        $usuario= usuario_model::getById($_SESSION[usuario_model::$idname . app::$prefix_site]);
        $pedidos=pedido_model::getByIdusuario($usuario[0],false);//obtiene todos los pedidos del usuario actual, con cualquier estado del pedido
        foreach ($pedidos as $key => $p) {
            if($p['idpedidoestado']==1){ //Quita cualquier pedido que este en carro
                unset($pedidos[$key]);
            }else{
                $pedidos[$key]['total']=functions::formato_precio($p['total']);
                $pedidos[$key]['fecha']=($p['fecha_pago']!=0)?$p['fecha_pago']:$p['fecha_creacion'];
                $pedidos[$key]['url']=functions::generar_url(array($this->url[0], 'pedido',$p['cookie_pedido']));
                $pedidos[$key]['estado']=$estados_pedido[$p['idpedidoestado']]['titulo'];
                $pedidos[$key]['background_estado']=$estados_pedido[$p['idpedidoestado']]['color'];
                $pedidos[$key]['color_estado']=functions::getContrastColor($estados_pedido[$p['idpedidoestado']]['color']);
            }
        }
        view::set('pedidos',$pedidos);        
        view::render('user/pedidos-lista');

        $footer = new footer();
        $footer->normal();
    }


   /**
     * Ver o pagar pedido
     *
     * @param  array $var
     *
     * @return void
     */
    public function pedido($var=array()){
        $this->meta($this->seo);
        $verificar = self::verificar(true);
        if ($verificar['exito']) {
            if(isset($var[0])){
                $pedido=pedido_model::getByCookie($var[0],false);
                //Podria desaparecer si se necesita que cualquier pedido sea publico
                if(isset($pedido['idusuario']) && $pedido['idusuario']==$_SESSION[usuario_model::$idname . app::$prefix_site]){
                    $this->url[] = 'pedido';
                    $this->url[] = $var[0];
                }else{
                //Podria desaparecer si se necesita que cualquier pedido sea publico
                    $this->url[] = 'pedidos';
                }
            }else{
                $this->url[] = 'pedido';
            }
        } else {
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], $this->metadata['title'],'Detalle del pedido');
        $sidebar   = array();
        $sidebar[] = array('title' => "Mis datos", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'datos')));
        $sidebar[] = array('title' => "Mis direcciones", 'active' => '', 'url' => functions::generar_url(array($this->url[0], 'direcciones')));
        $sidebar[] = array('title' => "Mis pedidos", 'active' => 'active', 'url' => functions::generar_url(array($this->url[0], 'pedidos')));

        view::set('sidebar_user', $sidebar);
        $sidebar=view::render('user/sidebar', false, true);

        $ep=pedidoestado_model::getAll();
        $estados_pedido=array();
        foreach ($ep as $key => $e) {
            $estados_pedido[$e[0]]=$e;
        }
        $direcciones_pedido = pedidodireccion_model::getAll(array('idpedido' => $pedido['idpedido']));
        $productos_pedido=pedidoproducto_model::getAll(array('idpedido' => $pedido['idpedido']));
        foreach ($direcciones_pedido as $key => $dp) {
            $direcciones_pedido[$key]['precio']=functions::formato_precio($dp['precio']);
            $direcciones_pedido[$key]['estado']=$estados_pedido[$dp['idpedidoestado']]['titulo'];
            $direcciones_pedido[$key]['background_estado']=$estados_pedido[$dp['idpedidoestado']]['color'];
            $direcciones_pedido[$key]['color_estado']=functions::getContrastColor($estados_pedido[$dp['idpedidoestado']]['color']);
            $lista_productos = array();
            foreach ($productos_pedido as $k => $p) {
                if ($p['idpedidodireccion'] == $dp[0]) {
                    $portada      = image::portada($p['foto']);
                    $thumb_url    = image::generar_url($portada, '');
                    $p['total']=functions::formato_precio($p['total']);
                    $p['foto']=$thumb_url;
                    $lista_productos[] = $p;
                    unset($productos_pedido[$k]);
                }
            }
            $direcciones_pedido[$key]['lista_productos']=$lista_productos;
        }
        $pedido['total']=functions::formato_precio($pedido['total']);
        $pedido['direcciones_pedido']=$direcciones_pedido;
        $pedido['estado']=$estados_pedido[$pedido['idpedidoestado']]['titulo'];
        $pedido['background_estado']=$estados_pedido[$pedido['idpedidoestado']]['color'];
        $pedido['color_estado']=functions::getContrastColor($estados_pedido[$pedido['idpedidoestado']]['color']);

        view::set_array($pedido);
        view::set('sidebar',$sidebar);

        $medios_pago=array();
        $descripcion_pago='';
        if($pedido['idpedidoestado']==3 || $pedido['idpedidoestado']==7){ // Solo si hay pago pendiente
            $medios_pago=mediopago_model::getAll();
            $seo_pago=seo_model::getById(12); //seo medios de pago
            foreach ($medios_pago as $key => $mp) {
                $url=functions::generar_url(array($seo_pago['url'],'medio',$mp[0],$pedido['cookie_pedido']));
                $medios_pago[$key]['url']=$url;
            }
        }else{
            $medio_pago=mediopago_model::getById($pedido['idmediopago']);
            $descripcion_pago=$medio_pago['descripcion'];
        }
        view::set('medios_pago',$medios_pago);
        view::set('descripcion_pago',$descripcion_pago);
        view::set('is_descripcion_pago',(trim(strip_tags($descripcion_pago))!=''));
        view::set('pago',(count($medios_pago)>0) );

        view::render('user/pedidos-detalle');

        $footer = new footer();
        $footer->normal();
    }








    public function registro()
    {
        $this->meta($this->seo);
        
        $verificar = self::verificar(true);
        if($verificar['exito']){
            if(isset($_GET['next_url'])){
                $this->url = explode('/',$_GET['next_url']);
            }else{
                $this->url[] = 'datos';
            }
        }else{
            $this->url[] = 'registro';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Registro');

        $token                      = sha1(uniqid(microtime(), true));
        $_SESSION['registro_token'] = array('token' => $token, 'time' => time());
        view::set('token', $token);
        view::set('url_login', functions::generar_url(array($this->url[0], 'login')));
        view::render('user/registro');

        $footer = new footer();
        $footer->normal();
    }
    
    /**
     * registro_process
     * procesa el POST para registro
     *
     * @return json
     * 
     */
    public function registro_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST['campos']);

        if (isset($campos['nombre']) && isset($campos['telefono']) && isset($campos['email']) && isset($campos['pass']) && isset($campos['pass_repetir']) && isset($campos['token'])) {
            if (isset($_SESSION['registro_token']['token']) && $_SESSION['registro_token']['token'] == $campos['token']) {
                if (time() - $_SESSION['registro_token']['time'] <= 120) {
                    $respuesta = usuario_model::registro($campos['nombre'], $campos['telefono'], $campos['email'], $campos['pass'], $campos['pass_repetir']);
                    if ($respuesta['exito']) {
                        $respuesta['exito'] = usuario_model::login($campos['email'], $campos['pass'], isset($campos['recordar']));
                        if (!$respuesta['exito']) {
                            $respuesta['mensaje'] = "Cuenta creada correctamente, pero ha ocurrido un error al ingresar. Intenta loguearte";
                        } else {
                            $respuesta['mensaje'] = "Bienvenido";
                        }
                    }
                } else {
                    $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
                }
            } else {
                $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
            }
        } else {
            $respuesta['mensaje'] = 'Debes llenar todos los campos';
        }

        echo json_encode($respuesta);
    }
    public function recuperar(){
        $this->meta($this->seo);
        $this->url[] = 'recuperar';
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Recuperar contraseña');

        $token                   = sha1(uniqid(microtime(), true));
        $_SESSION['recuperar_token'] = array('token' => $token, 'time' => time());
        view::set('token', $token);
        view::set('url_registro', functions::generar_url(array($this->url[0], 'registro')));
        view::render('user/recuperar');

        $footer = new footer();
        $footer->normal();

    }
    
    /**
     * recuperar_process
     * procesa el POST para recuperacion de contraseña
     *
     * @return json
     * 
     */
    public function recuperar_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST['campos']);

        if(isset($campos['email']) && isset($campos['token'])){
            if($_SESSION['recuperar_token']['token']==$campos['token']){
                if(time()-$_SESSION['recuperar_token']['time']<=120){
                    $respuesta=usuario_model::recuperar($campos['email']);
                    if($respuesta["exito"]){
                        $respuesta['mensaje'] = "Se ha enviado tu nueva contraseña a tu email. recuerda modificarla al ingresar.";
                    }
                }else{
                    $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
                }
            }else{
                $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
            }
        }else{
            $respuesta['mensaje'] = 'Debes llenar todos los campos';
        }

        echo json_encode($respuesta);
    }
    public function login()
    {
        $this->meta($this->seo);
        $verificar = self::verificar(true);
        if($verificar['exito']){
            if(isset($_GET['next_url'])){
                $this->url = explode('/',$_GET['next_url']);
            }else{
                $this->url[] = 'datos';
            }
        }else{
            $this->url[] = 'login';
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();

        $banner = new banner();
        $banner->individual($this->seo['banner'], 'Login');

        $token                   = sha1(uniqid(microtime(), true));
        $_SESSION['login_token'] = array('token' => $token, 'time' => time());
        view::set('token', $token);
        view::set('url_recuperar', functions::generar_url(array($this->url[0], 'recuperar')));
        view::set('url_registro', functions::generar_url(array($this->url[0], 'registro')));
        view::render('user/login');

        $footer = new footer();
        $footer->normal();
    }
    
    /**
     * login_process
     * procesa el POST para login
     *
     * @return json
     * 
     */
    public function login_process()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $campos    = functions::test_input($_POST['campos']);

        if (isset($campos['email']) && isset($campos['pass']) && isset($campos['token'])) {
            if (isset($_SESSION['login_token']['token']) && $_SESSION['login_token']['token'] == $campos['token']) {
                if (time() - $_SESSION['login_token']['time'] <= 120) {
                    $respuesta['exito'] = usuario_model::login($campos['email'], $campos['pass'], isset($campos['recordar']));
                    if (!$respuesta['exito']) {
                        $respuesta['mensaje'] = "Ha ocurrido un error al ingresar. Revisa tus datos e intenta nuevamente";
                    } else {
                        $respuesta['mensaje'] = "Bienvenido";
                        if(isset($_GET['next_url'])){
                            $respuesta['next_url'] = $_GET['next_url'];
                        }
                    }
                } else {
                    $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente'.(time() - $_SESSION['login_token']['time']);
                }
            } else {
                $respuesta['mensaje'] = 'Error de token, recarga la pagina e intenta nuevamente';
            }
        } else {
            $respuesta['mensaje'] = 'Debes llenar todos los campos';
        }

        echo json_encode($respuesta);
    }
    /**
     * verificar
     *
     * @param  mixed $return
     *
     * @return array o json
     */
    public static function verificar(bool $return = false)
    {
        $respuesta   = array('exito' => false, 'mensaje' => '');
        $logueado    = usuario_model::verificar_sesion();
        if (!$logueado) {
            if (isset($_COOKIE['cookieusuario' . app::$prefix_site])) {
                $logueado = usuario_model::login_cookie($_COOKIE['cookieusuario' . app::$prefix_site]);
            }
        }
        $respuesta['exito'] = $logueado;
        if ($logueado) {
            $nombre               = explode(" ", $_SESSION['nombreusuario' . app::$prefix_site]);
            $respuesta['mensaje'] = $nombre[0];
        }
        if ($return) {
            return $respuesta;
        } else {
            echo json_encode($respuesta);
        }
    }
    public function logout()
    {
        usuario_model::logout();
        echo json_encode(array('exito' => true, 'mensaje' => 'Gracias por visitar nuestro sitio'));
    }
}
