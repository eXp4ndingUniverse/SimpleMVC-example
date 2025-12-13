<?php
use ItForFree\SimpleMVC\Router\WebRouter;
?>

<h1>Категория: <?= htmlspecialchars($category->name ?? '') ?></h1>

<?php if (!empty($category->description)): ?>
    <p><?= htmlspecialchars($category->description) ?></p>
<?php endif; ?>

<?php if (!empty($articles)): ?>
    <h2>Статьи в этой категории:</h2>
    <ul id="headlines">
    <?php foreach ($articles as $article): ?>
        <li>
            <h3>
                <a href="<?= WebRouter::link('note/view&id=' . $article->id) ?>">
                    <?= htmlspecialchars($article->title) ?>
                </a>
            </h3>
            <p class="pubDate"><?= date('j F Y', strtotime($article->publicationDate)) ?></p>
            
            <!-- КЛИКАБЕЛЬНАЯ ПОДКАТЕГОРИЯ -->
            <?php if (!empty($article->subcategoryName) && !empty($article->subcategoryId)): ?>
                <p class="subcategory">
                    Подкатегория: 
                    <a href="<?= WebRouter::link('subcategory/view&id=' . $article->subcategoryId) ?>">
                        <?= htmlspecialchars($article->subcategoryName) ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <!-- КЛИКАБЕЛЬНЫЕ АВТОРЫ -->
            <?php if (!empty($article->authors)): ?>
                <p class="authors">
                    Authors: 
                    <?php 
                    $authorLinks = [];
                    foreach ($article->authors as $author) {
                        if (is_array($author) && isset($author['id']) && isset($author['login'])) {
                            $authorLinks[] = '<a href="' . WebRouter::link('user/view&id=' . $author['id']) . '">' . 
                                             htmlspecialchars($author['login']) . '</a>';
                        }
                    }
                    echo implode(', ', $authorLinks);
                    ?>
                </p>
            <?php endif; ?>
            
            <p class="summary"><?= htmlspecialchars(substr($article->content, 0, 200)) ?>...</p>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>В этой категории пока нет статей.</p>
<?php endif; ?>

<p><a href="<?= WebRouter::link('homepage/index') ?>">← Вернуться на главную</a></p>