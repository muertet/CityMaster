<?php
include_once('bootstrap.php');

unset($_SESSION['app']);
unset($_SESSION['user']);

header('Location: http://'.Config::get('domain'));