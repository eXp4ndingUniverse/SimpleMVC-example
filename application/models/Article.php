<?php

namespace application\models;

use ItForFree\SimpleMVC\MVC\Model;

/**
 * Класс для обработки статей
 */
class Article extends Model
{
    public $publicationDate = null;
    public $title = null;
    public $categoryId = null;
    public $subcategoryId = null;
    public $summary = null;
    public $content = null;
    public $active = 1;
    public $authors = array();
    public $firstchars = null;
    public $categoryName = null;
    public $subcategoryName = null;

    public static function getTableName(): string
    {
        return 'articles';
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

        if (isset($data['publicationDate'])) {
            $this->publicationDate = (string) $data['publicationDate'];
        }

        if (isset($data['title'])) {
            $this->title = $data['title'];
        }

        if (isset($data['categoryId'])) {
            $this->categoryId = (int) $data['categoryId'];
        } elseif (isset($data['category_id'])) {
            $this->categoryId = (int) $data['category_id'];
        }

        if (isset($data['subcategoryId'])) {
            $this->subcategoryId = $data['subcategoryId'] !== null ? (int) $data['subcategoryId'] : null;
        } elseif (isset($data['subcategory_id'])) {
            $this->subcategoryId = $data['subcategory_id'] !== null ? (int) $data['subcategory_id'] : null;
        }

        if (isset($data['summary'])) {
            $this->summary = $data['summary'];
        }

        if (isset($data['content'])) {
            $this->content = $data['content'];
            $this->firstchars = mb_strimwidth($data['content'], 0, 50) . "...";
        }

        $this->active = isset($data['active']) ? (int)$data['active'] : 1;

        if (isset($data['categoryName'])) {
            $this->categoryName = $data['categoryName'];
        }

        if (isset($data['subcategoryName'])) {
            $this->subcategoryName = $data['subcategoryName'];
        }

        // Инициализируем массив авторов
        $this->authors = array();
    }

    /**
     * Получить список статей с авторами и категориями
     */
    public static function getListWithDetails(
        $numRows = 1000000,
        $categoryId = null,
        $subcategoryId = null,
        $order = "publicationDate DESC",
        $onlyActive = true
    ) {
        // Создаем экземпляр для доступа к $pdo
        $instance = new static();
        $pdo = $instance->pdo;

        // Формируем условия WHERE
        $whereConditions = array();
        $params = array();

        if ($categoryId) {
            $whereConditions[] = "a.categoryId = :categoryId";
            $params[':categoryId'] = $categoryId;
        }
        if ($subcategoryId) {
            $whereConditions[] = "a.subcategoryId = :subcategoryId";
            $params[':subcategoryId'] = $subcategoryId;
        }
        if ($onlyActive) {
            $whereConditions[] = "a.active = 1";
        }

        $whereClause = "";
        if (!empty($whereConditions)) {
            $whereClause = "WHERE " . implode(" AND ", $whereConditions);
        }

        $sql = "SELECT a.*, 
                       UNIX_TIMESTAMP(a.publicationDate) AS publicationDate,
                       c.name as categoryName, 
                       sc.name as subcategoryName
                FROM articles a
                LEFT JOIN categories c ON a.categoryId = c.id
                LEFT JOIN subcategories sc ON a.subcategoryId = sc.id
                $whereClause
                ORDER BY $order 
                LIMIT :numRows";

        $st = $pdo->prepare($sql);
        $st->bindValue(":numRows", $numRows, \PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }

        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $article = new static($row);
            $article->loadAuthors();
            $list[] = $article;
        }

        // Получаем общее количество статей
        $sql = "SELECT COUNT(*) AS totalRows 
                FROM articles a
                $whereClause";

        $st = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $st->execute();
        $totalRows = $st->fetch();

        return array(
            "results" => $list,
            "totalRows" => $totalRows[0]
        );
    }

    public function loadAuthors(): void
    {
        if (is_null($this->id)) return;

        $sql = "SELECT u.id, u.login 
            FROM users u 
            INNER JOIN article_authors aa ON u.id = aa.user_id 
            WHERE aa.article_id = :articleId
            ORDER BY u.login";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":articleId", $this->id, \PDO::PARAM_INT);
        $st->execute();

        $this->authors = array();
        while ($row = $st->fetch()) {
            $this->authors[] = array(
                'id' => $row['id'],        // Важно: должен быть ID
                'login' => $row['login']   // и логин
            );
        }
    }

    /**
     * Сохраняет авторов статьи из свойства $authors
     */
    public function saveAuthors(): void
    {
        if (is_null($this->id)) return;

        // Удаляем старых авторов
        $sql = "DELETE FROM article_authors WHERE article_id = :articleId";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":articleId", $this->id, \PDO::PARAM_INT);
        $st->execute();

        // Добавляем новых авторов
        if (!empty($this->authors) && is_array($this->authors)) {
            $sql = "INSERT INTO article_authors (article_id, user_id) VALUES ";
            $placeholders = array();
            $values = array();

            foreach ($this->authors as $authorId) {
                $placeholders[] = "(?, ?)";
                $values[] = $this->id;
                $values[] = (int)$authorId;
            }

            $sql .= implode(", ", $placeholders);
            $st = $this->pdo->prepare($sql);
            $st->execute($values);
        }
    }

    /**
     * Переопределяем метод save для сохранения авторов
     */
    public function save(): void
    {
        parent::save();
        $this->saveAuthors();
    }

    /**
     * Переопределяем метод delete для удаления связей с авторами
     */
    public function delete(): void
    {
        if (is_null($this->id)) {
            trigger_error("Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR);
        }

        // Сначала удаляем связи с авторами
        $st = $this->pdo->prepare("DELETE FROM article_authors WHERE article_id = :id");
        $st->bindValue(":id", $this->id, \PDO::PARAM_INT);
        $st->execute();

        // Затем удаляем саму статью
        parent::delete();
    }

    /**
     * Получить ID авторов статьи
     */
    public function getAuthorIds(): array
    {
        $authorIds = array();
        foreach ($this->authors as $author) {
            if (is_array($author) && isset($author['id'])) {
                $authorIds[] = $author['id'];
            } elseif (is_numeric($author)) {
                $authorIds[] = (int)$author;
            }
        }
        return $authorIds;
    }

    /**
     * Переопределяем метод getById для корректной работы
     */
    public function getById(int $id, string $tableName = ''): ?self
    {
        $sql = "SELECT a.*, 
                   UNIX_TIMESTAMP(a.publicationDate) AS publicationDate,
                   c.name as categoryName, 
                   sc.name as subcategoryName
            FROM articles a
            LEFT JOIN categories c ON a.categoryId = c.id
            LEFT JOIN subcategories sc ON a.subcategoryId = sc.id
            WHERE a.id = :id";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":id", $id, \PDO::PARAM_INT);
        $st->execute();

        if ($row = $st->fetch()) {
            $article = new self($row);
            $article->loadAuthors();
            return $article;
        }

        return null;
    }
}
