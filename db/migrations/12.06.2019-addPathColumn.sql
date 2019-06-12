/*Добавление столбца для храния произвольного пути для сайта*/

ALTER TABLE `sites` ADD `path`VARCHAR(255) NULL DEFAULT NULL AFTER `site`;