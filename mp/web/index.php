<?php
/*
 * main.php
 */

# Fehler werden nicht verheimlicht
error_reporting(E_ALL);
ini_set("display_errors", "1");

require_once("include/main.php");
ini_set("session.cookie_lifetime","36000"); // 10 hours

$main = new Main();
$main->run();
?>
