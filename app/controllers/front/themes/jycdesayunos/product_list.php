<?php
namespace app\controllers\front\themes\jycdesayunos;

defined("APPPATH") or die("Acceso denegado");
use \app\models\configuracion as configuracion_model;
use \app\models\producto as producto_model;
use \app\models\productocategoria as productocategoria_model;
use \core\image;
use \core\functions;
use \core\view;

class product_list extends base
{
    private $view   = 'grid';
    private $order  = 'orden';
    private $search = '';
    private $page   = 1;
    private $limit  = 6;
    private $count  = 0;
    public function __construct()
    {
        parent::__construct($_REQUEST['idseo']);
        $this->view   = (isset($_GET['view']) && $_GET['view'] == 'list') ? 'list' : 'grid';
        $this->order  = (isset($_GET['order']) && $_GET['order'] != '') ? trim(strip_tags($_GET['order'])) : 'orden';
        $this->search = (isset($_GET['search'])) ? trim(strip_tags($_GET['search'])) : '';
        $this->page   = (isset($_GET['page']) && $_GET['page'] != '') ? (int) trim(strip_tags($_GET['page'])) : 1;
        $this->limit  = (isset($_GET['limit']) && $_GET['limit'] != '') ? (int) trim(strip_tags($_GET['limit'])) : 6;
    }
    public function is_search(){
        $is_search=($this->search!='');
        view::set('is_search', $is_search);
        view::set('search', $this->search);
    }
    public function sidebar($categoria = null)
    {
        $variables = array();
        if ($this->seo['tipo_modulo'] != 0) {
            $variables['tipo'] = $this->seo['tipo_modulo'];
        }
        if ($this->modulo['hijos']) {
            if ($categoria == null) {
                $variables['idpadre'] = 0;
            } else {
                $variables['idpadre'] = $categoria[0];
            }
        }
        $row                = productocategoria_model::getAll($variables);
        $sidebar_categories = array();
        foreach ($row as $key => $s) {
            $sidebar_categories[] = array('title' => $s['titulo'], 'active' => '', 'url' => functions::url_seccion(array($this->url[0], 'category'), $s, false, null));
        }

        $is_sidebar_categories = (count($sidebar_categories) > 0);
        $is_sidebar_prices     = false;
        if ($is_sidebar_categories || $is_sidebar_prices) {
            $is_sidebar = true;
        } else {
            $is_sidebar = false;
        }

        if($is_sidebar){
            view::set('title', "Categorias");
            view::set('is_sidebar_category', $is_sidebar_categories);
            view::set('sidebar_categories', $sidebar_categories);
            view::set('is_sidebar_prices', $is_sidebar_prices);
            return view::render('product/sidebar', false, true);
        }else{
            return "";
        }
    }

