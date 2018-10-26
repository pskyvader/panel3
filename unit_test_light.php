<?php

class unit_test_light
{
    private static $log = array();
    public static function start($message = 'START')
    {
        self::$log[] = array('message' => $message, 'time' => microtime(true), 'memory' => memory_get_usage(true), 'start' => true);
    }
    public static function add($message)
    {
        self::$log[] = array('message' => $message, 'time' => microtime(true), 'memory' => memory_get_usage(true));
    }
    public static function end($return_array = false)
    {
        $return = array();
        $time_start = self::$log[0]['time'];
        $time_block_start = self::$log[0]['time'];
        $memory_start = self::$log[0]['memory'];
        $memory_block_start = self::$log[0]['memory'];
        foreach (self::$log as $key => $l) {
            if (isset($l['start'])) {
                $time_block_start = $l['time'];
                $memory_block_start = $l['memory'];
            }
            $time_total = ($l['time'] - $time_start);
            $time_block = ($l['time'] - $time_block_start);
            $time = ($key > 0) ? ($l['time'] - self::$log[$key - 1]['time']) : 0;
            $memory_total = ($l['memory'] - $memory_start);
            $memory_block = ($l['memory'] - $memory_block_start);
            $memory = ($key > 0) ? ($l['memory'] - self::$log[$key - 1]['memory']) : 0;

            $return[] = array(
                'message' => $l['message'],
                'time' => (int)($time*1000).'ms',
                'time_block' => (int)($time_block*1000).'ms',
                'time_total' => (int)($time_total*1000).'ms',
                'memory' => self::convert($memory),
                'memory_block' => self::convert($memory_block),
                'memory_total' => self::convert($memory_total),
            );
        }
        if ($return_array) {
            return $return;
        } else {
            $string = "";
            foreach ($return as $key => $r) {
                $string = "{";
                foreach ($r as $k => $v) {
                    $string .= $k . ": " . $v;
                }
                $string .= "}\n";
            }
            return $string;
        }
    }
    private static function convert($size)
    {
        $sign=($size>=0)?'':'-';
        $size=abs($size);
        $unit=array('b','kb','mb','gb','tb','pb');
        $final_size=@round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
        return $sign.$final_size;
    }
}
