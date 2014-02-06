<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace Contao;


/**
 * Class NewsletterContentText
 *
 * Newsletter content element "text".
 * @copyright  David Enke 2014
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
class NewsletterContentText extends \ContentText {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_ce_text';


	/**
	 * Generate the content element
	 */
	protected function compile() {
		return parent::compile();
	}
}