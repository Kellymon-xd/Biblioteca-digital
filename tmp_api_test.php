<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_POST = ['username' => 'admin', 'password' => 'root2514'];
require 'c:/P/PHP/Biblioteca digital/config.php';
$controller = new ApiController();
$controller->login();
