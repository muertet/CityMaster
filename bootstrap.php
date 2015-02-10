<?php
include_once('classes/Config.php');
include_once('classes/Autoloader.php');

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors',1);

Autoloader::init(Config::get('root').'\\');

setlocale(LC_ALL,"es_ES");
//date_default_timezone_set("Europe/Madrid");
date_default_timezone_set("GMT");
session_start();

