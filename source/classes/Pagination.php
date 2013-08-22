<?php
class Pagination {
	/**
	 * Return values used to paginate a URL, e.g., /story.html?page=2
	 * @param int $records The number of records being paginated (e.g., the number of images in a photo album)
	 * @param int $perpage The number of records to display per page
	 * @param int $page The current page number (the "page" value from the HTTP request if null)
	 * @return array An associative array containing 'page', 'totalrecords', 'perpage', 'totalpages', 'limit', and 'pagedurl'
	 */
	public static function Calculate($records, $perpage = 20, $page = null, $url = null) {
		$result = array();
		if (is_null($page)) {
			$result['page'] = (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1);
		} else {
			$result['page'] = $page;
		}
		if ($result['page'] < 1) $result['page'] = 1;
		$result['totalrecords'] = $records;
		$result['perpage'] = $perpage;
		if ($perpage > 0) {
			$result['totalpages'] = ceil($records / $perpage);
			if ($result['totalpages'] < 1) $result['totalpages'] = 1;
		} else {
			$result['totalpages'] = 1;
		}
		if ($result['page'] > $result['totalpages']) $result['page'] = $result['totalpages'];
		$result['limit'] = ( ($result['page'] - 1) * $result['perpage'] ) . ', ' . $result['perpage'];
		if (is_null($url)) {
			$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$criteria = $_GET;
			unset($criteria['page']);
			$result['pagedurl'] = $url . (count($criteria) ? '?' . http_build_query($criteria) : '');
		} else {
			$result['pagedurl'] = $url;
		}
		return $result;
	}
}
