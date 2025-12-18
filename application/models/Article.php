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
                'id' => $row['id'],
                'login' => $row['login']
            );
        }
    }

    /**
     * Сохраняет авторов статьи из свойства $authors
     */
    public function saveAuthors(): void
    {
        if (is_null($this->id)) {
            return;
        }

        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Сохраняет статью (автоматически выбирает insert или update)
     */
    public function save(): void
    {
        if (empty($this->id)) {
            // Новая статья - используем insert
            $this->insert();
        } else {
            // Существующая статья - используем update
            $this->update();
        }
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
    public function getAuthorIdsForArticle($articleId): array
    {
        $sql = "SELECT user_id FROM article_authors WHERE article_id = :articleId";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":articleId", $articleId, \PDO::PARAM_INT);
        $st->execute();

        $authorIds = array();
        while ($row = $st->fetch()) {
            $authorIds[] = $row['user_id'];
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

    /**
     * Вставка новой статьи в БД
     */
    public function insert(): void
    {
        // Преобразуем дату в правильный формат
        if (!empty($this->publicationDate)) {
            // Если это Unix timestamp (число)
            if (is_numeric($this->publicationDate)) {
                $publicationDate = date('Y-m-d', (int)$this->publicationDate);
            }
            // Если это строка даты
            else {
                $publicationDate = date('Y-m-d', strtotime($this->publicationDate));
            }
        } else {
            $publicationDate = date('Y-m-d');
        }

        $sql = "INSERT INTO articles (publicationDate, title, categoryId, subcategoryId, summary, content, active) 
        VALUES (:publicationDate, :title, :categoryId, :subcategoryId, :summary, :content, :active)";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":publicationDate", $publicationDate, \PDO::PARAM_STR);
        $st->bindValue(":title", $this->title, \PDO::PARAM_STR);
        $st->bindValue(":categoryId", $this->categoryId, \PDO::PARAM_INT);
        $st->bindValue(":subcategoryId", $this->subcategoryId, \PDO::PARAM_INT);
        $st->bindValue(":summary", $this->summary, \PDO::PARAM_STR);
        $st->bindValue(":content", $this->content, \PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, \PDO::PARAM_INT);

        $st->execute();
        $this->id = $this->pdo->lastInsertId();

        // Сохраняем авторов после получения ID
        $this->saveAuthors();
    }

    /**
     * Обновление статьи в БД
     */
    public function update(): void
    {
        if (empty($this->id)) {
            throw new \Exception("Article::update(): Attempt to update an Article without ID.");
        }

        try {
            // Преобразуем дату в правильный формат
            if (!empty($this->publicationDate)) {
                // Если это Unix timestamp (число)
                if (is_numeric($this->publicationDate)) {
                    $publicationDate = date('Y-m-d', (int)$this->publicationDate);
                }
                // Если это строка даты в формате 'Y-m-d\TH:i'
                elseif (strpos($this->publicationDate, 'T') !== false) {
                    $publicationDate = date('Y-m-d', strtotime($this->publicationDate));
                }
                // Если это уже правильный формат
                else {
                    $publicationDate = $this->publicationDate;
                }
            } else {
                $publicationDate = date('Y-m-d');
            }

            $sql = "UPDATE articles SET 
                publicationDate = :publicationDate,
                title = :title,
                categoryId = :categoryId,
                subcategoryId = :subcategoryId,
                summary = :summary,
                content = :content,
                active = :active
                WHERE id = :id";

            $st = $this->pdo->prepare($sql);

            $summary = $this->summary ?? '';
            $active = $this->active ?? 1;
            $subcategoryId = $this->subcategoryId !== null ? $this->subcategoryId : null;

            $st->bindValue(":publicationDate", $publicationDate, \PDO::PARAM_STR);
            $st->bindValue(":title", $this->title, \PDO::PARAM_STR);
            $st->bindValue(":categoryId", $this->categoryId, \PDO::PARAM_INT);

            if ($subcategoryId === null) {
                $st->bindValue(":subcategoryId", null, \PDO::PARAM_NULL);
            } else {
                $st->bindValue(":subcategoryId", $subcategoryId, \PDO::PARAM_INT);
            }

            $st->bindValue(":summary", $summary, \PDO::PARAM_STR);
            $st->bindValue(":content", $this->content, \PDO::PARAM_STR);
            $st->bindValue(":active", $active, \PDO::PARAM_INT);
            $st->bindValue(":id", $this->id, \PDO::PARAM_INT);

            $st->execute();
            
            // Сохраняем авторов
            $this->saveAuthors();
        } catch (\PDOException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Загружает данные из массива в свойства объекта
     */
    public function loadFromArray(array $data): self
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }

        if (isset($data['title'])) {
            $this->title = $data['title'];
        }

        if (isset($data['content'])) {
            $this->content = $data['content'];
            // Если summary нет, создаем его из content
            if (!isset($data['summary']) && empty($this->summary)) {
                $this->summary = mb_substr($data['content'], 0, 200) . '...';
            }
        }

        if (isset($data['publicationDate'])) {
            $this->publicationDate = $data['publicationDate'];
        }

        if (isset($data['categoryId'])) {
            $this->categoryId = (int) $data['categoryId'];
        }

        if (isset($data['subcategoryId'])) {
            $this->subcategoryId = $data['subcategoryId'] !== null ? (int) $data['subcategoryId'] : null;
        }

        if (isset($data['active'])) {
            $this->active = (int) $data['active'];
        }

        if (isset($data['summary'])) {
            $this->summary = $data['summary'];
        }

        if (isset($data['authors'])) {
            $this->authors = is_array($data['authors']) ? $data['authors'] : [$data['authors']];
        }

        return $this;
    }
}