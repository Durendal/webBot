<?php
require_once "src/FTPBot.php";

use Durendal\webBot as webBot;

$bot = new webBot\FTPBot("anonymous", "lolz@lolz.com", "ftp.hq.nasa.gov", 21);

print $bot->ls();

$bot->download("/home/durendal/robots.txt", "robots.txt");


?>