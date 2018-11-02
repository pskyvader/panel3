<?php
namespace app\controllers\front\themes\gatografias;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\functions;

class instagram
{
    public function __construct()
    {
    }
    public function index()
    {
        $config          = app::getConfig();
        $instagram_token = $config['instagram_token'];
        $photo_count     = 8;
        $json_link       = "https://api.instagram.com/v1/users/self/media/recent/?";
        $json_link .= http_build_query(array('access_token' => $instagram_token, 'count' => $photo_count));
        $json = functions::decode_json(file_get_contents($json_link));
        $respuesta=array();
        foreach ($json['data'] as $key => $data) {
            $respuesta[]=array(
                'url'=>$data['link'],
                'title'=>$data['caption']['text'],
                'images'=>$data['images'],
            );
        }
        echo functions::encode_json($respuesta);
    }
}
