<?php
namespace application\models;

use ItForFree\SimpleMVC\User;

/**
 * Класс для проверки авторизационных данных пользователя
 */
class AuthUser extends User
{        
    
    /**
     * Проверка логина и пароля пользователя.
     */
    protected function checkAuthData($login, $pass): bool 
    {
        $result = false;
        $User = new UserModel();
        $siteAuthData = $User->getAuthData($login);
        
        if (isset($siteAuthData['pass'])) {
            // Ваши пароли в MD5, а не password_hash!
            // Проверяем MD5
            
            // Если есть соль (но у вас её нет)
            if (!empty($siteAuthData['salt'])) {
                $pass .= $siteAuthData['salt'];
            }
            
            // Пробуем password_verify (на случай если пароли обновятся)
            if (password_verify($pass, $siteAuthData['pass'])) {
                $result = true;
            }
            // Проверяем MD5 (ваш текущий формат)
            elseif (md5($pass) === $siteAuthData['pass']) {
                $result = true;
            }
        }
        
        return $result;
    }

    /**
     * Получить роль по имени пользователя
     */
    protected function getRoleByUserName($login): string 
    {
        $User = new UserModel();
        $siteAuthData = $User->getRole($login);
        
        if (isset($siteAuthData['role'])) {
            return $siteAuthData['role'];
        }
        
        return 'guest'; // Возвращаем guest по умолчанию
    }
    
    /**
     * Проверяет, является ли текущий пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Получает имя текущего пользователя
     */
    public function getUserName(): string
    {
        return $this->userName ?? '';
    }
}