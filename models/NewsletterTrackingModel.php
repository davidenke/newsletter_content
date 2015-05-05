<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace NewsletterContent\Models;


/**
 * Reads and writes newsletters
 *
 * @author David Enke <post@davidenke.de>
 */
class NewsletterTrackingModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_newsletter_tracking';


	/**
	 * Count tracking items by their parent ID and email
	 *
	 * @param integer $intPid      The newsletter ID
	 * @param boolean $strEmail    The tracking email address
	 * @param array   $arrOptions  An optional options array
	 *
	 * @return integer The number of newsletter tracking items
	 */
	public static function countTrackedByPidAndEmail($intPid, $strEmail, array $arrOptions=array())
	{
		if (!$intPid || !$strEmail)
		{
			return 0;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid=? AND $t.email=?");
		$arrValues = array($intPid, $strEmail);

		return static::countBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Count tracking hits by their parent ID
	 *
	 * @param integer $intPid      The newsletter ID
	 * @param array   $arrOptions  An optional options array
	 *
	 * @return integer The number of newsletter tracking items
	 */
	public static function countTrackedByPid($intPid, array $arrOptions=array())
	{
		if (!$intPid)
		{
			return 0;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid=?");
		$arrValues = array($intPid);

		if (!isset($arrOptions['group']))
		{
			$arrOptions['group'] = "$t.email";
		}

		return static::countBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Count tracking hits by their parent ID
	 *
	 * @param integer $intPid      The newsletter ID
	 * @param array   $arrOptions  An optional options array
	 *
	 * @return integer The number of newsletter tracking items
	 */
	public static function findTrackedByPid($intPid, array $arrOptions=array())
	{
		if (!$intPid)
		{
			return 0;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid=?");
		$arrValues = array($intPid);

		if (!isset($arrOptions['group']))
		{
			$arrOptions['group'] = "$t.email";
		}

		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find newsletters tracking data by their newsletter ID
	 *
	 * @param integer $intPid     The newsletter ID
	 * @param array   $arrOptions An optional options array
	 *
	 * @return \Model\Collection|null A collection of models or null if there are no sent newsletters
	 */
	public static function findByPid($intPid, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = array("$t.pid=?");

		return static::findBy($arrColumns, $intPid, $arrOptions);
	}
}
