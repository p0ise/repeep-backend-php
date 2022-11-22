<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET');
header('Content-Type:application/json; charset=utf-8');

require_once('./config.php');
require_once('./model.php');

$verify = trim(isset($_GET['v']) ? $_GET['v'] : '');

if ($verify !== $secret) {
    $result = array('status' => 1, 'key' => null);
} else {
    $key = Model::get_key();
    $result = array('status' => 0, 'key' => $key);
}

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