    public function orden_producto()
    {
        $orden_producto = configuracion_model::getByVariable('orden_producto');
        if (!is_bool($orden_producto)) {
            $orden_producto = explode(',', $orden_producto);
            foreach ($orden_producto as $key => $op) {
                $orden_producto[$key] = explode(':', $op);
                foreach ($orden_producto[$key] as $k => $o) {
                    $orden_producto[$key][$k] = trim($o);
                }
            }
        }
        if (!is_array($orden_producto) || count($orden_producto) == 0) {
            $orden_producto = array(
                array('orden', 'Recomendados'),
                array('ventas', 'Más vendidos'),
                array('precio ASC', 'Precio de menor a mayor'),
                array('precio DESC', 'Precio de mayor a menor'),
                array('titulo ASC', 'A-Z'),
                array('titulo DESC', 'Z-A'),
            );
            $orden_producto_guardar = array();
            foreach ($orden_producto as $key => $op) {
                $orden_producto_guardar[$key] = implode(':', $op);
            }
            $orden_producto_guardar = implode(',', $orden_producto_guardar);
            configuracion_model::setByVariable('orden_producto', $orden_producto_guardar);
        }

        $orden_producto_mostrar = array();

        foreach ($orden_producto as $key => $op) {
            $orden_producto_mostrar[] = array(
                'title'  => $op[1],
                'action' => $op[0],
                'active' => (isset($_GET['order']) && $_GET['order'] == $op[0]),
            );
        }

        view::set('active_grid', ($this->view == 'grid') ? 'active' : '');
        view::set('active_list', ($this->view == 'list') ? 'active' : '');
        view::set('orden_producto', $orden_producto_mostrar);
    }
    public function limit_producto()
    {
        $limits = array(
            6   => array('action' => 6, 'title' => 6, 'active' => false),
            12  => array('action' => 12, 'title' => 12, 'active' => false),
            30  => array('action' => 30, 'title' => 30, 'active' => false),
            120 => array('action' => 120, 'title' => 120, 'active' => false),
        );
        if (isset($limits[$this->limit])) {
            $limits[$this->limit]['active'] = true;
        }
        view::set('limit_producto', $limits);
    }
    public function product_list($categoria = null)
    {
        $where = array();
        if ($this->seo['tipo_modulo'] != 0) {
            $where['tipo'] = $this->seo['tipo_modulo'];
        }
        if ($categoria != null) {
            $where[productocategoria_model::$idname] = $categoria[0];
        }
        $condiciones = array('order' => $this->order);
        if ($this->search != '') {
            $condiciones['palabra'] = $this->search;
        }
        $this->count = producto_model::getAll($where, $condiciones, 'total');

        $condiciones['limit'] = $this->limit;
        if ($this->page > 1) {
            $condiciones['limit']  = (($this->page - 1) * $this->limit);
            $condiciones['limit2'] = ($this->limit);
        }

        $productos = producto_model::getAll($where, $condiciones);
        if (count($productos) > 0) {
            $lista_productos = $this->lista_productos($productos, 'detail', 'foto2');
            view::set('lista_productos', $lista_productos);
            if ($this->view == 'grid') {
                 // Comprobar si existe o no sidebar, para agrandar o achicar el tamaño del producto
                $variables = array();
                if ($this->seo['tipo_modulo'] != 0) {
                    $variables['tipo'] = $this->seo['tipo_modulo'];
                }
                if ($this->modulo['hijos']) {
                    if ($categoria == null) {
                        $variables['idpadre'] = 0;
                    } else {
                        $variables['idpadre'] = $categoria[0];
                    }
                }
                $count = productocategoria_model::getAll($variables, array(), 'total');
                if ($count > 0) {
                    view::set('col-lg', 'col-lg-6');
                } else {
                    view::set('col-lg', 'col-lg-4');
                }

                view::set('col-md', 'col-md-12');
                $product_list = view::render('product/grid', false, true);
            } else {
                $product_list = view::render('product/list', false, true);
            }
        }
        return $product_list;
    }

    public function pagination()
    {
        $pagination = array();
        $rango      = 5;
        $min        = 1;
        $max        = (int) ($this->count / $this->limit);
        if ($max < ($this->count / $this->limit)) {
            $max++;
        }
        $total = $max;
        $sw    = false;
        $page  = $this->page;
        while ((($max - $min) + 1) > $rango) {
            if ($sw) {
                if ($min != $page && $min + 1 != $page) {
                    $min++;
                }
            } else {
                if ($max != $page && $max - 1 != $page) {
                    $max--;
                }
            }
            $sw = !$sw;
        }

        $_GET['page'] = $page - 1;
        $pagination[] = array(
            'class_page' => 'previous ' . (($page > 1) ? '' : 'disabled'),
            'url_page'   => (($page > 1) ?  functions::generar_url($this->url) : functions::generar_url($this->url,false)),
            'text_page'  => '<i class="fa fa-angle-left"> </i>',
        );

        for ($i = $min; $i <= $max; $i++) {
            $_GET['page'] = $i;
            $pagination[] = array(
                'class_page' => (($page == $i) ? 'active' : ''),
                'url_page'   => functions::generar_url($this->url),
                'text_page'  => $i,
            );
        }

        $_GET['page'] = $page + 1;
        $pagination[] = array(
            'class_page' => 'next ' . (($page < $total) ? '' : 'disabled'),
            'url_page'   => (($page < $total) ? functions::generar_url($this->url) : functions::generar_url($this->url,false)) ,
            'text_page'  => '<i class="fa fa-angle-right"> </i> ',
        );
        view::set('pagination', $pagination);
    }

    
    public function lista_productos($row, $url = 'detail', $recorte = 'foto1')
    {
        $lista = array();
        foreach ($row as $key => $v) {
            $portada = image::portada($v['foto']);
            $c       = array(
                'id'          => $v[0],
                'title'       => $v['titulo'],
                'is_descuento'       => ($v['precio_final']!=$v['precio']),
                'price'       => functions::formato_precio($v['precio_final']),
                'old_price'       => functions::formato_precio($v['precio']),
                'is_stock'       => ($v['stock']>0),
                'image'       => image::generar_url($portada, $recorte),
                'description' => strip_tags($v['resumen']),
                'srcset'      => array(),
                'url'         => functions::url_seccion(array($this->url[0], $url), $v),
            );
            $src = image::generar_url($portada, $recorte, 'webp');
            if ($src != '') {
                $c['srcset'][] = array('media' => '', 'src' => $src, 'type' => 'image/webp');
            }
            if ($c['image'] != "") {
                $lista[] = $c;
            }
        }
        return $lista;
    }

}
