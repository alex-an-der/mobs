<?php
// Permanent redirect to ./../ypum/yfront/register.php
header("HTTP/1.1 301 Moved Permanently");
// Absolute Adresse, wegen Subdomain-Wechsel (register.mobs24.de => www.mobs24.de)
header("Location: https://www.mobs24.de/ypum/yfront/register.php");
exit();
?>
