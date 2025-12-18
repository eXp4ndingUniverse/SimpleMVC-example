<?php

namespace application\models;

use ItForFree\SimpleMVC\MVC\Model;

/**
 * Класс для обработки пользователей (расширенный с функционалом из my-first-cms)
 */
class UserModel extends Model
{
    // Свойства
    public $login = null;
    public ?int $id = null;
    public $pass = null;
    public $role = null;
    public $email = null;
    public $timestamp = null;
    public string $orderBy = "login ASC";
    public string $tableName = 'users';
    public $salt = null;

    // Новые свойства из my-first-cms
    public $active = 1;
    public $created_at = null;

    /**
     * Конструктор с поддержкой данных из my-first-cms
     */
    public function __construct($data = array())
    {
        parent::__construct();

        if (isset($data['id'])) {
            $this->id = (int)$data['id'];
        }
        if (isset($data['login'])) {
            $this->login = $data['login'];
        }
        if (isset($data['password'])) {
            $this->pass = $data['password'];
        } elseif (isset($data['pass'])) {
            $this->pass = $data['pass'];
        }
        if (isset($data['role'])) {
            $this->role = $data['role'];
        }
        if (isset($data['email'])) {
            $this->email = $data['email'];
        }
        if (isset($data['timestamp'])) {
            $this->timestamp = $data['timestamp'];
        }
        if (isset($data['active'])) {
            $this->active = (int)$data['active'];
        }
        if (isset($data['created_at'])) {
            $this->created_at = $data['created_at'];
        }
        if (isset($data['salt'])) {
            $this->salt = $data['salt'];
        }
    }

    /**
     * Устанавливаем свойства с помощью значений формы редактирования
     * (из my-first-cms)
     */
    public function storeFormValues($params)
    {
        $this->__construct($params);

        // Хешируем пароль если он был изменен (из my-first-cms)
        if (isset($params['password']) && !empty($params['password'])) {
            $this->pass = password_hash($params['password'], PASSWORD_DEFAULT);
        }
    }

    public function insert()
    {
        $sql = "INSERT INTO $this->tableName (login, password, role, email, active, created_at) 
                VALUES (:login, :password, :role, :email, :active, :created_at)";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":login", $this->login, \PDO::PARAM_STR);

        // Используем существующий пароль или генерируем новый
        if (empty($this->pass)) {
            $this->pass = password_hash($this->salt . 'default', PASSWORD_BCRYPT);
        } elseif (!password_get_info($this->pass)['algo']) {
            // Если пароль не хеширован, хешируем его
            $this->pass = password_hash($this->pass, PASSWORD_DEFAULT);
        }

        $st->bindValue(":password", $this->pass, \PDO::PARAM_STR);
        $st->bindValue(":role", $this->role ?? 'user', \PDO::PARAM_STR);
        $st->bindValue(":email", $this->email ?? '', \PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, \PDO::PARAM_INT);
        $st->bindValue(":created_at", (new \DateTime('NOW'))->format('Y-m-d H:i:s'), \PDO::PARAM_STMT);

        $st->execute();
        $this->id = $this->pdo->lastInsertId();
    }

    public function update()
    {
        if (empty($this->id)) {
            throw new \Exception("UserModel::update(): Attempt to update a user without ID.");
        }

        // Определяем, менялся ли пароль
        if (!empty($this->pass) && !password_get_info($this->pass)['algo']) {
            // Пароль новый, нужно его хешировать
            $sql = "UPDATE $this->tableName SET login=:login, password=:password, role=:role, email=:email, active=:active WHERE id = :id";
            $st = $this->pdo->prepare($sql);

            // Хешируем пароль (без соли)
            $hashedPass = password_hash($this->pass, PASSWORD_DEFAULT);

            $st->bindValue(":password", $hashedPass, \PDO::PARAM_STR);
        } else {
            // Пароль не менялся или уже хеширован
            $sql = "UPDATE $this->tableName SET login=:login, role=:role, email=:email, active=:active WHERE id = :id";
            $st = $this->pdo->prepare($sql);
        }

        $st->bindValue(":login", $this->login, \PDO::PARAM_STR);
        $st->bindValue(":role", $this->role ?? 'user', \PDO::PARAM_STR);
        $st->bindValue(":email", $this->email ?? '', \PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, \PDO::PARAM_INT);
        $st->bindValue(":id", $this->id, \PDO::PARAM_INT);
        $st->execute();
    }

    /**
     * Вернёт id пользователя
     */
    public function getId()
    {
        if ($this->login !== 'guest') {
            $sql = "SELECT id FROM users where login = :userName";
            $st = $this->pdo->prepare($sql);
            $st->bindValue(":userName", $this->login, \PDO::PARAM_STR);
            $st->execute();
            $row = $st->fetch();
            return $row['id'] ?? null;
        } else {
            return null;
        }
    }

    /**
     * Проверка логина и пароля пользователя.
     */
    public function getAuthData($login): ?array
    {
        // Если колонки salt нет, получаем только пароль
        $sql = "SELECT password as pass, id, active FROM users WHERE login = :login";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":login", $login, \PDO::PARAM_STR);
        $st->execute();
        $authData = $st->fetch();

        // Добавляем пустой salt для совместимости
        if ($authData) {
            $authData['salt'] = '';
        }

