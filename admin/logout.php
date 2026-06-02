<?php
// admin/logout.php
// Log out admin user cleanly

require_once __DIR__ . '/../models/Admin.php';

Admin::logout();
header("Location: login.php");
exit();
