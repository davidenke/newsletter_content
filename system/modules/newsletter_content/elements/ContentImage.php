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
 * Class ContentImage
 *
 * Newsletter content element "image".
 * @copyright    David Enke 2015
 * @author       David Enke <post@davidenke.de>
 * @package      newsletter_content
 */
class ContentImage extends \ContentImage {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_image';


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
		$this->addImageToTemplate($this->Template, $this->arrData, \Config::get('maxImageWidth'));
	}
}