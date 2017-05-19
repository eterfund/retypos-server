<?php

/**
 * Menu helper. Composing menus
 *
 * @author george popoff <ambulance@etersoft.ru>
 */

/**
 * Composes and returns a admin menu as array of items
 * @return string
 */
function menu_admin($baseUrl)  {
    $data['sites'] = "<a href='".$baseUrl."index.php/admins/sites'>Сайты</a>";
    $data['users'] = "<a href='".$baseUrl."index.php/admins/users'>Пользователи</a>";
    $data['typos'] = "<a href='".$baseUrl."index.php/users/typos'>Опечатки</a>";
    $data['logout'] = "<a href='".$baseUrl."index.php/authorization/logout'>Выйти</a>";

    return $data;
}   

/**
 * Composes and returns a user menu as array of items
 * @return string
 */
function menu_user($baseUrl)  {
    $data['typos'] = "<a href='".$baseUrl."index.php/users/typos'>Опечатки</a>";
    $data['logout'] = "<a href='".$baseUrl."index.php/authorization/logout'>Выйти</a>";
    
    return $data;
}

