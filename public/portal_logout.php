<?php
// public/portal_logout.php
session_start();
session_unset();
session_destroy();
header('Location: portal.php');
exit;
