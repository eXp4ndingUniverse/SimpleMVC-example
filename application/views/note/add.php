<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление статьи</title>
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
                <h2><?= $addNoteTitle ?? 'Добавление статьи' ?></h2>
            </div>

            <?php if (isset($error)): ?>
                <div style="color: black; margin: 10px 0; padding: 10px; background-color: #f8f8f8; border: 1px solid #ddd;">
                    Ошибка: <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form id="addNote" method="post" action="<?= $Url::link("admin/notes/add") ?>" class="edit-user-form">
                <div class="form-group">
                    <h5>Заголовок статьи</h5>
                    <input type="text" class="form-input" name="title"
                        placeholder="Введите название статьи"
                        value="<?= htmlspecialchars($formData['title'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <h5>Краткое описание (summary)</h5>
                    <textarea class="form-input" name="summary"
                        placeholder="Введите краткое описание статьи..."
                        required style="height: 100px;"><?= htmlspecialchars($formData['summary'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <h5>Автор статьи</h5>
                    <select class="form-select" name="authorId" required>
                        <option value="" disabled>Выберите автора...</option>
                        <?php if (isset($users) && !empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->id ?>"
                                    <?= (isset($formData['authorId']) && $formData['authorId'] == $user->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user->login ?? 'Без имени') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Нет доступных авторов</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <h5>Категория</h5>
                    <select class="form-select" name="categoryId" required>
                        <option value="" disabled>Выберите категорию...</option>
                        <?php if (isset($categories) && !empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>"
                                    <?= (isset($formData['categoryId']) && $formData['categoryId'] == $category->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Нет доступных категорий</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <h5>Подкатегория</h5>
                    <select class="form-select" name="subcategoryId">
                        <?php if (isset($subcategories) && !empty($subcategories)): ?>
                            <?php foreach ($subcategories as $subcategory): ?>
                                <option value="<?= $subcategory->id ?>"
                                    <?= (isset($formData['subcategoryId']) && $formData['subcategoryId'] == $subcategory->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subcategory->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($error) && strpos($error, 'подкатегория') !== false): ?>
                        <div style="color: black; margin-top: 5px;"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <h5>Статус статьи</h5>
                    <select class="form-select" name="active" required>
                        <option value="1" selected>Активна</option>
                        <option value="0">Неактивна</option>
                    </select>
                </div>

                <div class="form-group">
                    <h5>Дата публикации</h5>
                    <input type="datetime-local" class="form-input" name="publicationDate"
                        value="<?= isset($formData['publicationDate']) ? htmlspecialchars($formData['publicationDate']) : date('Y-m-d\TH:i') ?>"
                        required>
                    <span class="helper-text">Обязательное поле</span>
                </div>

                <div class="form-group">
                    <h5>Содержание</h5>
                    <textarea class="form-input" name="content"
                        placeholder="Введите текст статьи..."
                        required><?= htmlspecialchars($formData['content'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <input type="submit" class="submit-button" name="saveNewNote" value="Сохранить">
                    <input type="submit" class="cancel-button" name="cancel" value="Назад">
                </div>
            </form>
        </div>
    </div>

</body>

</html>