<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление статьи</title>
</head>
<body>

<?php 
use ItForFree\SimpleMVC\Config;

$Url = Config::getObject('core.router.class');
$User = Config::getObject('core.user.class');
?>

<?php include('includes/admin-notes-nav.php'); ?>

<h2><?= $deleteNoteTitle ?? 'Удаление статьи' ?></h2>

<div class="delete-confirmation">
    <p>Вы уверены, что хотите удалить статью "<strong><?= htmlspecialchars($deletedArticle->title ?? '') ?></strong>"?</p>
    
    <form method="post" action="<?= $Url::link("admin/notes/delete&id=" . ($_GET['id'] ?? '')) ?>">
        <input type="hidden" name="id" value="<?= $_GET['id'] ?? '' ?>">
        
        <div class="form-actions">
            <input type="submit" name="deleteNote" value="Удалить">
            <input type="submit" name="cancel" value="Отмена">
        </div>
    </form>
</div>

</body>
</html>