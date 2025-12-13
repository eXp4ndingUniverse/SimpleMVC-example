<?php
// application/controllers/CategoryController.php
namespace application\controllers;

use application\models\Article;
use application\models\Category;
use ItForFree\SimpleMVC\Router\WebRouter;

class CategoryController extends \ItForFree\SimpleMVC\MVC\Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->layoutPath = 'main.php';
    }
    
    /**
     * Показать статьи категории
     */
    public function viewAction()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->redirect(WebRouter::link('homepage/index'));
            return;
        }
        
        // Получить категорию
        $categoryModel = new Category();
        $category = $categoryModel->getById((int)$id);
        
        if (!$category) {
            $this->view->addVar('message', 'Категория не найдена');
            $this->view->render('error.php');
            return;
        }
        
        // Получить статьи этой категории
        $articlesData = Article::getListWithDetails(
            1000000, // все статьи
            $id,     // categoryId
            null,    // subcategoryId
            "publicationDate DESC",
            true     // onlyActive
        );
        
        $this->view->addVar('category', $category);
        $this->view->addVar('articles', $articlesData['results']);
        $this->view->addVar('pageTitle', 'Категория: ' . $category->name);
        
        $this->view->render('category/view.php');
    }
}