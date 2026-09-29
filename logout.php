<?php
require_once 'config/init.php';
logoutUser();
setFlashMessage('info', 'You have been logged out successfully.');
header('Location: login.php');
exit;

