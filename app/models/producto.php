<?php
namespace app\models;

defined("APPPATH") or die("Acceso denegado");
use \core\app;
use \core\database;
use \core\functions;

class producto extends base_model
{
    public static $idname = 'idproducto',
    $table                = 'producto';
    public static function getAll($where = array(), $condiciones = array(), $select = "")
    {
        $connection = database::instance();
        if (!isset($where['estado']) && app::$_front) {
            $where['estado'] = true;
        }
        if (isset($where['idproductocategoria'])) {
            $idproductocategoria = $where['idproductocategoria'];
            unset($where['idproductocategoria']);
            if (isset($condiciones['limit'])) {
                $limit  = $condiciones['limit'];
                $limit2 = 0;
                unset($condiciones['limit']);
            }
            if (isset($condiciones['limit2'])) {
                if (!isset($limit)) {
                    $limit = 0;
                }

                $limit2 = $condiciones['limit2'];
                unset($condiciones['limit2']);
            }
        }

        if (!isset($condiciones['order'])) {
            $condiciones['order'] = 'orden ASC';
        }

        if (isset($condiciones['palabra'])) {
            $fields                = table::getByname(static::$table);
            $condiciones['buscar'] = array();
            if (isset($fields['titulo'])) {
                $condiciones['buscar']['titulo'] = $condiciones['palabra'];
            }

            if (isset($fields['keywords'])) {
                $condiciones['buscar']['keywords'] = $condiciones['palabra'];
            }

            if (isset($fields['descripcion'])) {
                $condiciones['buscar']['descripcion'] = $condiciones['palabra'];
            }

            if (isset($fields['metadescripcion'])) {
                $condiciones['buscar']['metadescripcion'] = $condiciones['palabra'];
            }

        }

        if ($select == 'total') {
            $return_total = true;
            if (isset($idproductocategoria)) {
                $select = '';
            }
        }
        $row = $connection->get(static::$table, static::$idname, $where, $condiciones, $select);
        foreach ($row as $key => $value) {
            if (isset($row[$key]['idproductocategoria'])) {
                $row[$key]['idproductocategoria'] = functions::decode_json($row[$key]['idproductocategoria']);
                if (isset($idproductocategoria) && !in_array($idproductocategoria, $row[$key]['idproductocategoria'])) {
                    unset($row[$key]);
                }
            }
            if (isset($row[$key]) && isset($row[$key]['foto'])) {
                $row[$key]['foto'] = functions::decode_json($row[$key]['foto']);
            }
            if (isset($row[$key]) && isset($row[$key]['archivo'])) {
                $row[$key]['archivo'] = functions::decode_json($row[$key]['archivo']);
            }
        }

        if (isset($idproductocategoria)) {
            $row = array_values($row);
        }

        if (isset($limit)) {
            if ($limit2 == 0) {
                $row = array_slice($row, $limit2, $limit);
            } else {
                $row = array_slice($row, $limit, $limit2);
            }
        }
        if (isset($return_total)) {
            return count($row);
        }

        $variables = array();
        if (isset($where['tipo'])) {
            $variables['tipo'] = $where['tipo'];
        }
        $cat        = productocategoria::getAll($variables);
        $categorias = array();
        foreach ($cat as $key => $c) {
            $categorias[$c[0]] = array('descuento' => $c['descuento'], 'descuento_fecha' => $c['descuento_fecha']);
        }

        foreach ($row as $key => $v) {
            if (isset($row[$key]['precio'])) {
                $row[$key]['precio_final'] = $row[$key]['precio'];
                $descuento                 = 0;
                if ($v['descuento'] != 0) {
                    $descuento = $v['descuento'];
                    $fechas    = $v['descuento_fecha'];
                } elseif (isset($categorias[$v['idproductocategoria'][0]]) && $categorias[$v['idproductocategoria'][0]]['descuento'] != 0) {
                    $descuento = $categorias[$v['idproductocategoria'][0]]['descuento'];
                    $fechas    = $categorias[$v['idproductocategoria'][0]]['descuento_fecha'];
                }

                if ($descuento > 0 && $descuento < 100) {
                    $fechas = explode(' - ', $fechas);
                    $fecha1 = strtotime(str_replace('/', '-', $fechas[0]));
                    $fecha2 = strtotime(str_replace('/', '-', $fechas[1]));
                    $now    = time();
                    if ($fecha1 < $now && $now < $fecha2) {
                        $precio_descuento = (($row[$key]['precio']) * $descuento) / 100;
                        $precio_final     = $row[$key]['precio'] - $precio_descuento;
                        if ($precio_final < 1) {
                            $precio_final = 1;
                        }

                        $row[$key]['precio_final'] = $precio_final;
                    }
                }
            }
        }
        return $row;
    }

