<?php
require_once "FTPBot.php";

$bot = new FTPBot("durendal", "*assfuck420", "192.168.1.42", 21);
$bot->download("/home/durendal/cannmine.sh", "/home/durendal/cannmine.sh");
var_dump($bot->ls("home/durendal"));


?>