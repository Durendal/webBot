<?php
require_once "src/FTPBot.php";

use Durendal\webBot as webBot;

$bot = new webBot\FTPBot("anonymous", "lolz@lolz.com", "ftp.hq.nasa.gov", 21);
$home = getenv("HOME");

print $bot->ls();
print $bot->ls("pub");
$bot->download("$home/robots.txt", "robots.txt");
$bot->download("$home/Doug's Presentation to Denver.ppt", "pub/Doug's Presentation to Denver.ppt");



?>