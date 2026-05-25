// Некоторые тестовые наборы данных. Их можно дополнить, если тестировать тщательно +++
    var functions_to_TEST = [
['POST /api/deposit', "user_id:2", "amount:510.00", "comment:qwerДо~\"\\:`@#$%;&amp; *(),<.?/\|польty"],
	
		['POST /api/add', "user_id:1", "amount:0", "comment:0"],
        ['POST /api/add', "user_id:2", "amount:0", "comment:0"],
        ['POST /api/add', "user_id:3", "amount:0", "comment:0"],
        ['POST /api/add', "user_id:-1", "amount:0", "comment:0"],
        ['POST /api/add', "user_id:user2", "amount:0", "comment:0"],

        ['POST /api/deposit', "user_id:2", "amount:500.00", "comment:qwerДобавлен ~\"\\:`@#$%;&amp; *(),<.?/\| пользовательty"],
        ['POST /api/deposit', "user_id:2", "amount:500.00", "comment:Пополнение через карту"],
        ['POST /api/deposit', "user_id:-2", "amount:500.00", "comment:Пополнение через карту"],
        ['POST /api/deposit', "user_id:2", "amount:-500.00", "comment:Пополнение через карту"],
        ['POST /api/deposit', "user_id:карта", "amount:500.00", "comment:Пополнение через карту"],
        ['POST /api/deposit', "user_id:3", "amount:сумма", "comment:Пополнение через карту"],
        ['POST /api/deposit', "user_id:100000000000", "amount:500.00", "comment:Пополнение через карту"],
        ['POST /api/deposit', "user_id:3", "amount:100000000000", "comment:Пополнение через карту"],
        ['POST /api/deposit', "user_id:5", "amount:100.00", "comment:Пополнение через карту"],

        ['POST /api/withdraw', "user_id:2", "amount:500.00", "comment:Списание через карту"],
        ['POST /api/withdraw', "user_id:2", "amount:5.00", "comment:Списаниечерез карту"],
        ['POST /api/withdraw', "user_id:-2", "amount:500.00", "comment:Списаниечерез карту"],
        ['POST /api/withdraw', "user_id:2", "amount:-500.00", "comment:Списаниечерез карту"],
        ['POST /api/withdraw', "user_id:карта", "amount:500.00", "comment:Списаниечерез карту"],
        ['POST /api/withdraw', "user_id:3", "amount:сумма", "comment:Списаниечерез карту"],
        ['POST /api/withdraw', "user_id:100000000000", "amount:500.00", "comment:Списаниечерез карту"],
        ['POST /api/withdraw', "user_id:3", "amount:100000000000", "comment:Списаниечерез карту"],
        ['POST /api/withdraw', "user_id:5", "amount:300.00", "comment:Списаниечерез карту"],

        ['POST /api/transfer', "from_user_id:2", "to_user_id:3", "amount:50.00", "comment:Перевод через карту"],
        ['POST /api/transfer', "from_user_id:2", "to_user_id:3", "amount:qwe.00", "comment:Переводчерез карту"],
        ['POST /api/transfer', "from_user_id:5", "to_user_id:3", "amount:5000.00", "comment:Переводчерез карту"],
        ['POST /api/transfer', "from_user_id:2", "to_user_id:3", "amount:-50.00", "comment:Переводчерез карту"],
        ['POST /api/transfer', "from_user_id:2", "to_user_id:30", "amount:50.00", "comment:Переводчерез карту"],
        ['POST /api/transfer', "from_user_id:2", "to_user_id:qwe", "amount:10.00", "comment:Переводчерез карту"],
        ['POST /api/transfer', "from_user_id:200000000000000", "to_user_id:3", "amount:50.00", "comment:Переводчерез карту"],
        ['POST /api/transfer', "from_user_id:2", "to_user_id:3000000000000000", "amount:10.00", "comment:Переводчерез карту"],
        ['POST /api/transfer', "from_user_id:2", "to_user_id:3", "amount:10000000000000000000000000.00", "comment:Переводчерез карту"],

        ['GET /api/balance/{user_id}', "user_id:100000000000"],
        ['GET /api/balance/{user_id}', "user_id:1"],
        ['GET /api/balance/{user_id}', "user_id:qwe"]
    ]; // Массив атрибутов data-route у кнопок
