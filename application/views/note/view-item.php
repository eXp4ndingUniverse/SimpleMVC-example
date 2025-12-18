<?php
use ItForFree\SimpleMVC\Router\WebRouter;
?>
<h2>Просмотр статьи</h2>

<div class="card">
    <div class="card-header">
        <h3><?= htmlspecialchars($viewArticle->title ?? '') ?></h3>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>ID:</strong> <?= $viewArticle->id ?? '' ?></p>
                <p><strong>Дата публикации:</strong> 
                    <?= isset($viewArticle->publicationDate) ? date('d.m.Y', strtotime($viewArticle->publicationDate)) : '' ?>
                </p>
                <p><strong>Статус:</strong> 
                    <?php if (isset($viewArticle->active)): ?>
                        <span class="badge <?= $viewArticle->active ? 'badge-success' : 'badge-danger' ?>">
                            <?= $viewArticle->active ? 'Активна' : 'Неактивна' ?>
                        </span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6">
                <?php if (!empty($viewArticle->categoryName)): ?>
                    <p><strong>Категория:</strong> <?= htmlspecialchars($viewArticle->categoryName) ?></p>
                <?php endif; ?>
                <?php if (!empty($viewArticle->subcategoryName)): ?>
                    <p><strong>Подкатегория:</strong> <?= htmlspecialchars($viewArticle->subcategoryName) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($viewArticle->content)): ?>
        <div class="mb-3">
            <h5>Содержание:</h5>
            <div class="border p-3 bg-light">
                <?= nl2br(htmlspecialchars($viewArticle->content)) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($viewArticle->authors)): ?>
            <div class="mb-3">
                <h5>Авторы:</h5>
                <ul class="list-group">
                    <?php foreach ($viewArticle->authors as $author): ?>
                        <li class="list-group-item">
                            <?php if (is_array($author) && isset($author['id'])): ?>
                                <a href="<?= WebRouter::link('user/view&id=' . $author['id']) ?>" target="_blank">
                                    <?= htmlspecialchars($author['login'] ?? $author['name'] ?? 'Неизвестный автор') ?>
                                </a>
                            <?php elseif (is_object($author) && isset($author->id)): ?>
                                <a href="<?= WebRouter::link('user/view&id=' . $author->id) ?>" target="_blank">
                                    <?= htmlspecialchars($author->login ?? $author->name ?? 'Неизвестный автор') ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($author) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer">
        <?php if (isset($viewArticle->id)): ?>
            <a href="<?= WebRouter::link('admin/notes/edit&id=' . $viewArticle->id) ?>" class="btn btn-primary">
                Редактировать
            </a>
            <a href="<?= WebRouter::link('admin/notes/delete&id=' . $viewArticle->id) ?>" class="btn btn-danger">
                Удалить
            </a>
            <a href="<?= WebRouter::link('admin/notes/index') ?>" class="btn btn-secondary">
                К списку статей
            </a>
            <a href="<?= WebRouter::link('note/view&id=' . $viewArticle->id) ?>" class="btn btn-info" target="_blank">
                Просмотреть на сайте
            </a>
        <?php endif; ?>
    </div>
</div>