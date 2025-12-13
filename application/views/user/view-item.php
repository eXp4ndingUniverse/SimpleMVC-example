<?php
use ItForFree\SimpleMVC\Config;
use ItForFree\SimpleMVC\Router\WebRouter;

$User = Config::getObject('core.user.class');
?>

<h1>Автор: <?= htmlspecialchars($user->login ?? '') ?></h1>

<?php if (!empty($user->email)): ?>
    <p>Email: <?= htmlspecialchars($user->email) ?></p>
<?php endif; ?>

<?php if (!empty($articles)): ?>
    <h2>Статьи автора:</h2>
    <ul id="headlines">
    <?php foreach ($articles as $article): ?>
        <li>
            <h3>
                <a href="<?= WebRouter::link('note/view&id=' . $article->id) ?>">
                    <?= htmlspecialchars($article->title) ?>
                </a>
            </h3>
            <p class="pubDate"><?= date('j F Y', strtotime($article->publicationDate)) ?></p>
            
            <?php if (!empty($article->categoryName)): ?>
                <p class="category">
                    in <?= htmlspecialchars($article->categoryName) ?>
                    
                    <?php if (!empty($article->subcategoryName)): ?>
                        <span class="subcategory">
                            | Подкатегория: <?= htmlspecialchars($article->subcategoryName) ?>
                        </span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            
            <p class="summary"><?= htmlspecialchars(substr($article->content, 0, 200)) ?>...</p>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>У этого автора пока нет статей.</p>
<?php endif; ?>

<p><a href="<?= WebRouter::link('homepage/index') ?>">← Вернуться на главную</a></p>