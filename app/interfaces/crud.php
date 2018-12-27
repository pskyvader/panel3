<?php
namespace app\interfaces;

defined("APPPATH") or die("Acceso denegado");

interface crud
{
    public static function getAll(array $where = array(), array $condiciones = array(), string $select);
    public static function getById(int $id);
    public static function insert(array $data, bool $log);
    public static function update(array $data, bool $log);
    public static function delete(int $id);
}
