<?php
require_once '../config/init.php';
logoutAdmin();
setFlashMessage('info', 'You have been logged out from admin panel.');
header('Location: login.php');
exit;
