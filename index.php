<?php
header('Content-Type: application/json; charset=utf-8');
require_once("./Doviz.php");

$whiteList = [
  "test"
];

if(in_array($_GET["token"], $whiteList)){
    $doviz = new Doviz();
    $doviz->type = $_GET["type"];
    $doviz->key = $_GET["key"];
    echo json_encode($doviz->getExchange(), JSON_UNESCAPED_UNICODE);
    exit();
}

echo json_encode(["error" => "Geçersiz anahtar !"], JSON_UNESCAPED_UNICODE);