<?php 
use ItForFree\SimpleMVC\Config;

$User = Config::getObject('core.user.class');
?>

<<?php
use ItForFree\SimpleMVC\Router\WebRouter;
?>
<h2>Статьи</h2>

<?php if (!empty($articles)): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Категория</th>
                <th>Дата публикации</th>
                <th>Активна</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
            <tr>
                <td><?= $article->id ?></td>
                <td>
                    <a href="<?= WebRouter::link('note/view&id=' . $article->id) ?>">
                        <?= htmlspecialchars($article->title) ?>
                    </a>
                </td>
                <td>
                    <?php if (!empty($article->categoryName)): ?>
                        <?= htmlspecialchars($article->categoryName) ?>
                    <?php endif; ?>
                </td>
                <td><?= date('d.m.Y', strtotime($article->publicationDate)) ?></td>
                <td>
                    <?php if ($article->active): ?>
                        <span class="badge badge-success">Да</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Нет</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= WebRouter::link('admin/notes/edit&id=' . $article->id) ?>" class="btn btn-sm btn-primary">Редактировать</a>
                    <a href="<?= WebRouter::link('admin/notes/delete&id=' . $article->id) ?>" class="btn btn-sm btn-danger">Удалить</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Статьи не найдены.</p>
<?php endif; ?>

<a href="<?= WebRouter::link('admin/notes/add') ?>" class="btn btn-success">Добавить статью</a>