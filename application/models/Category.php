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

    /**
     * Получить список категорий
     */
    public function getList(int $numRows = 1000000, string $order = "name ASC"): array
    {
        if (empty($order)) {
            $order = "name ASC"; // значение по умолчанию
        }

        $sql = "SELECT * FROM categories ORDER BY $order LIMIT :numRows";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":numRows", $numRows, \PDO::PARAM_INT);
        $st->execute();

        $list = array();
        while ($row = $st->fetch()) {
            $category = new self($row);
            $list[] = $category;
        }

        $sql = "SELECT COUNT(*) AS totalRows FROM categories";
        $totalRows = $this->pdo->query($sql)->fetch();

        return array(
            "results" => $list,
            "totalRows" => $totalRows[0]
        );
    }
}
