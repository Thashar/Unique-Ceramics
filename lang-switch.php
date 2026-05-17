<?php
require_once __DIR__ . '/config.php';
$_SESSION['lang'] = current_lang() === 'pl' ? 'en' : 'pl';
$ref = $_SERVER['HTTP_REFERER'] ?? url('index.php');
redirect($ref);
