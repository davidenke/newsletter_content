<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Core
 * @link	https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace NewsletterContent\Elements;


/**
 * Class ContentNews
 *
 * Newsletter content element "news".
 * @copyright    David Enke 2015
 * @author       David Enke <post@davidenke.de>
 * @package      newsletter_content
 */
class ContentNews extends ContentIncludes {


	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_news';


	/**
	 * Generate the content element
	 */
	protected function compile() {
		$arrItems = array();
		$t = 'tl_news';

		if ($this->include_type == 'archives') {
			$arrArchiveIds = deserialize($this->include_archives, true);
			$strSortOrder = $this->sortOrder == 'ascending' ? 'ASC' : 'DESC';

			if (sizeof($arrArchiveIds)) {
				$objItems = \NewsModel::findPublishedByPids($arrArchiveIds, null, 0, 0, array('order'=>'date ' . $strSortOrder));
			}
		} else {
			$arrItemIds = deserialize($this->include_items, true);

			if (sizeof($arrItemIds)) {
				$arrItems = array_map(function() { return ''; }, array_flip($arrItemIds));
				$arrColumns = array("$t.id IN(" . implode(',', array_map('intval', $arrItemIds)) . ")");

				if (!BE_USER_LOGGED_IN)
				{
					$time = time();
					$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
				}

				$objItems = \NewsModel::findBy($arrColumns, null);
			}
		}

		if (!is_null($objItems)) {
			while ($objItems->next()) {
				$objReaderPage = \PageModel::findById($objItems->getRelated('pid')->jumpTo);

				$arrItem = $objItems->row();
				$arrItem['date'] = \Date::parse('Y-m-d', $objItems->date);
				$arrItem['dateReadable'] = \Date::parse(\Config::get('dateFormat') ?: 'Y-m-d', $objItems->date);
				$arrItem['href'] = ampersand($this->generateFrontendUrl($objReaderPage->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ?  '/' : '/items/') . ((!\Config::get('disableAlias') && $objItems->alias != '') ? $objItems->alias : $objItems->id)));

				$arrItems[$objItems->id] = $arrItem;
			}
		}

		$this->Template->items = array_filter($arrItems);
	}
}
