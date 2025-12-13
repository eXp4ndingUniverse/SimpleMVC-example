<?php
// application/controllers/SubcategoryController.php
namespace application\controllers;

use application\models\Article;
use application\models\Subcategory;
use ItForFree\SimpleMVC\Router\WebRouter;

class SubcategoryController extends \ItForFree\SimpleMVC\MVC\Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->layoutPath = 'main.php';
    }
    
    /**
     * Показать статьи подкатегории
     */
    public function viewAction()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->redirect(WebRouter::link('homepage/index'));
            return;
        }
        
        // Получить подкатегорию
        $subcategoryModel = new Subcategory();
        $subcategory = $subcategoryModel->getById((int)$id);
        
        if (!$subcategory) {
            $this->view->addVar('message', 'Подкатегория не найдена');
            $this->view->render('error.php');
            return;
        }
        
        // Получить статьи этой подкатегории
        $articlesData = Article::getListWithDetails(
            1000000, // все статьи
            $subcategory->categoryId, // categoryId
            $id,                      // subcategoryId
            "publicationDate DESC",
            true                      // onlyActive
        );
        
        $this->view->addVar('subcategory', $subcategory);
        $this->view->addVar('articles', $articlesData['results']);
        $this->view->addVar('pageTitle', 'Подкатегория: ' . $subcategory->name);
        
        $this->view->render('subcategory/view.php');
    }
}