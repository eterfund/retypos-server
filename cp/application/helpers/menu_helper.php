<?php

/**
 * Menu helper. Composing menus
 *
 * @author george popoff <ambulance@etersoft.ru>
 */

/**
 * Composes and returns a admin menu as array of items
 * @return array
 */
function menu_admin($baseUrl)  {
    $data = [];

    $data[] = [
        "href" => "${baseUrl}index.php/admins/sites",
        "name" => "Сайты"
    ];

    $data[] = [
        "href" => "${baseUrl}index.php/admins/users",
        "name" => "Пользователи"
    ];

    $data[] = [
        "href" => "${baseUrl}index.php/users/typos",
        "name" => "Опечатки"
    ];

    return $data;
}   

/**
 * Composes and returns a user menu as array of items
 * @return array
 */
function menu_user($baseUrl)  {
    $data = [];

    $data[] = [
        "href" => "${baseUrl}index.php/users/typos",
        "name" => "Опечатки"
    ];

    return $data;
}

