<?php
// application/models/Subcategory.php
namespace application\models;

use ItForFree\SimpleMVC\MVC\Model;

/**
 * Класс для обработки подкатегорий
 */
class Subcategory extends Model
{
    public $name = null;
    public $categoryId = null;
    public $createdAt = null;
    public $categoryName = null;
    
    public static function getTableName(): string
    {
        return 'subcategories';
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
        
        if (isset($data['category_id'])) {
            $this->categoryId = (int) $data['category_id'];
        }
        if (isset($data['categoryId'])) {
            $this->categoryId = (int) $data['categoryId'];
        }
        
        if (isset($data['created_at'])) {
            $this->createdAt = $data['created_at'];
        }
        if (isset($data['category_name'])) {
            $this->categoryName = $data['category_name'];
        }
    }
    
    /**
     * Получить подкатегорию по ID
     */
    public function getById(int $id, string $tableName = ''): ?self
    {
        $sql = "SELECT s.*, c.name as category_name 
                FROM subcategories s 
                LEFT JOIN categories c ON s.category_id = c.id 
                WHERE s.id = :id
                LIMIT 1";
        
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":id", $id, \PDO::PARAM_INT);
        $st->execute();
        
        if ($row = $st->fetch()) {
            return new self($row);
        }
        
        return null;
    }
}