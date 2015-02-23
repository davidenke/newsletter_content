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
 * Class Header
 *
 * Newsletter content element "header".
 * @copyright  David Enke 2015
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
class Header extends Boundaries {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_header';

	/**
	 * Parse the template
	 * @return string
	 */
	public function generate() {
		if (TL_MODE == 'BE' && !defined('NEWSLETTER_CONTENT_PREVIEW')) {
			return 'NEWSLETTER HEADER';
		}

		return parent::generate();
	}
}