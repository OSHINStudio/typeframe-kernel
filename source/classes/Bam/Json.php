<?php
/*
	BAM JSON class

	requires requestIsAjax() and Typeframe::Redirect()
*/

class Bam_Json
{	/*
		constants and variables
	*/

	const FAILURE = -1;
	const NO_DATA =  0;
	const SUCCESS =  1;

	/*
		private static functions
	*/

	private static function _addRedirectToData($data, $redirect)
	{
		if (!is_null($redirect))
		{
			if (is_null($data)) $data = array();
			$data['redirect'] = $redirect;
		}
		return $data;
	}

	/*
		public static functions
	*/

	public static function Encode($message, $data, $status)
	{
		$result = (is_array($data) ? $data : array('data' => $data));
		$result['message'] = $message;
		$result['status']  = $status;
		return json_encode($result);
	}

	public static function NoData($message, $data = null)
	{
		return self::Encode($message, $data, self::NO_DATA);
	}

	public static function Success($message, $data = null)
	{
		return self::Encode($message, $data, self::SUCCESS);
	}

	public static function Failure($message, $data = null)
	{
		return self::Encode($message, $data, self::FAILURE);
	}

	public static function NoDataOrRedirect($message, $redirect = null, $data = null)
	{
		if (requestIsAjax())
			die(self::NoData($message, self::_addRedirectToData($data, $redirect)));

		Typeframe::Redirect($message, $url);
	}

	public static function SuccessOrRedirect($message, $redirect = null, $data = null)
	{
		if (requestIsAjax())
			die(self::Success($message, self::_addRedirectToData($data, $redirect)));

		Typeframe::Redirect($message, $url);
	}

	public static function FailureOrRedirect($message, $redirect = null, $data = null)
	{
		if (requestIsAjax())
			die(self::Failure($message, self::_addRedirectToData($data, $redirect)));

		Typeframe::Redirect($message, $redirect);
	}
}
