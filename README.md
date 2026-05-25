# Notification-Service-email

Mailing Email Service

The system emulates bulk email sending. Messages are sent in two queues: regular and urgent. An asynchronous message broker is used to implement these queues. Messages are NOT actually sent; a stub function is used instead of mail() functions. For more details, see the !Readme.txt file.

Сервис массовой рассылки e-mail

Система эмулирует массовую отправку сообщений e-mail.
Система рассылает сообщения в виде двух очередей: обычной и срочной. Для реализации работы этих очередей есть асинхронный брокер сообщений. Сообщения фактически НЕ отправляются, вместо функций типа mail() используется функция-заглушка. 
Подробнее см. в файле !Readme.txt.
