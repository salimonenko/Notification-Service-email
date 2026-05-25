<?php
header('Content-type: text/html; charset=utf-8');

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Система рассылки</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>
    <meta name="description" content="Проект работает как на РНР 5.3, так и на PHP 8.0.
•	Хранение данных — в MySQL.
•	Вся информация о сообющениях пользователям выполняется в транзакциях.
•	Баланс не может быть отрицательным.
•	Если о пользователя нет записи — она создаётся при генерации (первом пополнении).
•	Все ответы и ошибки будут в формате JSON, с корректными HTTP-кодами.
        200 — успешный ответ
        400 / 422 — ошибки валидации
        404 — пользователь не найден
        409 — конфликт (например, некорректный e-mail)
•	Транзакция (как результат рассылки) имеет следующие статусы сообщений:
        0 - не отправлялось;
        1 - в очереди (сообщение принято и ожидает отправки);
        2 - отправлено (передано шлюзу/провайдеру);
        3 - доставлено (подтверждено провайдером);
       -1 - отброшено (ошибка доставки, несуществующий номер/email и т.д.).
deposit, withdraw, transfer_in, transfer_out"/>
<style type="text/css">

* { font-size: 13px; box-sizing: border-box; font-family: Arial }

#channel { vertical-align: top; }
#text { width: 100%; }
#theme { width: 70% }

.main { max-width: 600px; border: solid 2px; display: inline-block; vertical-align: top; min-width: 300px; min-height: 200px }

button { height: 60px; font-size: 16px; margin-top: 10px; display: block }
pre { display: inline-block; white-space: pre-wrap; }
</style>

</head>
<body>
<div class="main">
    <textarea id="text" rows="6" placeholder="Введите сообщение..."></textarea>
    <textarea id="email" rows="10" cols="50"
              placeholder="Введите список e-mail для рассылки через запятую. Например: user1@example.com, user2@example.com. Если ничего не будет введено, то сообщение отправится ВСЕМ пользователям, присутствующим в базе данных"></textarea>

    <select id="channel" title="Выберите вид сообщения">
        <option selected="selected">e-mail</option>
        <option>SMS</option>
    </select>

    <input id="theme" type="text"  placeholder="Пустая тема..." />

    <button onclick="send_message();" title="Сделать рассылку сообщений">Сделать рассылку сообщений</button>

    <div>
        <div style="display: inline-block; vertical-align: top; margin-right: 20px">
            <button onclick="add_user(this);" title="Добавить нового пользователя POST /api/add_user" value="POST">Добавить нового пользователя...
            </button>
            <input type="text" name="telefon" placeholder="Номер телефона..."/>
            <input type="text" name="email" placeholder="email..."/>
        </div>

        <div style="display: inline-block; vertical-align: top">
            <button style="" onclick="show_user_messages(this);" title="Посмотреть все e-mail сообщения пользователя с их статусами POST /api/POST_api_email_messages_user_id" value="POST">Посмотреть историю <br/>сообщений пользователя
            </button>
            <input type="text" name="user_id" placeholder="Введите id..."/>
        </div>

        <input type="hidden" name="text" value=""/>
        <input type="hidden" name="status" value="0"/>
    </div>

    <div style="background-color: #ffbc93; padding: 10px; margin-top: 10px; display: none">
        <div id="testing_INFO">
            <button title="Будут показаны файлы и каталоги этого проекта с указанием страниц у файлов, которые помечены как требующие доработки">
                Узнать о требующихся доработках <br/>в этом проекте
            </button>
        </div>

        <div style="padding-top: 10px">
            <div id="testing_INFO1">
                <button title="Запустить автоматические тесты для этого проекта">Запуск тестирования</button>
            </div>
            <div id="testing_CHECK">
                <button title="Проверить результаты предыдущего тестирования">Проверить результаты тестирования</button>
            </div>
        </div>
    </div>

</div>


<div class="main">
    <pre id="id_RESPONSE"></pre>
</div>

<script type="text/javascript">

