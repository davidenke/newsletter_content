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
 * Class NewsletterFooter
 *
 * Newsletter content element "footer".
 * @copyright  David Enke 2014
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
class NewsletterFooter extends \ContentElement {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_footer';

	/**
	 * Parse the template
	 * @return string
	 */
	public function generate() {
		if (TL_MODE == 'BE' && !defined('NEWSLETTER_CONTENT_PREVIEW')) {
			return 'NEWSLETTER FOOTER';
		}

		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile() {
		$objNewsletter = \NewsletterModel::findByIdOrAlias($this->pid);
		$objNewsletterChannel = \NewsletterChannelModel::findByIds(array($objNewsletter->pid), array('limit' => 1));

		$href = ampersand($this->generateFrontendUrl($this->getPageDetails($objNewsletterChannel->jumpTo)->row(), '/items/' . $objNewsletter->alias));
		$this->Template->view_online = $href;

		return;
	}
}