    public static function getById($id)
    {
        $where = array(static::$idname => $id);
        if (app::$_front) {
            $fields = table::getByname(static::$table);
            if (isset($fields['estado'])) {
                $where['estado'] = true;
            }

        }
        $connection = database::instance();
        $row        = $connection->get(static::$table, static::$idname, $where);
        if (count($row) == 1) {
            $row[0]['idproductocategoria'] = functions::decode_json($row[0]['idproductocategoria']);
            if (isset($idproductocategoria) && !in_array($idproductocategoria, $row[0]['idproductocategoria'])) {
                unset($row[0]);
            }
            if (isset($row[0]) && isset($row[0]['foto'])) {
                $row[0]['foto'] = functions::decode_json($row[0]['foto']);
            }
            if (isset($row[0]) && isset($row[0]['archivo'])) {
                $row[0]['archivo'] = functions::decode_json($row[0]['archivo']);
            }

            if (isset($row[0]) && isset($row[0]['precio'])) {

                $cat        = productocategoria::getById($row[0]['idproductocategoria'][0]);
                $categorias = array();
                if (count($cat) > 0) {
                    $categorias[$cat[0]] = array('descuento' => $cat['descuento'], 'descuento_fecha' => $cat['descuento_fecha']);
                }

                $row[0]['precio_final'] = $row[0]['precio'];
                $descuento              = 0;
                if ($row[0]['descuento'] != 0) {
                    $descuento = $row[0]['descuento'];
                    $fechas    = $row[0]['descuento_fecha'];
                } elseif (isset($categorias[$row[0]['idproductocategoria'][0]]) && $categorias[$row[0]['idproductocategoria'][0]]['descuento'] != 0) {
                    $descuento = $categorias[$row[0]['idproductocategoria'][0]]['descuento'];
                    $fechas    = $categorias[$row[0]['idproductocategoria'][0]]['descuento_fecha'];
                }

                if ($descuento > 0 && $descuento < 100) {
                    $fechas = explode(' - ', $fechas);
                    $fecha1 = strtotime(str_replace('/', '-', $fechas[0]));
                    $fecha2 = strtotime(str_replace('/', '-', $fechas[1]));
                    $now    = time();
                    if ($fecha1 < $now && $now < $fecha2) {
                        $precio_descuento = (($row[0]['precio']) * $descuento) / 100;
                        $precio_final     = $row[0]['precio'] - $precio_descuento;
                        if ($precio_final < 1) {
                            $precio_final = 1;
                        }

                        $row[0]['precio_final'] = $precio_final;
                    }
                }
            }

        }
        return (count($row) == 1) ? $row[0] : $row;
    }

    public static function copy($id)
    {
        $row = static::getById($id);
        if (isset($row['foto'])) {
            unset($row['foto']);
        }
        if (isset($row['archivo'])) {
            unset($row['archivo']);
        }
        $row['idproductocategoria'] = functions::encode_json($row['idproductocategoria']);
        $fields                     = table::getByname(static::$table);
        $insert                     = database::create_data($fields, $row);
        $connection                 = database::instance();
        $row                        = $connection->insert(static::$table, static::$idname, $insert);
        if ($row) {
            $last_id = $connection->get_last_insert_id();
            log::insert_log(static::$table, static::$idname, __FUNCTION__, $insert);
            return $last_id;
        } else {
            return $row;
        }
    }

}