<?php
namespace application\controllers;

use application\models\Article;
use ItForFree\SimpleMVC\Router\WebRouter;

class NoteController extends \ItForFree\SimpleMVC\MVC\Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->layoutPath = 'main.php';
    }
    
    public function viewAction()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->redirect(WebRouter::link('homepage/index'));
            return;
        }
        
        // СОЗДАЙТЕ ЭКЗЕМПЛЯР модели Article
        $articleModel = new Article();
        $article = $articleModel->getById($id);
        
        if (!$article) {
            $this->view->addVar('message', 'Статья не найдена');
            $this->view->render('error.php');
            return;
        }
        
        // Загрузите авторов статьи
        if (method_exists($article, 'loadAuthors')) {
            $article->loadAuthors();
        }
        
        $this->view->addVar('article', $article);
        $this->view->addVar('pageTitle', $article->title);
        
        $this->view->render('note/view-item.php');
    }
}