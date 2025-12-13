$(function () {
    console.log('Привет, это страый js ))');
    init_get();
    init_post();
});

function init_get() {
    $('a.ajaxArticleBodyByGet').one('click', function () {
        var contentId = $(this).attr('data-contentId');
        console.log('ID статьи = ', contentId);
        showLoaderIdentity();
        $.ajax({
            url: '/ajax/showContentsHandler.php?articleId=' + contentId,
            dataType: 'json'
        })
            .done(function (obj) {
                hideLoaderIdentity();
                console.log('Ответ получен');
                $('p.summary' + contentId).text(obj);
            })
            .fail(function (xhr, status, error) {
                hideLoaderIdentity();

                console.log('ajaxError xhr:', xhr); // выводим значения переменных
                console.log('ajaxError status:', status);
                console.log('ajaxError error:', error);

                console.log('Ошибка соединения при получении данных (GET)');
            });

        return false;

    });
}

function init_post() {
    $('a.ajaxArticleBodyByPost').one('click', function () {
        var content = $(this).attr('data-contentId');
        showLoaderIdentity();
        $.ajax({
            url: '/ajax/showContentsHandler.php',
            data: 'articleId=' + content,
            dataType: 'text',
            converters: 'json text',
            method: 'POST'
        })
            .done(function (obj) {
                console.log('Ответ получен', obj);
                $('p.summary' + content).text(JSON.parse(obj));
            })
            .fail(function (xhr, status, error) {
                console.log('Ошибка соединения с сервером (POST)');
                console.log('ajaxError xhr:', xhr); // выводим значения переменных
                console.log('ajaxError status:', status);
                console.log('ajaxError error:', error);
            });

        return false;

    });
}
