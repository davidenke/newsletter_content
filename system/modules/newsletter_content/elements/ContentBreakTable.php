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
 * Class ContentBreakTable
 *
 * Newsletter content element "breaktable".
 * @copyright    David Enke 2015
 * @author       David Enke <post@davidenke.de>
 * @package      newsletter_content
 */
class ContentBreakTable extends \ContentElement {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_breaktable';


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
	 * Parse the template
	 * @return string
	 */
	public function generate() {
		if (TL_MODE == 'BE' && !defined('NEWSLETTER_CONTENT_PREVIEW')) {
			return 'NEWSLETTER AREA BREAK';
		}

		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile() {
		return;
	}
}