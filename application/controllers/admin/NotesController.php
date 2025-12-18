<?php

namespace application\controllers\admin;

use application\models\Article;
use application\models\Category;
use application\models\Subcategory;
use application\models\UserModel;
use ItForFree\SimpleMVC\Config;
use application\assets\AdminAsset;

/* 
 *   Class-controller notes (для управления статьями через модель Article)
 * 
 */

class NotesController extends \ItForFree\SimpleMVC\MVC\Controller
{
    public function __construct()
    {
        parent::__construct();
        AdminAsset::add(); // подключаем стили админки
    }

    public string $layoutPath = 'admin-main.php';

    public function indexAction()
    {
        // Проверка прав администратора
        $User = Config::getObject('core.user.class');
        if (!$User->isAdmin()) {
            $this->redirect('/');
            return;
        }

        $articleId = $_GET['id'] ?? null;

        if ($articleId) { // если указана конкретная статья
            $Article = new Article();
            $viewArticle = $Article->getById($articleId);

            if (!$viewArticle) {
                $this->redirect(Config::get('core.router.class')::link("admin/notes/index"));
                return;
            }

            $this->view->addVar('viewArticle', $viewArticle);
            $this->view->render('note/view-item.php');
        } else { // выводим полный список

            // Получаем все статьи (включая скрытые) через модель Article
            $Article = new Article();
            $data = Article::getListWithDetails(1000000, null, null, "publicationDate DESC", false);

            $this->view->addVar('articles', $data['results']);
            $this->view->addVar('totalRows', $data['totalRows']);
            $this->view->render('note/index.php');
        }
    }

    /**
     * Выводит на экран форму для создания новой статьи
     */
    public function addAction()
    {
        $User = Config::getObject('core.user.class');
        if (!$User->isAdmin()) {
            $this->redirect('/');
            return;
        }

        $Url = Config::get('core.router.class');

        if (!empty($_POST)) {
            if (!empty($_POST['saveNewNote'])) {
                $Article = new Article();

                // Заполняем данные статьи
                $articleData = [
                    'title' => $_POST['title'] ?? '',
                    'summary' => $_POST['summary'] ?? '',
                    'content' => $_POST['content'] ?? '',
                    'categoryId' => $_POST['categoryId'] ?? null,
                    'subcategoryId' => $_POST['subcategoryId'] ?? null,
                    'active' => $_POST['active'] ?? 1,
                    'publicationDate' => $_POST['publicationDate'] ?? date('Y-m-d')
                ];

                // Создаем статью
                $newArticle = new Article($articleData);
                $newArticle->save();

                // Добавляем авторов
                if (isset($_POST['authors']) && is_array($_POST['authors'])) {
                    $newArticle->authors = $_POST['authors'];
                    $newArticle->saveAuthors();
                }

                $this->redirect($Url::link("admin/notes/index"));
            } elseif (!empty($_POST['cancel'])) {
                $this->redirect($Url::link("admin/notes/index"));
            }
        } else {
            // Получаем данные для формы
            $Category = new Category();
            $categoriesResult = $Category->getList();
            $categories = $categoriesResult['results'];

            $Subcategory = new Subcategory();
            $subcategoriesResult = $Subcategory->getList();
            $subcategories = $subcategoriesResult['results'];

            $UserModel = new UserModel();
            $usersResult = $UserModel->getActiveUsersList();
            $users = $usersResult['results'];

            //передаем переменные в представление
            $addNoteTitle = "Добавление новой статьи";

            $this->view->addVar('addNoteTitle', $addNoteTitle);
            $this->view->addVar('categories', $categories);
            $this->view->addVar('subcategories', $subcategories);
            $this->view->addVar('users', $users);

            $this->view->render('note/add.php');
        }
    }

