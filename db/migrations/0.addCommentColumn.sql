/* Теперь comment столбец называется corrected. А новый столбец comment отвечает за комментарий 
пользователя, который посылается вместе с исправлением. */

ALTER TABLE `messages` CHANGE `comment` `corrected` VARCHAR(255) 
CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Correct variant of a text';

ALTER TABLE `messages` ADD `comment` VARCHAR(255) 
NULL DEFAULT NULL COMMENT 'Optional user message' AFTER `corrected`;