// Функция делает запрос на вывод всех сообщений с их статусами для пользователя с заданным id. todo: Возможно, ее стоит объединить с функцией  add_user  +++
function show_user_messages($this) {

    var inputs = $this.parentNode.getElementsByTagName('input');
    var data_Obj = {};
    var flag_error = false;

    Array.prototype.forEach.call(inputs, function (item, i, arr) {
        var name = item.getAttribute('name');

        if (!item.value) {
            alert('Введите ' + name);
            flag_error = true;
            return;
        }
        data_Obj[name] = encodeURIComponent(item.value);
    });

    if (flag_error) {
        return;
    }

    var route = ('../routes/web.php?route=/api/email_messages_user_id');
    sender('POST', route, 'id_RESPONSE', data_Obj, 'JSON', true, false);
}


// Функция делает запрос на добавление пользователя в БД.
function add_user($this) {

    var inputs = $this.parentNode.getElementsByTagName('input');
    var data_Obj = {};
    var flag_error = false;

    Array.prototype.forEach.call(inputs, function (item, i, arr) {
        var name = item.getAttribute('name');

        if (!item.value) {
            alert('Введите ' + name);
            flag_error = true;
            return;
        }
        data_Obj[name] = encodeURIComponent(item.value);
    });

    if(flag_error){
        return;
    }


    var route = ('../routes/web.php?route=/api/add_user');
    sender('POST', route, 'id_RESPONSE', data_Obj, 'JSON', true, false);
}
// Функция делает запрот на рассылку сообщений по заданным e-mail или всем пользователям
function send_message() {

    var text = document.getElementById('text').value;
    if (!text) {
        alert('Введите сообщение');
        return;
    }

    var theme = document.getElementById('theme').value;
    if (!theme) {
        theme = 'Пустая тема';
    }

    var channel = document.getElementById('channel').value;
    if(channel === 'SMS'){
        alert('Отправка SMS пока не реализована. Можно эмулировать отправку только e-mail');
        return;
    }


    var email = document.getElementById('email').value;

    if (!email) {
        if (confirm('Ни один e-mail не задан. Отправить сообщения ВСЕМ пользователям?')) {

        } else {
            return;
        }
    }

    var data_Obj1 = {};

    var params_Obj = {text: text, channel: channel, email: email, theme: theme};
    for (var key in params_Obj) {
        data_Obj1[key] = encodeURIComponent(params_Obj[key]);
    }

    var route = ('../routes/web.php?route=/api/add_user_mes_all');
    sender('POST', route, 'id_RESPONSE', data_Obj1, 'JSON', true, false);
}

