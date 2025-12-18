<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование статьи</title>
    <style>
        textarea {
            height: 200px;
            width: 1110px;
        }
    </style>
</head>

<body>

    <?php

    use ItForFree\SimpleMVC\Config;

    $Url = Config::getObject('core.router.class');
    $User = Config::getObject('core.user.class');
    ?>

    <?php include('includes/admin-notes-nav.php'); ?>

    <div class="admin-user-edit-container">
        <div class="edit-user-page">
            <div class="edit-user-header">
                <h2><?= $editNoteTitle ?></h2>
                <span>
                    <?php if ($User->isAllowed("admin/notes/delete") && isset($_GET['id'])): ?>
                        <a href="<?= $Url::link("admin/notes/delete&id=" . $_GET['id']) ?>"
                            class="delete-button">[Удалить]</a>
                    <?php endif; ?>
                </span>
            </div>

            <form id="editNote" method="post" action="<?= $Url::link("admin/notes/edit&id=" . ($_GET['id'] ?? '')) ?>" class="edit-user-form">
                <div class="form-group">
                    <h5>Заголовок статьи</h5>
                    <input type="text" class="form-input" name="title"
                        placeholder="Введите название статьи"
                        value="<?= htmlspecialchars($viewArticle->title ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <h5>Краткое описание (summary)</h5>
                    <textarea class="form-input" name="summary"
                        placeholder="Введите краткое описание статьи..."
                        required style="height: 100px;"><?= htmlspecialchars($viewArticle->summary ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <h5>Автор(ы) статьи</h5>
                    <select class="form-select" name="authors[]" multiple required style="height: 150px;">
                        <option value="" disabled>Выберите автора(ов)...</option>
                        <?php if (isset($users) && !empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <?php
                                // Получаем ID авторов статьи
                                $currentAuthorIds = [];
                                if (isset($viewArticle->authors) && is_array($viewArticle->authors)) {
                                    // Если authors это массив объектов с id
                                    if (is_object($viewArticle->authors[0] ?? null)) {
                                        $currentAuthorIds = array_column($viewArticle->authors, 'id');
                                    } else {
                                        // Если authors это массив ID
                                        $currentAuthorIds = $viewArticle->authors;
                                    }
                                }
                                ?>
                                <option value="<?= $user->id ?>"
                                    <?= in_array($user->id, $currentAuthorIds) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user->login ?? 'Без имени') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Нет доступных авторов</option>
                        <?php endif; ?>
                    </select>
                    <small class="helper-text">Удерживайте Ctrl для выбора нескольких авторов</small>
                </div>

                <!-- Категории (строки ~85-95): -->
                <div class="form-group">
                    <h5>Категория</h5>
                    <select class="form-select" name="categoryId" required>
                        <option value="" disabled>Выберите категорию...</option>
                        <?php if (isset($categories) && !empty($categories['results'])): ?>
                            <?php foreach ($categories['results'] as $category): ?>
                                <option value="<?= $category->id ?>"
                                    <?= (isset($viewArticle->categoryId) && $viewArticle->categoryId == $category->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php elseif (isset($categories) && is_array($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>"
                                    <?= (isset($viewArticle->categoryId) && $viewArticle->categoryId == $category->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Нет доступных категорий</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Подкатегории (строки ~100-110): -->
                <div class="form-group">
                    <h5>Подкатегория</h5>
                    <select class="form-select" name="subcategoryId">
                        <option value="">Без подкатегории</option>
                        <?php if (isset($subcategories) && !empty($subcategories['results'])): ?>
                            <?php foreach ($subcategories['results'] as $subcategory): ?>
                                <option value="<?= $subcategory->id ?>"
                                    <?= (isset($viewArticle->subcategoryId) && $viewArticle->subcategoryId == $subcategory->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subcategory->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php elseif (isset($subcategories) && is_array($subcategories)): ?>
                            <?php foreach ($subcategories as $subcategory): ?>
                                <option value="<?= $subcategory->id ?>"
                                    <?= (isset($viewArticle->subcategoryId) && $viewArticle->subcategoryId == $subcategory->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subcategory->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <h5>Статус статьи</h5>
                    <select class="form-select" name="active" required>
                        <option value="1" <?= (isset($viewArticle->active) && $viewArticle->active == 1) ? 'selected' : '' ?>>Активна</option>
                        <option value="0" <?= (isset($viewArticle->active) && $viewArticle->active == 0) ? 'selected' : '' ?>>Неактивна</option>
                    </select>
                </div>

                <div class="form-group">
                    <h5>Дата публикации</h5>
                    <input type="datetime-local" class="form-input" name="publicationDate"
                        value="<?= isset($viewArticle->publicationDate) ? date('Y-m-d\TH:i', strtotime($viewArticle->publicationDate)) : date('Y-m-d\TH:i') ?>"
                        required>
                    <span class="helper-text">Обязательное поле</span>
                </div>

                <div class="form-group">
                    <h5>Содержание</h5>
                    <textarea class="form-input" name="content"
                        placeholder="Введите текст статьи..."
                        required><?= isset($viewArticle->content) ? htmlspecialchars($viewArticle->content) : '' ?></textarea>
                </div>

                <?php if (isset($_GET['id'])): ?>
                    <input type="hidden" name="id" value="<?= $_GET['id']; ?>">
                <?php endif; ?>

                <div class="form-actions">
                    <input type="submit" class="submit-button" name="saveChanges" value="Сохранить">
                    <input type="submit" class="cancel-button" name="cancel" value="Назад">
                </div>
            </form>
        </div>
    </div>

</body>

</html>