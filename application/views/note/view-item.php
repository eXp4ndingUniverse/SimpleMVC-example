<?php
use ItForFree\SimpleMVC\Config;
use ItForFree\SimpleMVC\Router\WebRouter;

$User = Config::getObject('core.user.class');
?>

<?php 
// Включите навигацию если файл существует
if (file_exists(__DIR__ . '/includes/admin-notes-nav.php')) {
    include('includes/admin-notes-nav.php');
}
?>

<h1><?= htmlspecialchars($article->title ?? '') ?>
    <span>
        <?php if ($User->returnIfAllowed("admin/notes/edit", true)): ?>
            <a href="<?= WebRouter::link("admin/notes/edit&id=" . ($article->id ?? '')) ?>">[Редактировать]</a>
        <?php endif; ?>
        
        <?php if ($User->returnIfAllowed("admin/notes/delete", true)): ?>
            <a href="<?= WebRouter::link("admin/notes/delete&id=" . ($article->id ?? '')) ?>">[Удалить]</a>
        <?php endif; ?>
    </span>
</h1>

<!-- Дата публикации -->
<p class="pubDate"><?= date('j F Y', strtotime($article->publicationDate ?? '')) ?></p>

<!-- Категория -->
<?php if (!empty($article->categoryName)): ?>
    <p class="category">
        in <a href="<?= WebRouter::link('category/view&id=' . ($article->categoryId ?? '')) ?>">
            <?= htmlspecialchars($article->categoryName) ?>
        </a>
    </p>
<?php endif; ?>

<!-- Подкатегория -->
<?php if (!empty($article->subcategoryName)): ?>
    <p class="subcategory">
        Подкатегория: 
        <a href="<?= WebRouter::link('subcategory/view&id=' . ($article->subcategoryId ?? '')) ?>">
            <?= htmlspecialchars($article->subcategoryName) ?>
        </a>
    </p>
<?php endif; ?>

<?php if (!empty($article->authors)): ?>
    <p class="authors">
        Authors: 
        <?php 
        $authorLinks = [];
        foreach ($article->authors as $author) {
            if (is_array($author) && isset($author['id']) && isset($author['login'])) {
                $authorLinks[] = '<a href="' . WebRouter::link('user/view&id=' . $author['id']) . '">' . 
                                 htmlspecialchars($author['login']) . '</a>';
            } elseif (is_object($author) && isset($author->id) && isset($author->login)) {
                $authorLinks[] = '<a href="' . WebRouter::link('user/view&id=' . $author->id) . '">' . 
                                 htmlspecialchars($author->login) . '</a>';
            }
        }
        echo implode(', ', $authorLinks);
        ?>
    </p>
<?php endif; ?>

<!-- Краткое описание -->
<?php if (!empty($article->summary)): ?>
    <p class="summary"><?= htmlspecialchars($article->summary) ?></p>
<?php endif; ?>

<!-- Содержание -->
<div class="content">
    <?= nl2br(htmlspecialchars($article->content ?? '')) ?>
</div>

<!-- Ссылка на главную -->
<p><a href="<?= WebRouter::link('homepage/index') ?>">← Вернуться на главную</a></p>