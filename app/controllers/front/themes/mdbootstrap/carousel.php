<?php
namespace app\controllers\front\themes\mdbootstrap;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\image;
use \core\view;

class carousel
{
    public static $sizes = array(
        array('foto' => 'foto1', 'size' => '1200'),
        array('foto' => 'foto2', 'size' => '991'),
        array('foto' => 'foto3', 'size' => '768'),
        array('foto' => 'foto4', 'size' => '0'),
    );
    public function normal($row_carousel = array(), $titulo)
    {
        if (count($row_carousel) > 0) {
            $thumb = array();

            foreach ($row_carousel as $key => $c) {
                if (isset($c)) {
                    $foto = image::generar_url($c, 'thumb_carousel');
                } else {
                    $foto = '';
                }
                if ($foto != '') {
                    $foto_w = image::generar_url($c, 'thumb_carousel', 'webp');
                    if ($foto_w != '') {
                        $srcset = array(array('url' => $foto_w, 'type' => 'image/webp'));
                    }
                    $thumb[] = array(
                        'srcset' => $srcset,
                        'id' => $key,
                        'title' => $titulo,
                        'active' => ($key == 0) ? 'active' : '',
                        'foto' => $foto,
                    );
                }
            }

            $carousel = array();
            foreach ($row_carousel as $key => $c) {
                if (isset($c)) {
                    $foto = image::generar_url($c, 'foto1');
                } else {
                    $foto = '';
                }
                if ($foto != '') {

                    $srcset = $this->srcset($c);

                    $carousel[] = array(
                        'id' => $key,
                        'srcset' => $srcset,
                        'title' => $titulo,
                        'active' => ($key == 0) ? 'active' : '',
                        'data' => ($key != 0) ? 'data-' : '',
                        'foto' => $foto,
                        'original' => image::generar_url($c, ''),
                    );
                }
            }
            view::set('thumb', $thumb);
            view::set('carousel', $carousel);
            view::render('carousel');
        }
    }

    public function srcset($foto_base)
    {
        $images = self::$sizes;
        $srcset = array();
        foreach ($images as $k => $size) {
            $foto = image::generar_url($foto_base, $size['foto'], 'webp');
            if ($foto != '') {
                $srcset[] = array('media' => '(min-width: ' . $size['size'] . 'px)', 'url' => $foto, 'type' => 'image/webp');
            }
        }
        foreach ($images as $k => $size) {
            $foto = image::generar_url($foto_base, $size['foto']);
            if ($foto != '') {
                $srcset[] = array('media' => '(min-width: ' . $size['size'] . 'px)', 'url' => $foto, 'type' => 'image/jpg');
            }
        }
        return $srcset;
    }
}
