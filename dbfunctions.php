<?php

function get_site_id($DBH, $url) {
    //error_log("\nurl: $url\n");
    $mas_url = parse_url($url);
    if (!isset($mas_url['host']))
        return 0;
    $host = $mas_url['host'];

    $query_sites = "SELECT sites.id AS id, sites.site AS url FROM sites
            WHERE sites.site REGEXP ?";

    $STH = $DBH->prepare($query_sites);

    $STH->execute(array("^(https?://)*(www.)*" . $host . "/?"));
    if ($STH->rowCount() == 0) {
        error_log("no host $host");
        return 0;
    }

    $max_id = 0;
    $max_len = 0;
    while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
        //echo $row['id'] . " " . $row['url'] . " in " . $url . "\n";
        //preg_match('/(foo)(bar)(baz)/', $row['url'] , $matches
        // TODO: сравнивать строки, начиная с ://
        $pos = strpos ($url, $row['url']);
        $len = strlen($row['url']);
        //echo "pos = $pos, len = $len\n";
        if ($pos !== false && $max_len < $len) {
            $max_id = $row['id'];
            $max_len = $len;
        }
    }
    //echo "Result: $max_id, $max_len\n";
    return $max_id;
}

/*
// Uncomment for test
require __DIR__ . "/vendor/autoload.php";

require_once('configuration.php');

$DBH = new PDO(DB_DRIVER . ":host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

get_site_id($DBH, "https://bogaiskov.ru");
get_site_id($DBH, "https://bogaiskov.ru/test/t1/page.php");
get_site_id($DBH, "https://bogaiskov.ru/dum");
*/
