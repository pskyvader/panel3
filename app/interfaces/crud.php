<?php
namespace app\interfaces;
defined("APPPATH") OR die("Acceso denegado");
 
interface crud
{
    public static function getAll($where=array(),$condiciones=array());
    public static function getById($id);
    public static function insert($data);
    public static function update($data);
    public static function delete($id);
}