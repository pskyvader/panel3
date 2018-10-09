<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\functions;
use \core\image;
use \core\view;

class banner
{
    public function normal($row_banner=array())
    { 
        if(count($row_banner)>0){
            $thumb=array();
            $banner=array();
            foreach ($row_banner as $key => $b) {
                $thumb[]=array('id'=>$key,'active'=>($key==0)?'active':'');
                $banner[]=array(
                    'title'=>$b['titulo'],
                    'active'=>($key==0)?'active':'',
                    'data'=>($key!=0)?'data-':'',
                    'texto1'=>$b['texto1'],'is_texto1'=>($b['texto1']!=''),
                    'texto2'=>$b['texto2'],'is_texto2'=>($b['texto2']!=''),
                    'link'=>functions::ruta($b['link']),'is_link'=>($b['link']!=''),
                    'foto1'=>image::generar_url($b["foto"][0], 'foto1', 'banner', $b[0]),
                    'foto2'=>image::generar_url($b["foto"][0], 'foto2', 'banner', $b[0]),
                    'foto3'=>image::generar_url($b["foto"][0], 'foto3', 'banner', $b[0]),
                    'foto4'=>image::generar_url($b["foto"][0], 'foto4', 'banner', $b[0]),
                    'background'=>image::generar_url($b["foto"][0], 'color', 'banner', $b[0]),
                );
            }
            view::set('thumb',$thumb);
            view::set('banner',$banner);
            view::render('banner');
        }
    }
}
