<?php
namespace app\controllers\back;

defined("APPPATH") or die("Acceso denegado");
use \app\models\administrador as administrador_model;
use \app\models\sitemap as sitemap_model;
use \core\app;
use \core\functions;
use \core\view;

class sitemap extends base
{
    protected $url = array('sitemap');
    protected $metadata = array('title' => 'sitemap', 'modulo' => 'sitemap');
    protected $breadcrumb = array();
    public function __construct()
    {

    }
    public function index()
    {
        if (!administrador_model::verificar_sesion()) {
            $this->url = array('login', 'index', 'sitemap');
        }
        functions::url_redirect($this->url);

        $head = new head($this->metadata);
        $head->normal();

        $header = new header();
        $header->normal();
        $aside = new aside();
        $aside->normal();

        $breadcrumb = array(
            array('url' => functions::generar_url($this->url), 'title' => $this->metadata['title'], 'active' => 'active'),
        );
        $row = sitemap_model::getAll(array('ready' => true, 'valid' => ''), array('order' => 'idsitemap DESC'));
        $log = array();
        foreach ($row as $key => $r) {
            $log[] = array('url' => $r['url']);
        }
        $row = sitemap_model::getAll(array('ready' => true), array(), 'COUNT(url) as count');
        $listos = (int) $row[0]['count'];
        $row = sitemap_model::getAll(array('ready' => false), array(), 'COUNT(url) as count');
        $pendientes = (int) $row[0]['count'];
        if ($listos == 0 && $pendientes == 0) {
            $total = 0;
        } else {
            $total = ($listos * 100) / ($listos + $pendientes);
        }

        
        $dir = app::get_dir(true);
        $mensaje_error='';
        if(is_writable($dir)){
            if(file_exists($dir . 'sitemap.xml')){
                if(!is_writable($dir . 'sitemap.xml')){
                    $mensaje_error='Debes dar permisos de escritura o eliminar el archivo '.$dir . 'sitemap.xml';
                }
            }
        }else{
            $mensaje_error='Debes dar permisos de escritura en '.$dir;
        }
        $is_error=($mensaje_error!='');

        view::set('breadcrumb', $breadcrumb);
        view::set('log', $log);
        view::set('title', $this->metadata['title']);
        view::set('progreso', $total);
        view::set('is_error', $is_error);
        view::set('mensaje_error', $mensaje_error);
        view::set('url_sitemap', functions::generar_url(array('sitemap.xml'), array('time'=>time()), false, true));
        view::render('sitemap');

        $footer = new footer();
        $footer->normal();
    }
    public function vaciar()
    {
        $respuesta = sitemap_model::truncate();
        $respuesta['vacio'] = true;
        echo json_encode($respuesta);
    }

