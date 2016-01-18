<?php

define('ROOT_DIR', __DIR__);

$config = include_once __DIR__ . '/farm/config.php';

require_once __DIR__ . '/farm/Arr.php';
require_once __DIR__ . '/farm/FarmControl.php';
require_once __DIR__ . '/farm/Command.php';


session_start();


// После запуска сессии, установим необходимый язык для переводов
$allowedLanguages = scandir(__DIR__ . '/farm/lang/');
$allowedLanguages = array_filter($allowedLanguages, function(&$item) {
        if (in_array($item, ['.', '..'])) {
            return false;
        }
        $item = str_replace('.php', '', $item);
        return true;
    });

// Получим текущий язык для отображения. Приоритет: $_GET, $_SESSION, $config.
$lang = Arr::get('lang', $_SESSION, $config['lang']);
if (in_array($lang = Arr::get('lang', $_GET), $allowedLanguages)) {
    $_SESSION['lang'] = $lang;
}

// загрузим языки и пропарсим шаблон:
$path = __DIR__ . '/farm/lang/';
if (file_exists($path . $lang . '.php')) {
    $langPath = $path . $lang . '.php';
} else {
    $langPath = $path . 'en.php';
}
$langData = require_once $langPath;


// Фермер занимается разведением овечек.
// На ферме – 4 загона, в которых содержаться овечки. Овечки размножаются каким-то случайным образом 
//   (полностью на ваше усмотрение, например: одна овечка за каждые 10 секунд, а для отсчета можно использовать 
//   время последнего обновления страницы), но есть одно правило: если в загоне остается одна овца, 
//   то размножаться она не может.
// Любых овечек в загонах можно выбрать и пустить на мясо. Количество зарубленных овец должно подсчитываться.
// Все данные, кроме конфигурационных, хранить в базе данных. Тип базы данных не важен.
// Программа должна иметь интерфейс для управления и контроля поголовья мелкого рогатого скота. 

// Дизайн страницы не является основной целью задания, но удобство – это конечно хорошо.  : )
// При работе над заданием постарайтесь предусмотреть следующее:
//   - Если захочется перенести базу на другой сервер баз данных? Как сделать так, чтобы это можно было осуществить с наименьшими изменениями в коде?
//   - Страница должна обновляться автоматически, например, каждые 30 секунд. Но также, нужно предусмотреть 
//        и дополнительную кнопку для обновления страницы вручную.
// Дополнительные задачи (не обязательные, но будут большим плюсом):
//    - Реализовать поддержку локализации (ну скажем русский и английский языки)
//    - Реализовать возможность выполнения 1-2х команд (к примеру «Овечка1 переместить Загон4», “Убить Овечка1”).
//    - Обновление содержимого загонов без обновления всей страницы (AJAX).


// id - номер овцы
// yard_id - загона для овец
// alive - живая или нет
// сtime - время рождения овцы
// atime - время последнего изменения овцы
/*
CREATE TABLE public.funny_farm (
  id serial PRIMARY KEY NOT NULL,
  yard_id integer NOT NULL,
  alive boolean NOT NULL DEFAULT true,
  ctime timestamp without time zone NOT NULL DEFAULT now(),
  atime timestamp without time zone NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX unique_id ON funny_farm USING BTREE (id);

INSERT INTO public.funny_farm (yard_id, alive, ctime, atime) VALUES (1, TRUE, NOW(), NOW());
INSERT INTO public.funny_farm (yard_id, alive, ctime, atime) VALUES (1, TRUE, NOW(), NOW());
*/

try {
    $control = new \FarmControl($config);

    // получить всех живых овец из БД
    $yards = $control->getYardsSheepList();

    // УМНОЖЕНИЕ!
    // Для каждого загона интервалы считаем отдельно
    $time = time();
    if (!array_key_exists('last_multiple', $_SESSION)) {
        $_SESSION['last_multiple'] = [1 => $time, 2 => $time, 3 => $time, 4 => $time];
    } else {
        
        // производим увеличение поголовья МРС
        foreach ($yards as $id => $yard) {
            $timeDiff = $time - $_SESSION['last_multiple'][$id];
            if ($timeDiff >= $config['period']) {
                // получим кратность увеличения (может прошло больше одного периода умножения)
                $steps = (integer) floor($timeDiff / $config['period']);
                if (count($yard) > 1) {
                    $control->createSheepInYard($id, $steps);
                }
                // если овечек нет или только одна - значит обнуляем период размножения в любом случае
                $_SESSION['last_multiple'][$id] = $time;
            }
        }
    }


    // если пришла команда - заполним $_REQUEST данными из команды
    if (array_key_exists('command', $_REQUEST)) {
        Command::parseCommandToRequest($_REQUEST['command'], $lang);
    }

    // УНИЧТОЖЕНИЕ!
    if (array_key_exists('kill', $_REQUEST)) {
        $sheeps = Arr::get('sheeps', $_REQUEST['kill'], []);
        $control->killSheep($sheeps);
    }
    
    // ПЕРЕМЕЩЕНИЕ!
    if (array_key_exists('move', $_REQUEST)) {
        $yardId = Arr::get('yard', $_REQUEST['move']);
        $sheeps = Arr::get('sheeps', $_REQUEST['move'], []);
        
        $control->moveSheepToYard($sheeps, $yardId);
    }

    // получить индекс кровожадности
    $bloodyIndex = $control->getBloodyIndex();
    // получить всех живых овец из БД
    $yards = $control->getYardsSheepList();

} catch (\Exception $e) {
    if ($e->getCode() == 100) {
        $error = $e->getMessage();
    } else {
        echo $e->getMessage();
        exit;
    }
}



if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // AJAX
    echo json_encode($yards, JSON_UNESCAPED_UNICODE);
    exit;
} else {

    // ВЫВОД ШАБЛОНА
    ob_start();
    include __DIR__ . "/farm/farm_template.php";
    $tpl = ob_get_clean();
    
    if (preg_match_all('/\[\[([^\]]+)\]\]/', $tpl, $m)) {
        foreach ($m[1] as &$item) {
            if (isset($langData[$item])) {
                $item = $langData[$item];
            }
        }
        unset($item);
        
        $tpl = str_replace($m[0], $m[1], $tpl);
    }
    
    echo $tpl;
}