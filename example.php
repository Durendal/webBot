<?php

	require_once 'webBot.php';

	// Vanilla instantiation, no proxy
	$bot = new webBot();

	$bot->setVerbose();
	$subreddit = ($argc > 1) ? $argv[1] : 'talesfromtechsupport';

	// GET based HTTP Request to reddit
	$page = $bot->requestGET("http://www.reddit.com/r/$subreddit/.rss");

	// Create an array by extracting the text between <item> and </item> elements inclusively
	$posts = $bot->parse_array($page, "<item>", "</item>");
	$titles = array();
	$links = array();

	for($i = 0; $i < count($posts); $i++)
	{
		$ii = $i+1;

		// Remove the <title> and </title> tags, ensure the last parameter is 1 or they will remain.
		$titles[$i] = $bot->return_between($posts[$i], "<title>", "</title>", 1);
		$links[$i] = $bot->return_between($posts[$i], "<link>", "</link>", 1);
		print "Title #$ii: ".$titles[$i]."\n";
		print "Link #$ii: ".$links[$i]."\n";
	}

	print "\n\n";
	// Assign a Proxy to our bot
	$bot->setProxy("127.0.0.1:9050", "SOCKS");

	// Check the User-Agent
	print "User-Agent: " . $bot->getAgent() . "\n";

	// View Proxy Settings
	var_dump($bot->getProxy());

	// Lets try scraping an onion site
	print "Scraping hidden index...\n";
	$page = $bot->requestGET("http://zqktlwi4fecvo6ri.onion/wiki/index.php/Main_Page");
	print "Done!\n";
	file_put_contents("index.html", $page);

	// Lets try scraping multiple pages at once!
	$sites = array(array("http://www.google.com"), array("http://www.bing.com"), array("http://www.cnn.com"), array("http://zqktlwi4fecvo6ri.onion"));
	$results = $bot->curl_multi_request($sites);
	
	foreach($results as $key => $page)
	{
		$key = str_replace(array("http://", "https://"), "", $key);
		print $key . " Len: " . strlen($page) . "\n";
		file_put_contents("$key.html", $page);
	}

	// Disable Proxy
	$bot->setProxy();

	// We can also build up a queue of URLs and either run through them individually or execute them all through batch mode!
	$bot->pushURL("http://www.reddit.com/r/circlejerk/.rss");
	$bot->pushURL("http://www.reddit.com/r/bitcoin/.rss");
	$bot->pushURL("http://www.reddit.com/r/jobs4bitcoins/.rss");

	// Check the stack size
	print "URL Stack Size: " . $bot->urlCount() . "\n";

	// Pop the top URL from the stack and execute a request
	$page = $bot->requestGET();

	// Same subreddit parser as above:
	$posts = $bot->parse_array($page, "<item>", "</item>");
	$titles = array();
	$links = array();

	for($i = 0; $i < count($posts); $i++)
	{
		$ii = $i+1;
		$titles[$i] = $bot->return_between($posts[$i], "<title>", "</title>", 1);
		$links[$i] = $bot->return_between($posts[$i], "<link>", "</link>", 1);
		print "Title #$ii: ".$titles[$i]."\n";
		print "Link #$ii: ".$links[$i]."\n";
	}

	// Check the stack size
	print "URL Stack Size: " . $bot->urlCount() . "\n";

	// Empty out the $bot->urls stack
	$results = $bot->curl_multi_request();
	
	foreach($results as $key => $page)
	{
		// Make $key a little bit nicer for a filename
		$key = str_replace(array("http://", "https://", ".rss", "www.reddit.com/r/"), "", $key);
		$key = substr($key, 0, -1);
		print $key . " Len: " . strlen($page) . "\n";
		file_put_contents("$key.html", $page);
	}

?>