// Функция отправляет сообщение на сервер  и ждет того или иного ответа, выводя потом его в alert
function sender(method, route, id_to_RESPONSE, data_Obj1, format, flag_ALERT, flag_RESPONSE_ADD, Function_AFTER = false, Function_AFTER_args_Arr = false) {
/*  Можно отправить GET или POST запрос
    Можно отправить сообщение в формате JSON или HTML (XML)
    Можно указать имя функции и ее аргументы, которая будет выполняться после получения ответа сервера
*/
    var xhr = new XMLHttpRequest();

    if (format === 'JSON') {
// 1. Готовим тело сообщения для отправки
        data_Obj1["route"] = encodeURIComponent(route);

        var body_FINAL = JSON.stringify(data_Obj1);

        var xhrHeader_Content_Type;
            xhrHeader_Content_Type = "application/json; charset=utf-8";

    } else if (format === 'HTML') { // HTML
        xhrHeader_Content_Type = 'application/x-www-form-urlencoded';

        body_FINAL = data_Obj1 + '&route=' + encodeURIComponent(route); // Предполагается, что data_Obj1 (при формате 'HTML') теперь представляет собой обычную строку HTML-запроса с соединительными амперсандами
    } else {
        alert('Формат сообщений, отправляемых на сервер, может быть либо JSON, либо HTML. Нужно задать тот или иной формат или доработать программу');
        return;
    }

    var GET_reque = '', POST_reque = '';
    if (method === "GET") {
        GET_reque = '?json_str=' + body_FINAL;
    } else if (method === 'POST') {
        POST_reque = body_FINAL;
    } else { // Можно доработать с учетом других методов (PUT, DELETE и т.д.) +++
        method = 'POST';
        POST_reque = body_FINAL;
    }

    console.log('Итак, вот что отправляем на сервер методом ' + method + ', в формате "' + format + '":');
    console.log(POST_reque ? POST_reque : GET_reque);

    xhr.open(method, route + GET_reque, true); // Имена всех методов посылаем заданным методом
// 2. например, xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.setRequestHeader('Content-Type', xhrHeader_Content_Type);
    xhr.onreadystatechange = function xhr_state() {
        if (xhr.readyState != 4) return;
        if (xhr.status <= 300) {
// 3. После подтверждения получения сообщения сервером выдаем оповещение
//                if(flag_ALERT) alert('Операция '+ method + ' выполнена правильно.');
        } else {
            if (flag_ALERT)
                alert('xhr message: ' + xhr.statusText); // Сообщение об ошибке на транспортном (ТСР) уровне. Обычно вызвано проблемами  с доступом к сети или неправильной работой РНР на сервере, т.п.
        }
// Ответ придет в блок с id=id_RESPONSE

/*  В случае фатальных ошибок или т.п. Дело в том, что тогда РНР по своей инициативе выдает сообщение в ТЕКСТОВОМ виде, не в JSON. Проблема в том, что функция РНР  register_shutdown_function() также выдаст сообщение, а вот оно будет json_encode(array('... текст...'). Поэтому придется ОБРАТНО декодировать JSON (последовательности вида \u0431) в читаемый текст (актуально для кириллицы, например).
*/

// 4.1. Если определено, что ожидался ответ сервера в JSON
        if (format === 'JSON') {

//            document.getElementById(id_to_RESPONSE).innerHTML = decodeURIComponent(xhr.responseText.replace(/[\r\n]+/g, ' \ '));
//                var f = decodeURIComponent(xhr.responseText).replace(/[\r\n]+/g, ' \ ');
            var f = xhr.responseText; // Убрать?... +++

            try {
// Если будет ошибка с JSON, выполнение этой функции остановится, поэтому ниже запись в блок будет делаться уже НЕ в формате JSON
                var obj = JSON.parse(xhr.responseText); // Проверяем, корректный ли JSON пришел с сервера. Если да, то разбираем его

                var str = '';
                for (var t in obj) {
                    if (t !== '0') {
                        str += t + ': ';
                    }
                        str += ( obj[t] + '<br>');
                }
                console.log(str);

                    if (flag_RESPONSE_ADD) {
                        document.getElementById(id_to_RESPONSE).innerHTML += str;
                    } else {
                        document.getElementById(id_to_RESPONSE).innerHTML = str;
                    }

            } catch (er) { // Если JSON некорректный (тут надо доработать, иногда при ответе сервера все же появляются юникод-последовательности вида \u0431) +++
                console.log('Ожидался ответ сервера в виде JSON. Но, ответ оказался некорректным JSON');
// Denwer может в случае ошибки также вставить свой скрипт, а это мешает
                f = f.replace(/<script[^>]*>([^<]*)<\/script>/g, ' ');
                f = f.replace(/\\\"/g, ' * '); // Убираем излишние кавычки
                f = f.replace(/\"/g, '\\"').toString();
// Заменяем последовательности вида \u0431 на читаемые символы (русские или т.п.)
                f = f.replace(/(\\u[\w]{4})/g, function (match, p1) {
                return JSON.parse('"' + p1 + '"');
// return decodeURIComponent(p1.toString()) // Почему-то не работает, хотя должно
                });

                if (flag_RESPONSE_ADD) { // Если true, то добавляем очередной ответ сервера в инфо-блок (предыдущие ответы сохраняются)
                    document.getElementById(id_to_RESPONSE).innerHTML += f;
                } else {
                    document.getElementById(id_to_RESPONSE).innerHTML = f; // Предыдущие ответы НЕ сохраняются
                }
            }

        } else { // 4.2. Если не JSON. Например, если формат был задан как HTML
            if (flag_RESPONSE_ADD) {
                document.getElementById(id_to_RESPONSE).innerHTML += xhr.responseText;
            } else {
                document.getElementById(id_to_RESPONSE).innerHTML = xhr.responseText;
            }
        }

// 5. Если нужно что-то сделать после того, как ответ сервера помещен в соответствующий блок
        if (Function_AFTER && (typeof Function_AFTER) === 'function') { // Если задана функция-обработчик и она существует
            Function_AFTER(Function_AFTER_args_Arr, xhr);
        }
    };

        xhr.send(POST_reque);
        return false;
}

</script>


</body>
</html>