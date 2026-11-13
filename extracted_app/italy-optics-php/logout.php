<?php
require_once __DIR__ . '/config/config.php';

unset($_SESSION['user']);
$_SESSION['flash_info'] = "You have been logged out.";
header("Location: index.php");
exit;
