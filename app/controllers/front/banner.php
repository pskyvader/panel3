<?php
namespace app\controllers\front;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\functions;
use \core\image;
use \core\view;

class banner
{
    public static $sizes = array(
        array('foto' => 'foto1', 'size' => '1200'),
        array('foto' => 'foto2', 'size' => '991'),
        array('foto' => 'foto3', 'size' => '768'),
        array('foto' => 'foto4', 'size' => '0'),
    );
    public function normal($row_banner = array())
    {
        if (count($row_banner) > 0) {
            $thumb = array();
            $banner = array();
            foreach ($row_banner as $key => $b) {
                if (isset($b["foto"][0])) {
                    $foto = image::generar_url($b["foto"][0], 'foto1', 'banner', $b[0]);
                } else {
                    $foto = '';
                }
                if ($foto != '') {
                    $thumb[] = array('id' => $key, 'active' => ($key == 0) ? 'active' : '');

                    $srcset = $this->srcset($b["foto"][0], $b[0]);

                    $banner[] = array(
                        'srcset' => $srcset,
                        'title' => $b['titulo'],
                        'active' => ($key == 0) ? 'active' : '',
                        'data' => ($key != 0) ? 'data-' : '',
                        'foto' => $foto,
                        'texto1' => $b['texto1'], 'is_texto1' => ($b['texto1'] != ''),
                        'texto2' => $b['texto2'], 'is_texto2' => ($b['texto2'] != ''),
                        'link' => functions::ruta($b['link']), 'is_link' => ($b['link'] != ''),
                        'background' => image::generar_url($b["foto"][0], 'color', 'banner', $b[0]),
                    );
                }

            }
            view::set('thumb', $thumb);
            view::set('banner', $banner);
            view::render('banner');
        }
    }

    public function individual($row_banner = array())
    {
        if (count($row_banner) > 0) {
            $b = $row_banner[0];

            if (isset($b["foto"][0])) {
                $foto1 = image::generar_url($b["foto"][0], 'foto1', 'banner', $b[0]);
                $name = explode(".", $b["foto"][0]['url']);
                $mime = 'image/' . strtolower(array_pop($name));

            } else {
                $foto1 = '';
            }
            if ($foto1 != '') {
                $srcset = array();

                $banner = array(
                    'srcset' => $srcset,
                    'title' => $b['titulo'],
                    'active' => ($key == 0) ? 'active' : '',
                    'data' => ($key != 0) ? 'data-' : '',
                    'foto' => $foto1,
                    'texto1' => $b['texto1'], 'is_texto1' => ($b['texto1'] != ''),
                    'texto2' => $b['texto2'], 'is_texto2' => ($b['texto2'] != ''),
                    'link' => functions::ruta($b['link']), 'is_link' => ($b['link'] != ''),
                    'background' => image::generar_url($b["foto"][0], 'color', 'banner', $b[0]),
                );
            }

            view::set('banner', $banner);
            view::render('banner');
        }
    }

    public function srcset($foto_base, $id)
    {
        $images = self::$sizes;
        $srcset = array();
        foreach ($images as $k => $size) {
            $foto = image::generar_url($foto_base, $size['foto'], 'banner', $id, 'webp');
            if ($foto != '') {
                $srcset[] = array('media' => '(min-width: ' . $size['size'] . 'px)', 'url' => $foto, 'type' => 'image/webp');
            }
        }
        foreach ($images as $k => $size) {
            $foto = image::generar_url($foto_base, $size['foto'], 'banner', $id);
            if ($foto != '') {
                $srcset[] = array('media' => '(min-width: ' . $size['size'] . 'px)', 'url' => $foto, 'type' => 'image/jpg');
            }
        }
        return $srcset;
    }
}
