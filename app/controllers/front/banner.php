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
                if(isset($b["foto"][0])){
                    $foto1=image::generar_url($b["foto"][0], 'foto1', 'banner', $b[0]);
                    $name = explode(".", $b["foto"][0]['url']);
                    $mime = 'image/'.strtolower(array_pop($name));

                }else{
                    $foto1='';
                }
                if($foto1!=''){
                    $thumb[]=array('id'=>$key,'active'=>($key==0)?'active':'');
                    $srcset=array();

                    $srcset[]=array('media'=>'(min-width: 1200px)','url'=>$foto1,'type'=>$mime);
                    
                    $foto=image::generar_url($b["foto"][0], 'foto1', 'banner', $b[0],'webp');
                    if($foto!='')  $srcset[]=array('media'=>'(min-width: 1200px)','url'=>$foto,'type'=>'image/webp');
                    if($foto!='')  $srcset[]=array('media'=>'','url'=>$foto,'type'=>'image/webp');

                    $foto=image::generar_url($b["foto"][0], 'foto2', 'banner', $b[0]);
                    if($foto!='')  $srcset[]=array('media'=>'(min-width: 991px)','url'=>$foto,'type'=>$mime);
                    $foto=image::generar_url($b["foto"][0], 'foto2', 'banner', $b[0],'webp');
                    if($foto!='')  $srcset[]=array('media'=>'(min-width: 991px)','url'=>$foto,'type'=>'image/webp');
                    
                    $foto=image::generar_url($b["foto"][0], 'foto3', 'banner', $b[0]);
                    if($foto!='')  $srcset[]=array('media'=>'(min-width: 991px)','url'=>$foto,'type'=>$mime);
                    $foto=image::generar_url($b["foto"][0], 'foto3', 'banner', $b[0],'webp');
                    if($foto!='')  $srcset[]=array('media'=>'(min-width: 991px)','url'=>$foto,'type'=>'image/webp');
                    
                    $foto=image::generar_url($b["foto"][0], 'foto4', 'banner', $b[0]);
                    if($foto!='')  $srcset[]=array('media'=>'(min-width: 991px)','url'=>$foto,'type'=>$mime);
                    $foto=image::generar_url($b["foto"][0], 'foto4', 'banner', $b[0],'webp');
                    if($foto!='')  $srcset[]=array('media'=>'(min-width: 991px)','url'=>$foto,'type'=>'image/webp');


                    $banner[]=array(
                        'srcset'=>$srcset,
                        'title'=>$b['titulo'],
                        'active'=>($key==0)?'active':'',
                        'data'=>($key!=0)?'data-':'',
                        'foto1'=>$foto1,
                        'texto1'=>$b['texto1'],'is_texto1'=>($b['texto1']!=''),
                        'texto2'=>$b['texto2'],'is_texto2'=>($b['texto2']!=''),
                        'link'=>functions::ruta($b['link']),'is_link'=>($b['link']!=''),
                        'background'=>image::generar_url($b["foto"][0], 'color', 'banner', $b[0]),
                    );
                }
                
            }
            view::set('thumb',$thumb);
            view::set('banner',$banner);
            view::render('banner');
        }
    }
}
