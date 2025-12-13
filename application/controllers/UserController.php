<?php
// application/controllers/UserController.php
namespace application\controllers;

use application\models\UserModel;
use application\models\Article;
use ItForFree\SimpleMVC\Router\WebRouter;

class UserController extends \ItForFree\SimpleMVC\MVC\Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->layoutPath = 'main.php';
    }
    
    /**
     * Показать профиль пользователя и его статьи
     */
    public function viewAction()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->redirect(WebRouter::link('homepage/index'));
            return;
        }
        
        // СОЗДАЙТЕ ЭКЗЕМПЛЯР модели UserModel
        $userModel = new UserModel();
        
        // Используйте метод getById() через экземпляр
        $user = $userModel->getById((int)$id);
        
        if (!$user) {
            $this->view->addVar('message', 'Пользователь не найден');
            $this->view->render('error.php');
            return;
        }
        
        // Получить статьи пользователя
        $userArticles = $user->getArticles();
        
        $this->view->addVar('user', $user);
        $this->view->addVar('articles', $userArticles);
        $this->view->addVar('pageTitle', 'Автор: ' . $user->login);
        
        $this->view->render('user/view-item.php');
    }
}