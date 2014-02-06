<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Newsletter
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace Contao;


/**
 * Class ModuleNewsletterContentReader
 *
 * Front end module "newsletter content reader".
 * @copyright  David Enke 2014
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
class ModuleNewsletterContentReader extends \ModuleNewsletterReader {

	/**
	 * Generate the module
	 */
	protected function compile() {
		parent::compile();

		$objNewsletter = \NewsletterModel::findSentByParentAndIdOrAlias(\Input::get('items'), $this->nl_channels);

		if (!$objNewsletter->sendText) {
			$strContent = '';
			$objContentElements = \ContentModel::findPublishedByPidAndTable($objNewsletter->id, 'tl_newsletter');

			if ($objContentElements !== null) {
				if (!defined('NEWSLETTER_CONTENT_PREVIEW')) {
					define('NEWSLETTER_CONTENT_PREVIEW', true);
				}
				while ($objContentElements->next()) {
					$strContent.= $this->getContentElement($objContentElements->id);
				}
			}
			
			// Parse simple tokens and insert tags
			$strContent = $this->replaceInsertTags($strContent);
			$strContent = \String::parseSimpleTokens($strContent, array());
	
			// Encode e-mail addresses
			$strContent = \String::encodeEmail($strContent);

			$this->Template->content = $strContent;
		}
	}
}
