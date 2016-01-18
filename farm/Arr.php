<?php


class Arr
{
    /**
     * Получает значение из массива $array по ключу $key. Если такого ключа не существует, 
     * возвращает $default
     * @param mixed $key
     * @param array $array
     * @param null $default
     * @return mixed
     */
    public static function get($key, $array, $default = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
} 