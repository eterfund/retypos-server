# Retypos-server

Retypos-server - это серверная часть и панель администратора системы исправления опечаток Etersoft. 

## Установка

1. Склонируйте репозиторий с проектом:

```bash
git clone git@gitlab.eterfund.ru:eterfund/typoservice.git retypos-server
```
2. Настройте следующие файлы конфигурации и сохраните их под именем, которое указано в скобках:

 - ./configuration.php.example (configuration.php) - общая конфигурация для скрипта обработки запросов на добавление опечаток

 - /cp/application/config/database_example.php (database.php) - конфигурация административной панели.

 - /cp/application/config/typos_config_example.php (typos_config.php) - конфигурация системы исправления опечаток.

3. Выполните следующие команды, находясь в папке cp/

```bash
composer install
npm install
npm run webpack
```

4. Настройте базу данных

Схема базы данных находится в db/typos.sql

Также необходимо применить патчи, сначала в директории db/patches, а затем в migrations/

После выполнения данных шагов административная панель и скрипт обработки опечаток должны работать должным образом.

## Использование 

Данный сервер предназначен для работы в составе системы исправления опечаток Etersoft. 

### Серверный сценарий server.php

Входящие запросы на добавление опечаток обрабатывает server.php. Если конфигурация в файле configuration.php была настроена должным образом, то входящие опечатки будут сохранены в базе данных.

### Панель управления опечатками
В панели управления опечатками, которая находится по адресу /cp можно управлять опечатками, пользователями и сайтами. 
Логин и пароль главного администратора панели указывается в файле /cp/application/config/typos_config.php:

```php
$config['typos_admin_login'] = 'admin';
$config['typos_admin_password'] = 'password';
$config['typos_admin_email'] = 'email@admin.com';
```

Измените их на нужные значения. После этого можно авторизоваться в панели администратора, используя указанные логин и пароль.

Администратор может добавлять новых пользователей и назначать им сайты, каждый пользователь может быть ответственным за один или более сайтов. Когда пользователь отвечает за какой-либо сайт, опечатки, которые пользователи регистрируют на страницах этого сайта, отображаются пользователю в разделе "Опечатки".

### Управление опечатками

После авторизации пользователь может управлять опечатками на сайтах, за которые он отвечает. Для того, чтобы выбрать сайт, опечатки которого нужно посмотреть, можно воспользоваться вкладками, названия которых соответствуют адресам сайтов. При переключении вкладки будут отображены опечатки с данного сайта. 
В виде карточки пользователю отображается следующая информация об опечатке:

 - Текст с опечаткой
 - Предложение по исправлению опечатки
 - Контекст, в котором была найдена опечатка
 - Опциональный комментарий пользователя

Также возможно изменять исправленный вариант, предложенный пользователем. Для этого нужно нажать на текст исправления, он станет редактируемым, после чего ввести правильный вариант.

Для каждой опечатки доступно 2 действия:

- Принять опечатку - принятая опечатка будет автоматически исправлена на сайте, в случае, если там используется адаптер системы исправления опечаток Etersoft.
- Отклонить опечатку - принятая опечатка не будет автоматически исправлена на сайте.

После выполнения какого-либо действия над опечаткой она будет удалена и пользователь увидит следующий отчёт об опечатке.





