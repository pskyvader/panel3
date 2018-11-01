<?php
namespace app\controllers\back\themes\paper;

defined("APPPATH") or die("Acceso denegado");
use \app\models\seo as seo_model;

class seo extends base
{
    protected $url = array('seo');
    protected $metadata = array('title' => 'SEO','modulo'=>'seo');
    protected $breadcrumb = array();
    public function __construct()
    {
        parent::__construct(new seo_model);
    }
    public function get_all()
    {
        $respuesta=array('exito'=>false,'mensaje'=>'Debes recargar la pagina');
        if ($this->contiene_tipos && !isset($_GET['tipo'])) {
            echo json_encode($respuesta);
            return;
        }
        if ($this->contiene_hijos && !isset($_GET['idpadre'])) {
            echo json_encode($respuesta);
            return;
        }
        $where = array();
        if ($this->contiene_tipos) {
            $where['tipo'] = $_GET['tipo'];
        }
        if ($this->contiene_hijos) {
            $where['idpadre'] = $_GET['idpadre'];
        }
        if (isset($this->class_parent)) {
            $class_parent = $this->class_parent;
            if (isset($_GET[$class_parent::$idname])) {
                $where[$class_parent::$idname] = $_GET[$class_parent::$idname];
            }
        }
        $condiciones = array();
        $select="";
        $class=$this->class;
        $row=$class::getAll($where, $condiciones, $select);
        foreach ($row as $key => $value) {
            $row[$key]['foto']=array_merge($row[$key]['foto'],$row[$key]['banner']);
        }
        echo json_encode($row);
    }
}
