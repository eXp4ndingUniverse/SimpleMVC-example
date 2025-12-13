<?php
// application/controllers/HomepageController.php
namespace application\controllers;

use application\models\Article;

class HomepageController extends \ItForFree\SimpleMVC\MVC\Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->layoutPath = 'main.php';
    }
    
    public function indexAction()
    {
        // Получаем статьи с деталями (категории, подкатегории, авторы)
        $articlesData = Article::getListWithDetails(10, null, null, "publicationDate DESC", true);
        $articles = $articlesData['results'];
        
        // Передаем данные в представление
        $this->view->addVar('homepageTitle', 'Последние статьи');
        $this->view->addVar('articles', $articles);
        
        $this->view->render('homepage/index.php');
    }
}