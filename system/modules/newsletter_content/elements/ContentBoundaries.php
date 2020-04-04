<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace NewsletterContent\Elements;


/**
 * Class ContentBoundaries
 *
 * Parent class for newsletter boundary content elements.
 * @copyright    David Enke 2015
 * @author       David Enke <post@davidenke.de>
 * @package      newsletter_content
 */
abstract class ContentBoundaries extends \ContentElement {


	/**
	 * Initialize the object
	 * @param object
	 * @param string
	 */
	public function __construct($objElement, $strColumn='main') {
		parent::__construct($objElement, $strColumn);

		if ($this->customTpl != '') {
			$this->strTemplate = $this->customTpl;
		}
	}

	/**
	 * Generate the content element
	 */
	protected function compile() {
		$objNewsletter = \NewsletterModel::findByIdOrAlias($this->pid);
		$objNewsletterChannel = \NewsletterChannelModel::findByIds(array($objNewsletter->pid), array('limit' => 1));

		$this->Template->setData($objNewsletter->row());

		if ($objNewsletterChannel->jumpTo) {
			$objParent = $this->getPageDetails($objNewsletterChannel->jumpTo);
			$href = ampersand($this->generateFrontendUrl($objParent->row(), sprintf((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ?  '/%s' : '/items/%s', $objNewsletter->alias)));
			//$href = ampersand($this->generateFrontendUrl($objParent->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ?  '/%s' : '/items/%s'), $objParent->language));

			$this->Template->view_online = $href;
		}

		return;
	}
}