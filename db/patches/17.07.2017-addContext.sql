/**
 * Author:  jorgen
 * Created: 17.07.2017
 */
/**
* Добавляет в таблицу сообщений еще одно поле: context, содержащее контекст, в котором была найдена ошибка
*/

ALTER TABLE `messages` ADD `context` TEXT NOT NULL AFTER `text`;


