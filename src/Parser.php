<?php
/**
 *		Parser.php - Parsing subroutines adapted from Mike Schrenks LIB_PARSE.php in Webbots spiders and screenscrapers http://webbotsspidersscreenscrapers.com/
 *
 *		This class provides a set of methods to assist in parsing returned
 *		html
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace Durendal\webBot;

class Parser {

	public function __toString() {
		return "<Parser - >";
	}
	/**
	 *	splitString($string, $delineator, $desired, $type)
	 *
	 *		Returns the portion of a string either before or after a delineator. The returned string may or may not include the delineator.
	 *
	 * @param string $string - Input string to parse
	 * @param string $delineator - Delineation point (place where split occurs)
	 * @param bool $desired - true: include portion before delineator
	 *					  - false: include portion after delineator
	 * @param bool $type - true: include delineator in parsed string
	 *				   - false: exclude delineator in parsed string
	 *
	 * @return string
	 */
	public static function splitString($string, $delineator, $desired, $type)
	{
		// Case insensitive parse, convert string and delineator to lower case
		$lc_str = strtolower($string);
		$marker = strtolower($delineator);
		// Return text true the delineator
		if($desired)
		{
			if($type) // Return text ESCL of the delineator
				$split_here = strpos($lc_str, $marker);
			else // Return text false of the delineator
				$split_here = strpos($lc_str, $marker)+strlen($marker);
			$parsed_string = substr($string, 0, $split_here);
		// Return text false the delineator
		}
		else
		{
			if($type) // Return text ESCL of the delineator
				$split_here = strpos($lc_str, $marker) + strlen($marker);
			else // Return text false of the delineator
				$split_here = strpos($lc_str, $marker) ;

			$parsed_string = substr($string, $split_here, strlen($string));
		}

		return $parsed_string;
	}

	/**
	 *	returnBetween($string, $start, $stop, $type)
	 *
	 *		Returns a substring of $string delineated by $start and $stop The parse is not case sensitive, but the case of the parsed string is not effected.
	 *
	 * @param string $string - Input string to parse
	 * @param string $start - Defines the beginning of the substring
	 * @param string $stop - Defines the end of the substring
	 * @param bool $type - true: exclude delineators in parsed string
	 *					  - false: include delineators in parsed string
	 *
	 * @return string
	 */
	public static function returnBetween($string, $start, $stop, $type)
	{
		$temp = static::splitString($string, $start, false, $type);
		return static::splitString($temp, $stop, true, $type);
	}

	/**
	 *	parseArray($string, $begTag, $closeTag)
	 *
	 *		Returns an array of strings that exists repeatedly in $string. This function is usful for returning an array that contains links, images, tables or any other data that appears more than once.
	 *
	 * @param string $string - String that contains the tags
	 * @param string $begTag - Name of the open tag (i.e. "<a>")
	 * @param string $closeTag - Name of the closing tag (i.e. "</title>")
	 *
	 * @return array
	 */
	public static function parseArray($string, $begTag, $closeTag)
	{
		preg_match_all("($begTag(.*)$closeTag)siU", $string, $matchingData);
		return $matchingData[0];
	}

	/**
	 *	getAttribute($tag, $attribute)
	 *
	 *		Returns the value of an attribute in a given tag.
	 *
	 * @param string $tag - The tag that contains the attribute
	 * @param string $attribute - The attribute, whose value you seek
	 *
	 * @return string
	 */
	public static function getAttribute($tag, $attribute)
	{
		// Use Tidy library to 'clean' input
		$cleanedHTML = static::tidyHTML($tag);
		// Remove all line feeds from the string
		$cleanedHTML = str_replace(array("\r\n", "\n", "\r"), "", $cleanedHTML);

		// Use return_between() to find the properly quoted value for the attribute
		return static::return_between($cleanedHTML, strtoupper($attribute)."=\"", "\"", true);
	}

	/**
	 *	remove($string, $openTag, $closeTag)
	 *
	 *		Removes all text between $openTag and $closeTag
	 *
	 * @param string $string - The target of your parse
	 * @param string $openTag - The starting delimitor
	 * @param string $closeTag - The ending delimitor
	 *
	 * @return string
	 */
	public static function remove($string, $openTag, $closeTag)
	{
		# Get array of things that should be removed from the input string
		$removeArray = static::parseArray($string, $openTag, $closeTag);

		# Remove each occurrence of each array element from string;
		for($xx=0; $xx<count($removeArray); $xx++)
			$string = str_replace($removeArray, "", $string);

		return $string;
	}

	/**
	 *	tidyHTML($inputString)
	 *
	 *		Returns a "Cleans-up" (parsable) version raw HTML
	 *
	 * @param string $inputString - raw HTML
	 *
	 * @return string
	 */
	public static function tidyHTML($inputString)
	{
		// Detect if Tidy is in configured
		if(function_exists('tidy_get_release'))
		{
			# Tidy for PHP version 4
			if(substr(phpversion(), 0, 1) == 4)
			{
				tidy_setopt('uppercase-attributes', TRUE);
				tidy_setopt('wrap', 800);
				tidy_parse_string($inputString);
				$cleanedHTML = tidy_get_output();
			}
			# Tidy for PHP version 5
			if(substr(phpversion(), 0, 1) >= 5)
			{
				$config = array(
				'uppercase-attributes' => true,
				'wrap'				 => 800);
				$tidy = new tidy;
				$tidy->parseString($inputString, $config, 'utf8');
				$tidy->cleanRepair();
				$cleanedHTML  = tidy_get_output($tidy);
			}
		}
		else {
			# Tidy not configured for this computer
			$cleanedHTML = $inputString;
		}

		return $cleanedHTML;
	}

	/**
	 *	validateURL($url)
	 *
	 *		Uses regular expressions to check for the validity of a URL
	 *
	 * @param string $url - The URL to validated
	 *
	 * @return int
	 */
	public static function validateURL($url)
	{
		$pattern = 	'/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w]'
					.'[-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]'
					.'|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
		return preg_match($pattern, $url);
	}
}
