<?php
include_once('bootstrap.php');

$code = $_GET['code'];

$user = new User();
$verified = $user->verify($code);

header('Location: http://'.Config::get('domain').'/');
?>