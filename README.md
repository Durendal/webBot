webBot
======

## A web scraper written in PHP
webBot.php aims to simplify the use of cURL with php. At the moment it only handles GET and POST HTTP requests but I may add more to it as time and interest permits. It should work with HTTP and SOCKS proxies, the default behaviour is to use HTTP, to enable SOCKS proxies you must either declare it as the second parameter when instantiating the bot as in the first example below, or you must set it as the second parameter using the setProxy() method.

Donations Appreciated:

	BTC: 1NM3oe1k2wysV6B63iuzvQGVc9TrkespgU

## Proxies

An example of using it with tor:

    require_once 'src/HTTPBot.php';

    use Durendal\webBot as webBot;

	$bot = new webBot\HTTPBot("127.0.0.1:9050", "SOCKS");
	$page = $bot->requestGET("http://zqktlwi4fecvo6ri.onion/wiki/index.php/Main_Page");
	file_put_contents("index.html", $page);
	// index.html contains the html of the page

if you then ran setProxy() with no parameters it would clear the proxy settings and the same request would fail:

	$bot->setProxy();
	$page = $bot->requestGET("http://zqktlwi4fecvo6ri.onion/wiki/index.php/Main_Page");
	file_put_contents("index.html", $page);
	// index.html is an empty file


## Headers

Headers can be completely customized, although the defaults are enough to make basic requests. These values can also be overridden, added to, or deleted at any time.

Example:

	if($bot->checkHeader("Keep-alive"))
		$bot->delHeader("Keep-alive");
	$bot->addHeader("Keep-alive: 300");
	if($bot->checkHeader("User-Agent"))
		$bot->delHeader("User-Agent");
	$bot->addHeader("User-Agent: " . $bot->randomAgent());

POST parameters should be sent as an array through generatePOSTData() which will ensure they are urlencoded and properly formatted:

	$pdata = array("username" => "Durendal", "password" => "abc&123", "submit" => "true");
	$result = $bot->requestPOST("http://www.example.com/login.php", $bot->generatePOSTData($pdata));
	if(stristr($result, "Login Successful"))
		print "Successfully logged in\n";
	else
		print "Failed to log in\n";


## Parsing

This class also comes packaged with a number of parsing routines written by Mike Schrenk for his book Webbots, Spiders and Screenscrapers that I have found extremely useful in the past.

Example:

	require_once 'src/HTTPBot.php';

    use Durendal\webBot as webBot;

	$bot = new webBot\HTTPBot();
	$subreddit = ($argc > 1) ? $argv[1] : 'talesfromtechsupport';
	$page = $bot->requestGET("http://www.reddit.com/r/$subreddit/.rss");
	$posts = $bot->parseArray($page, "<item>", "</item>");
	$titles = array();
	$links = array();
	for($i = 0; $i < count($posts); $i++) {
		$titles[$i] = $bot->returnBetween($posts[$i], "<title>", "</title>", 1);
		$links[$i] = $bot->returnBetween($posts[$i], "<link>", "</link>", 1);
		print "Title #$i: ".$titles[$i]."\n";
		print "Link #$i: ".$links[$i]."\n";
	}


This script takes an optional parameter of a subreddit name the default is 'talesfromtechsupport' It will scrape the RSS feed and post the front page of posts. This should illustrate the basic principles of using the bot. All parsing methods were adapted from original code written by Mike Schrenk in his book 'Webbots spiders and Screenscrapers'

## curl_multi_*

This class is able to leverage the curl_multi_* functions to make multiple requests at once in batch mode. You can use a proxy with this function the same as you would with any other request, however at this time there is no way to specify a different proxy for each request. This may change in the future if I get the time. Send an array of arrays as the sole parameter, each array should have at least one element: the URL. If the request is a POST request place a second value inside the array that is an array of POST parameters. You can mix and match POST and GET requests, it will determine which is which at execution time.

Example:

	require_once 'src/HTTPBot.php';

    use Durendal\webBot as webBot;

	$bot = new webBot\HTTPBot("127.0.0.1:9050", "SOCKS");
	$creds = array("username" => "Durendal", "password" => "abc&123", "submit" => "true");
	$sites = array(array("http://www.google.com"), array("http://www.bing.com"), array("http://www.cnn.com"), array("http://zqktlwi4fecvo6ri.onion"), array("http://www.example.com/login.php", $creds));
	$results = $bot->curlMultiRequest($sites);

	foreach($results as $key => $page) {
		$key = str_replace(array("http://", "https://"), "", $key);
		print "Len: " . strlen($page) . "\n";
		file_put_contents("$key.html", $page);
	}

## Stack based URL Queue

You can optionally build a queue of URLs to scrape that acts as a FILO (First in last out) queue. This can work for individual or curl_multi_ requests, if its an individial request it will pop off the top URL and process it. If you run curl_multi it will process the entirety of the queue. In the future I may implement the option of selecting the number of urls to process. If there are items on the queue and you run requestGET(), requestPOST(), or requestHTTP() with an explicit url the queue will remain unaffected and the request will process normally.

Example:

	// Disable Proxy
	$bot->setProxy();

	$bot->pushURL("http://www.reddit.com/r/circlejerk/.rss");
	$bot->pushURL("http://www.reddit.com/r/bitcoin/.rss");
	$bot->pushURL("http://www.reddit.com/r/jobs4bitcoins/.rss");

	// Pop the top URL from the stack and execute a request
	$page = $bot->requestGET();

	$posts = $bot->parseArray($page, "<item>", "</item>");
	$titles = array();
	$links = array();

	for($i = 0; $i < count($posts); $i++) {
		$ii = $i+1;
		$titles[$i] = $bot->returnBetween($posts[$i], "<title>", "</title>", 1);
		$links[$i] = $bot->returnBetween($posts[$i], "<link>", "</link>", 1);
		print "Title #$ii: ".$titles[$i]."\n";
		print "Link #$ii: ".$links[$i]."\n";
	}

	// Check the stack size
	print "URL Stack Size: " . $bot->urlCount() . "\n";

	// Empty out the $bot->urls stack
	$results = $bot->curlMultiRequest();

	foreach($results as $key => $page) {
		// Make $key a little bit nicer for a filename
		$key = substr(str_replace(array("http://", "https://", ".rss", "www.reddit.com/r/"), "", $key), 0, -1);
		print $key . " Len: " . strlen($page) . "\n";
		file_put_contents("$key.html", $page);
	}

Special Thanks to:

	Viviparous
	Yani
	John Kozan

For providing suggestions to help make webBot better :)