    /**
     * Выводит на экран форму для редактирования статьи
     */
    public function editAction()
    {
        $User = Config::getObject('core.user.class');
        if (!$User->isAdmin()) {
            $this->redirect('/');
            return;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect(Config::get('core.router.class')::link("admin/notes/index"));
            return;
        }

        $Url = Config::get('core.router.class');

        if (!empty($_POST)) {
            if (!empty($_POST['saveChanges'])) {
                $Article = new Article();

                // Загружаем существующую статью
                $existingArticle = $Article->getById($id);
                if (!$existingArticle) {
                    $this->redirect($Url::link("admin/notes/index"));
                    return;
                }

                // Обновляем данные
                $existingArticle->title = $_POST['title'] ?? '';
                $existingArticle->summary = $_POST['summary'] ?? '';
                $existingArticle->content = $_POST['content'] ?? '';
                $existingArticle->categoryId = $_POST['categoryId'] ?? null;
                $existingArticle->subcategoryId = !empty($_POST['subcategoryId']) ? (int)$_POST['subcategoryId'] : null;
                $existingArticle->active = $_POST['active'] ?? 0;

                if (isset($_POST['publicationDate']) && !empty($_POST['publicationDate'])) {
                    // Преобразуем из формата формы в Unix timestamp или правильный формат даты
                    $existingArticle->publicationDate = strtotime($_POST['publicationDate']);
                } else {
                    $existingArticle->publicationDate = time(); // Текущее время
                }

                // Обновляем авторов
                if (isset($_POST['authors']) && is_array($_POST['authors'])) {
                    $existingArticle->authors = $_POST['authors'];
                } else {
                    $existingArticle->authors = [];
                }

                // Сохраняем изменения
                $existingArticle->save();

                $this->redirect($Url::link("admin/notes/index"));
            } elseif (!empty($_POST['cancel'])) {
                $this->redirect($Url::link("admin/notes/index&id=$id"));
            }
        } else {
            $Article = new Article();
            $viewArticle = $Article->getById($id);

            if (!$viewArticle) {
                $this->redirect($Url::link("admin/notes/index"));
                return;
            }

            // Получаем данные для формы
            $Category = new Category();
            $categoriesResult = $Category->getList();
            $categories = $categoriesResult['results']; // Извлекаем только массив объектов

            $Subcategory = new Subcategory();
            $subcategoriesResult = $Subcategory->getList();
            $subcategories = $subcategoriesResult['results']; // Извлекаем только массив объектов

            $UserModel = new UserModel();
            $usersResult = $UserModel->getActiveUsersList();
            $users = $usersResult['results']; // Извлекаем только массив объектов

            $editNoteTitle = "Редактирование статьи";

            $this->view->addVar('viewArticle', $viewArticle);
            $this->view->addVar('editNoteTitle', $editNoteTitle);
            $this->view->addVar('categories', $categories); // Передаем массив объектов
            $this->view->addVar('subcategories', $subcategories); // Передаем массив объектов
            $this->view->addVar('users', $users); // Передаем массив объектов пользователей

            $this->view->render('note/edit.php');
        }
    }

    /**
     * Выводит на экран предупреждение об удалении статьи
     */
    public function deleteAction()
    {
        $User = Config::getObject('core.user.class');
        if (!$User->isAdmin()) {
            $this->redirect('/');
            return;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect(Config::get('core.router.class')::link("admin/notes/index"));
            return;
        }

        $Url = Config::get('core.router.class');

        if (!empty($_POST)) {
            if (!empty($_POST['deleteNote'])) {
                $Article = new Article();
                $articleToDelete = $Article->getById($id);

                if ($articleToDelete) {
                    $articleToDelete->delete();
                }

                $this->redirect($Url::link("admin/notes/index"));
            } elseif (!empty($_POST['cancel'])) {
                $this->redirect($Url::link("admin/notes/edit&id=$id"));
            }
        } else {
            $Article = new Article();
            $deletedArticle = $Article->getById($id);

            if (!$deletedArticle) {
                $this->redirect($Url::link("admin/notes/index"));
                return;
            }

            $deleteNoteTitle = "Удалить статью?";

            $this->view->addVar('deleteNoteTitle', $deleteNoteTitle);
            $this->view->addVar('deletedArticle', $deletedArticle);

            $this->view->render('note/delete.php');
        }
    }
}
