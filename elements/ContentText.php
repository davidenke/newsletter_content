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
 * Class ContentText
 *
 * Newsletter content element "text".
 * @copyright  David Enke 2015
 * @author	 David Enke <post@davidenke.de>
 * @package	newsletter_content
 */
class ContentText extends \ContentText {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_text';


	/**
	 * Generate the content element
	 */
	protected function compile() {

		parent::compile();

		$nlc_imgattr = '';
		if ($this->floating == 'left') {
			$nlc_imgattr = ' align="left"';
		} else if ($this->floating == 'right') {
			$nlc_imgattr = ' align="right"';
		}

		$margin = unserialize($this->imagemargin);

		// outlook 07/10 kann kein margin (13 wohl auch nicht)
		$nlc_margin = '';
		if (!empty($margin['right'])) {
			$nlc_margin .= ' hspace="' . $margin['right'] . '"';
		}
		if (!empty($margin['bottom'])) {
			$nlc_margin .= ' vspace="' . $margin['bottom'] . '"';
		}

		$this->Template->nlc_imgattr = $nlc_margin . $nlc_imgattr;
	}
}