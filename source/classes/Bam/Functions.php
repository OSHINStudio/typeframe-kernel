<?php
/*
	Miscellaneous helper functions

	2011-02-24: created
	2012-10-01: added SimpleXml_Merge
*/

class Bam_Functions
{
	/**
	 * tells us whether an array is associative or not
	 * we immediately discount non-arrays and empty arrays
	 * we immediately confirm arrays whose first key is a non-integer
	 * otherwise, we run a more intensive test for associativeness
	 */
	public static function IsAssociative($array)
	{
		if (!is_array($array) || empty($array)) return false;
		if (!is_int(key($array))) return true;
		return (0 !== count(array_diff_key($array, array_keys($array))));
	}
	/**
	 * Generate a random alphanumeric string.
	 * @param int $len The length of the string.
	 * @return string The random string.
	 */
	public static function RandomId($len = 32) {
		// Generate a random alphanumeric string
		$seed = "abcdefghijklmnopqrtsuvwxyz0123456789";
		$result = "";
		for ($t = 0; $t < $len; $t++) {
			$x = rand(0, 35);
			$result .= substr($seed, $x, 1);
		}
		return $result;
	}
	/**
	 * Merge two SimpleXMLElements into a single object
	 * @param SimpleXMLElement $xml1
	 * @param SimpleXMLElement $xml2
	 */
	public static function SimpleXml_Merge (SimpleXMLElement &$xml1, SimpleXMLElement $xml2) {
		$dom1 = new DomDocument();
		$dom2 = new DomDocument();
		$dom1->loadXML($xml1->asXML());
		$dom2->loadXML($xml2->asXML());

		$xpath = new domXPath($dom2);
		$xpathQuery = $xpath->query('/*/*');
		for ($i = 0; $i < $xpathQuery->length; $i++) {
			$dom1->documentElement->appendChild(
			$dom1->importNode($xpathQuery->item($i), true));
		}
		$xml1 = simplexml_import_dom($dom1);
	}
	public static function GetIntro($text, $length = 255) {
		$text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
		if (strlen($text) <= $length) return $text;
		$result = substr($text, 0, $length);
		if (preg_match('/[^a-z]/', substr($text, $length, 1))) {
			return $result . '...';
		}
		$length--;
		while ( ($length > 0) && (!preg_match('/[^a-z]/', substr($result, $length, 1))) ) {
			$length--;
		}
		if ($length) {
			return substr($result, 0, $length) . '...';
		}
		return $result . '...';
	}
	public static function Markdown($text) {
		require_once(TYPEF_SOURCE_DIR . '/libraries/markdown.php');
		Markdown($text);
	}
	/**
	 * Convert a comma-separated list into an array. Trim leading and trailing
	 * whitespace from each value.
	 * @param string|array $list
	 * @return array
	 */
	public static function ListToArray($list) {
		if (is_array($list)) {
			return $list;
		}
		return preg_split('/[\s,]+/', trim($list));
	}
	public static function FileOwner($file) {
		$line = exec('ls -l ' . escapeshellarg($file), $output, $return);
		if ($return == 0) {
			$parts = explode(' ', $line);
			if (!empty($parts[2])) {
				return $parts[2];
			}
		}
		return false;
	}
	public static function WhoAmI() {
		$line = exec('whoami', $output, $return);
		if ($return == 0) {
			return $line;
		}
		return false;
	}
}
