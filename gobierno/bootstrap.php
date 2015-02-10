<?php
include_once('../bootstrap.php');

if (empty($_SESSION['user']) || empty($_SESSION['underControl']) ) {
	
	header('Location: '.Config::get('basedir'));
	exit;
}