    public function generar()
    {
        $respuesta = array('exito' => false, 'mensaje' => '');
        $row = sitemap_model::getAll();
        $sitio_base = app::get_url(true);
        if (count($row) == 0) {
            $r = $this->head($sitio_base, $sitio_base);
            $valido = $r['mensaje'];
            $ready = ($valido != '') ? true : false;
            $insert = array('idpadre' => 0, 'url' => $sitio_base, 'depth' => 0, 'valid' => $valido, 'ready' => $ready);
            if (isset($r['new_url']) && $r['new_url'] != '') {
                $insert['valid'] .= " redirect " . $r['new_url'];
                $insert['ready'] = true;
            }
            $id = sitemap_model::insert($insert);
            if (!$r['exito'] && $valido == '' && isset($r['new_url']) && $r['new_url'] != '') {
                $existe = sitemap_model::getAll(array('url' => $r['new_url']), array('limit' => 1));
                if (count($existe) == 0) {
                    $insert = array('idpadre' => $id, 'url' => $r['new_url'], 'depth' => 1, 'valid' => $valido, 'ready' => $ready);
                    $id = sitemap_model::insert($insert);
                }
            }
            $respuesta['exito'] = true;
        } else {
            $row = sitemap_model::getAll(array('ready' => false));
            if (count($row) == 0) {
                $respuesta = $this->generar_sitemap();
            } else {
                $sitio = $row[0];
                $depth = $sitio['depth'];
                $url = $sitio['url'];
                $sub_sitios = $this->generar_url($url, $sitio_base);

                if (is_array($sub_sitios)) {
                    $update = array('id' => $sitio[0], 'idpadre' => $sitio['idpadre'], 'url' => $sitio['url'], 'depth' => $depth, 'valid' => $sitio['valid'], 'ready' => true);
                    sitemap_model::update($update);
                    $id = $sitio[0];
                    $depth++;
                    foreach ($sub_sitios as $key => $sitios) {
                        $existe = sitemap_model::getAll(array('url' => $sitios), array('limit' => 1));
                        if (count($existe) == 0) {
                            $r = $this->head($sitios, $sitio_base);
                            $valido = $r['mensaje'];
                            $ready = ($valido != '') ? true : false;
                            $insert = array('idpadre' => $id, 'url' => $sitios, 'depth' => $depth, 'valid' => $valido, 'ready' => $ready);
                            if (isset($r['new_url']) && $r['new_url'] != '') {
                                $insert['valid'] .= " redirect " . $r['new_url'];
                                $insert['ready'] = true;
                            }
                            $id = sitemap_model::insert($insert);
                            if (!$r['exito'] && $valido == '' && isset($r['new_url']) && $r['new_url'] != '') {
                                $existe = sitemap_model::getAll(array('url' => $r['new_url']), array('limit' => 1));
                                if (count($existe) == 0) {
                                    $insert = array('idpadre' => $id, 'url' => $r['new_url'], 'depth' => $depth + 1, 'valid' => $valido, 'ready' => $ready);
                                    $id = sitemap_model::insert($insert);
                                }
                            }
                        }
                    }
                } else {
                    $update = array('id' => $sitio[0], 'idpadre' => $sitio['idpadre'], 'url' => $sitio['url'], 'depth' => $depth, 'valid' => $sitio['valid'], 'ready' => true);
                    sitemap_model::update($update);
                }
                $respuesta['exito'] = true;
            }
        }
        $row = sitemap_model::getAll(array('ready' => true), array(), 'COUNT(url) as count');
        $listos = (int) $row[0]['count'];
        $row = sitemap_model::getAll(array('ready' => true, 'valid' => ''), array('limit' => 1, 'order' => 'idsitemap DESC'));
        $respuesta['ultimo'] = $row[0];
        $row = sitemap_model::getAll(array('ready' => false), array(), 'COUNT(url) as count');
        $pendientes = (int) $row[0]['count'];
        if ($listos == 0 && $pendientes == 0) {
            $total = 0;
        } else {
            $total = ($listos * 100) / ($listos + $pendientes);
        }
        $respuesta['progreso'] = $total;

        echo json_encode($respuesta);
    }
    public function generar_sitemap()
    {
        $respuesta = array('exito' => true, 'mensaje' => '', 'generado' => true);
        $lista = sitemap_model::getAll(array('valid' => ''), array('order' => 'depth'));

        $body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $body .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $count = -1;
        $total = count($lista);
        foreach ($lista as $key => $value) {$count++;
            $prioridad = ($total - $count) / $total;
            $prioridad = ($prioridad >= 0.1) ? $prioridad : 0.1;

            $elemento = '<url>' . "\n";
            $elemento .= '<loc>' . $value['url'] . '</loc>' . "\n";
            $elemento .= '<changefreq>monthly</changefreq>' . "\n";
            $elemento .= '<priority>' . round($prioridad, 2) . '</priority>' . "\n";
            //$elemento.='<lastmod>'.$value['profundidad'].'</lastmod>'; //<lastmod>2005-01-01</lastmod>
            $elemento .= '</url>' . "\n";
            $body .= $elemento;
        }

        $body .= '</urlset>';
        $dir = app::get_dir(true);
        $respuesta['exito'] = file_put_contents($dir . 'sitemap.xml', $body);
        if (!$respuesta['exito']) {
            $respuesta['mensaje'] = 'Error al guardar el archivo en ' . $dir . 'sitemap.xml';
        }
        return $respuesta;
    }

    public function generar_url($sitio, $sitio_base)
    {
        $urlContent = file_get_contents($sitio);
        if ($urlContent === false) {
            return false;
        }
        $sublista = array();
        $dom = new \DOMDocument();
        @$dom->loadHTML($urlContent);
        $xpath = new \DOMXPath($dom);
        $hrefs = $xpath->evaluate("/html/body//a");

        for ($i = 0; $i < $hrefs->length; $i++) {
            $href = $hrefs->item($i);
            $url = $href->getAttribute('href');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            // validate url
            if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
                if (strpos($url, $sitio_base) == 0) {
                    $sublista[] = $url;
                }

            } elseif (!filter_var($sitio . $url, FILTER_VALIDATE_URL) === false) {
                if (strpos($sitio . $url, $sitio_base) == 0) {
                    $sublista[] = $sitio . $url;
                }

            }
        }
        return $sublista;
    }
    public function head($sitio, $sitio_base)
    {
        $respuesta = array('exito' => true, 'mensaje' => $this->validar_url($sitio, $sitio_base));
        if ($respuesta['mensaje'] == '') {
            $headers = get_headers($sitio, 1);
            if ($headers[0] != 'HTTP/1.1 200 OK') {
                if ($headers[0] == 'HTTP/1.1 301 Moved Permanently') {
                    if(is_array($headers['Location'])){
                        $headers['Location']=$headers['Location'][0];
                    }
                    $location = $this->head($headers['Location'], $sitio_base);
                    $respuesta['new_url'] = ((isset($location['new_url'])) ? $location['new_url'] : $headers['Location']);
                    if(is_array($respuesta['new_url']))$respuesta['new_url']=$respuesta['new_url'][0];
                    $respuesta['mensaje'] = $location['mensaje'];
                    $respuesta['exito']=false;
                } else {
                    $respuesta['mensaje'] = 'status: ' . $headers[0];
                }
            }
        }
        return $respuesta;
    }
    public function validar_url($sitio, $sitio_base)
    {
        if (
            strpos($sitio, "#") !== false ||
            strpos($sitio, "../") !== false ||
            strpos($sitio, ";") !== false ||
            strpos($sitio, "javascript") !== false ||
            strpos($sitio, "whatsapp") !== false ||
            strpos($sitio, "facebook") !== false ||
            strpos($sitio, "mailto:") !== false ||
            strpos($sitio, "tel:") !== false ||
            strpos($sitio, $sitio_base) === false
        ) {
            return 'invalid';
        } elseif (strpos($sitio, $sitio_base) == 0) {
            return '';
        } else {
            return 'domain';
        }

    }
}
