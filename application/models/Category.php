<?php
// application/models/Category.php
namespace application\models;

use ItForFree\SimpleMVC\MVC\Model;

/**
 * Класс для обработки категорий статей
 */
class Category extends Model
{
    public $name = null;
    public $description = null;
    
    public static function getTableName(): string
    {
        return 'categories';
    }
    
    /**
     * Конструктор
     */
    public function __construct($data = array())
    {
        parent::__construct();
        
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
    }
    
    /**
     * Получить категорию по ID
     */
    public function getById(int $id, string $tableName = ''): ?self
    {
        $sql = "SELECT * FROM categories WHERE id = :id LIMIT 1";
        
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":id", $id, \PDO::PARAM_INT);
        $st->execute();
        
        if ($row = $st->fetch()) {
            return new self($row);
        }
        
        return null;
    }
    
    /**
     * Получить список всех категорий
     */
    public static function getAll($order = "name ASC")
    {
        return self::getList(1000000, $order)['results'];
    }
}