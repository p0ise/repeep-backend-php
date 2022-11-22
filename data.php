<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET');
header('Content-Type:application/json; charset=utf-8');

require_once('./config.php');
require_once('./model.php');

$verify = trim(isset($_GET['v']) ? $_GET['v'] : '');
$key = strtolower(trim(isset($_GET['k']) ? $_GET['k'] : ''));

if ($verify !== $secret) {
    $result = array('status' => 1, 'data' => null);
} else if (empty($key)) {
    $result = array('status' => 2, 'data' => null);
} else {
    $model = new Model($key);
    $value = $model->data();
    $data = $value;
    $result = array('status' => 0, 'data' => $data);
}

exit(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
