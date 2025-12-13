<?php
// application/views/homepage/index.php
use application\assets\DemoJavascriptAsset;
use ItForFree\SimpleMVC\Router\WebRouter;

DemoJavascriptAsset::add();
?>

<?php if (!empty($articles)): ?>
    <ul id="headlines">
    <?php foreach ($articles as $article): ?>
        <li>
            <h2>
                <a href="<?= WebRouter::link('note/view&id=' . $article->id) ?>">
                    <?= htmlspecialchars($article->title) ?>
                </a>
            </h2>
            
            <!-- Дата публикации -->
            <p class="pubDate"><?= date('j F Y', strtotime($article->publicationDate)) ?></p>
            
            <!-- КЛИКАБЕЛЬНАЯ КАТЕГОРИЯ -->
            <?php if (!empty($article->categoryName) && !empty($article->categoryId)): ?>
                <p class="category">
                    in <a href="<?= WebRouter::link('category/view&id=' . $article->categoryId) ?>">
                        <?= htmlspecialchars($article->categoryName) ?>
                    </a>
                </p>
            <?php endif; ?>
            
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
                        } elseif (is_object($author) && isset($author->id) && isset($author->login)) {
                            $authorLinks[] = '<a href="' . WebRouter::link('user/view&id=' . $author->id) . '">' . 
                                             htmlspecialchars($author->login) . '</a>';
                        }
                    }
                    echo implode(', ', $authorLinks);
                    ?>
                </p>
            <?php endif; ?>
            
            <!-- Краткое содержание -->
            <p class="summary"><?= htmlspecialchars(substr($article->content, 0, 200)) ?>...</p>
            
            <!-- Ссылка на полный текст -->
            <p>
                <a href="<?= WebRouter::link('note/view&id=' . $article->id) ?>">
                    Показать полностью
                </a>
            </p>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Статей пока нет.</p>
<?php endif; ?>