        return $authData ? $authData : null;
    }

    /**
     * Проверяем роль пользователя.
     */
    public function getRole($login): array
    {
        $sql = "SELECT role FROM users WHERE login = :login";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":login", $login, \PDO::PARAM_STR);
        $st->execute();
        return $st->fetch() ?: ['role' => 'guest'];
    }

    /**
     * Получить всех пользователей (совместимость со старым кодом)
     */
    public function getAllUsers(): array
    {
        return $this->getList()['results'];
    }

    /**
     * ============ МЕТОДЫ ИЗ my-first-cms ============
     */

    /**
     * Получить пользователя по ID
     */
    public function getById(int $id, string $tableName = ''): ?self
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":id", $id, \PDO::PARAM_INT);
        $st->execute();

        if ($row = $st->fetch()) {
            return new self($row);
        }

        return null;
    }

    /**
     * Получить пользователя по логину (из my-first-cms)
     */
    public static function getByLogin($login)
    {
        $model = new self();
        $sql = "SELECT * FROM users WHERE login = :login";
        $st = $model->pdo->prepare($sql);
        $st->bindValue(":login", $login, \PDO::PARAM_STR);
        $st->execute();
        $row = $st->fetch();

        if ($row) {
            return new self($row);
        }
        return null;
    }

    /**
     * Получить список всех пользователей
     */
    public function getList(int $numRows = 1000000, string $order = "login ASC"): array
    {
        if (empty($order)) {
            $order = "login ASC";
        }

        $sql = "SELECT * FROM users ORDER BY $order LIMIT :numRows";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":numRows", $numRows, \PDO::PARAM_INT);
        $st->execute();

        $list = array();
        while ($row = $st->fetch()) {
            $user = new self($row);
            $list[] = $user;
        }

        $sql = "SELECT COUNT(*) AS totalRows FROM users";
        $totalRows = $this->pdo->query($sql)->fetch();

        return array(
            "results" => $list,
            "totalRows" => $totalRows[0]
        );
    }

    /**
     * Получить список активных пользователей (новый метод)
     */
    public function getActiveUsersList(int $numRows = 1000000): array
    {
        $sql = "SELECT * FROM users WHERE active = 1 ORDER BY login LIMIT :numRows";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":numRows", $numRows, \PDO::PARAM_INT);
        $st->execute();

        $list = array();
        while ($row = $st->fetch()) {
            $user = new self($row);
            $list[] = $user;
        }

        $sql = "SELECT COUNT(*) AS totalRows FROM users WHERE active = 1";
        $totalRows = $this->pdo->query($sql)->fetch();

        return array(
            "results" => $list,
            "totalRows" => $totalRows[0]
        );
    }

    /**
     * Получить список пользователей для выбора авторов (только активные)
     */
    public static function getAuthorsList(): array
    {
        $model = new self();
        $result = $model->getActiveUsersList(1000000);
        return $result['results'];
    }

    /**
     * Проверить логин и пароль (из my-first-cms)
     */
    public static function checkLogin($login, $password)
    {
        $user = self::getByLogin($login);
        if ($user && $user->active) {
            // Получаем данные аутентификации
            $authData = $user->getAuthData($login);

            if ($authData) {
                // Проверяем пароль (поддержка нового password_hash)
                if (password_verify($password, $authData['pass'])) {
                    return $user;
                }

                // Проверяем старый md5 (для совместимости)
                if ($authData['pass'] == md5($password)) {
                    // Обновляем на password_hash
                    $user->pass = password_hash($password, PASSWORD_DEFAULT);
                    $user->update();
                    return $user;
                }
            }
        }
        return null;
    }

    /**
     * Получить статьи пользователя (из my-first-cms)
     */
    public function getArticles()
    {
        if (empty($this->id)) return array();

        $sql = "SELECT a.*, UNIX_TIMESTAMP(a.publicationDate) AS publicationDate 
                FROM articles a 
                INNER JOIN article_authors aa ON a.id = aa.article_id 
                WHERE aa.user_id = :userId AND a.active = 1 
                ORDER BY a.publicationDate DESC";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":userId", $this->id, \PDO::PARAM_INT);
        $st->execute();

        $articles = array();
        while ($row = $st->fetch()) {
            $articles[] = new Article($row);
        }

        return $articles;
    }

    /**
     * Удалить пользователя (из my-first-cms)
     */
    public function delete(): void
    {
        if (empty($this->id)) {
            throw new \Exception("UserModel::delete(): Attempt to delete a user without ID.");
        }

        // Проверяем, есть ли у пользователя статьи
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM article_authors WHERE user_id = :id");
        $st->bindValue(":id", $this->id, \PDO::PARAM_INT);
        $st->execute();
        $articleCount = $st->fetch()[0];

        if ($articleCount > 0) {
            throw new \Exception("Нельзя удалить пользователя, который является автором статей. Сначала удалите или перепривяжите статьи.");
        }

        $st = $this->pdo->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
        $st->bindValue(":id", $this->id, \PDO::PARAM_INT);
        $st->execute();
    }

    /**
     * Проверить, является ли пользователь администратором
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Проверить, является ли пользователь редактором
     */
    public function isEditor()
    {
        return $this->role === 'editor' || $this->role === 'admin';
    }

    /**
     * Проверить активность пользователя
     */
    public function isActive()
    {
        return (bool)$this->active;
    }

    /**
     * Получить массив ID авторов статьи
     */
    public static function getAuthorIdsForArticle($articleId)
    {
        $model = new self();
        $sql = "SELECT user_id FROM article_authors WHERE article_id = :articleId";
        $st = $model->pdo->prepare($sql);
        $st->bindValue(":articleId", $articleId, \PDO::PARAM_INT);
        $st->execute();

        $authorIds = array();
        while ($row = $st->fetch()) {
            $authorIds[] = $row['user_id'];
        }

        return $authorIds;
    }
}
