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
 * Class NewsletterBoundaries
 *
 * Parent class for newsletter boundary content elements.
 * @copyright  David Enke 2014
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
abstract class NewsletterBoundaries extends \ContentElement {
	/**
	 * Generate the content element
	 */
	protected function compile() {
		$objNewsletter = \NewsletterModel::findByIdOrAlias($this->pid);
		$objNewsletterChannel = \NewsletterChannelModel::findByIds(array($objNewsletter->pid), array('limit' => 1));

		$href = ampersand($this->generateFrontendUrl($this->getPageDetails($objNewsletterChannel->jumpTo)->row(), '/items/' . $objNewsletter->alias));

		$this->Template->setData($objNewsletter->row());
		$this->Template->view_online = $href;

		return;
	}
}