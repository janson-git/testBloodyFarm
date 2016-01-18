<?php

require_once __DIR__ . '/Arr.php';


class FarmControl
{
    /** @var  \PDO */
    protected $pdo;
    
    protected $tableName = 'public.funny_farm';
    
    public function __construct($config)
    {
        $db = Arr::get('db', $config);
        if (empty($db)) {
            throw new \Exception("Database configuration not defined.");
        }

        $dbName = Arr::get('dbname', $db);
        $dbHost = Arr::get('host', $db);
        $dbUser = Arr::get('username', $db);
        $dbPass = Arr::get('password', $db);
        
        if (empty($dbName) || empty($dbHost) || empty($dbUser)) {
            throw new \Exception("Database connection configuration are missed.");
        }
        
        $this->pdo = new \PDO("pgsql:dbname={$dbName};host={$dbHost}", $dbUser, $dbPass);
    }

    /**
     * Возвращает ID овец, сгруппированых по загонам. Ключи первого уровня - ID загонов.
     * @return array
     */
    public function getYardsSheepList()
    {
        $sheeps = $this->pdo->query("SELECT id, yard_id FROM {$this->tableName} WHERE alive = TRUE")
            ->fetchAll(\PDO::FETCH_ASSOC);

        $yards = [];
        foreach ($sheeps as $sheep) {
            $yardId = $sheep['yard_id'];
            if (!array_key_exists($yardId, $yards)) {
                $yards[$yardId] = [];
            }
            array_push($yards[$yardId], $sheep['id']);
        }
        
        return $yards;
    }


    /**
     * @param integer $yardId ID загона в котором нужно создать овец
     * @param integer $count количество овец
     */
    public function createSheepInYard($yardId, $count)
    {
        $sql = "INSERT INTO {$this->tableName} (yard_id) VALUES ";
        $vals = [];
        for ($i = 0; $i < $count; $i++) {
            $vals[] = " ({$yardId}) ";
        }
        $sql .= implode(',', $vals);
        // добавим в БД
        $this->pdo->query($sql);
    }


    /**
     * @param array $sheepIdList Массив ID овец, которых нужно уничтожить
     */
    public function killSheep(Array $sheepIdList)
    {
        if (!empty($sheepIdList)) {
            // только integer должны быть в $sheeps
            $sheeps = array_map(function($item) {
                    return (integer) $item;
                }, $sheepIdList);
            $sheeps = array_filter($sheeps, function($item) {
                    return is_numeric($item);
                });
            $statement = $this->pdo->prepare("UPDATE {$this->tableName} SET alive = FALSE, atime = now() WHERE id IN (" . implode(',', $sheeps) . ")" );
            $statement->execute();
        }
    }

    /**
     * Перемещает массив овец в указаный $yardId загон
     * @param array $sheep
     * @param integer $yardId
     */
    public function moveSheepToYard(Array $sheep, $yardId)
    {
        if (!is_null($yardId) && !empty($sheep)) {
            // только integer должны быть в $sheeps
            $sheep = array_map(function($item) {
                    return (integer) $item;
                }, $sheep);
            $sheep = array_filter($sheep, function($item) {
                    return is_numeric($item);
                });
            $statement = $this->pdo->prepare("UPDATE {$this->tableName} SET yard_id = :yard_id, atime = now() WHERE id IN (" . implode(',', $sheep) . ")" );
            $statement->bindParam('yard_id', $yardId);
            $statement->execute();
        }
    }

    /**
     * Возвращает текущий уровень кровожадности
     * @return integer|false
     */
    public function getBloodyIndex()
    {
        return $this->pdo->query("SELECT count(*) FROM {$this->tableName} WHERE alive = FALSE")->fetch(\PDO::FETCH_COLUMN);
    }
} 