<?php

class Command
{
    const KILL = 'kill';
    const MOVE = 'move';
    const SHEEP = 'sheep';
    const YARD = 'yard';
    
    protected static $commands = ['kill', 'move'];
    
    protected $command;
    protected $sheep;
    protected $yard;
    
    public static function parseCommandToRequest($string, $lang = 'en')
    {
        $string = mb_strtolower($string, 'UTF-8');
        
        // чтобы команды работали на нескольких языках, создадим словари и здесь будем делать перевод
        // загрузим язык
        $path = ROOT_DIR . '/farm/lang/';
        $langData = $path . 'en_commands.php';
        if (file_exists($path . $lang . '_commands.php')) {
            $langPath = $path . $lang . '_commands.php';
        } else {
            $langPath = $path . 'en_commands.php';
        }
        if (!file_exists($langPath)) {
            throw new \Exception("Language data not exists for '{$lang}'");
        }
        $langData = require_once $langPath;

        // Переведём строку в английский язык
        $from = array_values($langData);
        $to = array_keys($langData);
        $string = str_replace($from, $to, $string);

        // TODO: парсим строку на предмет команды и обьектов для команды
        // TODO: на основе этих данных заполняем $_REQUEST
        $pattern = "#[\w]+#u";
        if (preg_match_all($pattern, $string, $m)) {
            $command = null;
            $m = $m[0];
            foreach ($m as $key => $match) {
                if (in_array($match, self::$commands)) {
                    $command = $match;
                    unset($m[$key]);
                }
            }


            if ($command == self::KILL) {
                if (count($m) !== 1) {
                    throw new \Exception("Only one arg allowed for 'kill' command");
                }
                $arg = array_pop($m);
                if (strpos($arg, self::SHEEP) === false ) {
                    throw new \Exception("Only sheep arg allowed for 'kill' command");
                }
                if ($arg == self::SHEEP) {
                    throw new \Exception("Wrong sheep ID given.");
                }
                
                preg_match("#sheep(\d+)#", $arg, $m);
                $sheepId = $m[1];
                
                $_REQUEST[$command] = ['sheeps' => [$sheepId]];
                
            } elseif ($command == self::MOVE) {
                if (count($m) !== 2) {
                    throw new \Exception("Only two args allowed for 'move' command");
                }
                
                $sheepId = null;
                $yardId = null;
                foreach ($m as $arg) {
                    if (preg_match("#sheep(\d+)#", $arg, $m)) {
                        $sheepId = $m[1];
                    } elseif (preg_match("#yard(\d+)#", $arg, $m)) {
                        $yardId = $m[1];
                    }
                }
                if (is_null($sheepId) || is_null($yardId)) {
                    throw new \Exception("Both args 'sheep' and 'yard' must be set for 'move' command");
                }

                $_REQUEST[$command] = ['sheeps' => [$sheepId], 'yard' => $yardId];
            }
        }
    }
} 