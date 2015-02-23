<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Newsletter
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace NewsletterContent\Modules;


/**
 * Class ContentReader
 *
 * Front end module "newsletter content reader".
 * @copyright  David Enke 2015
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
class ContentReader extends \ModuleNewsletterReader {

	/**
	 * Generate the module
	 */
	protected function compile() {
		global $objPage;

		$this->Template->content = '';
		$this->Template->referer = 'javascript:history.go(-1)';
		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

		if (TL_MODE == 'FE' && BE_USER_LOGGED_IN) {
			$objNewsletter = \NewsletterModel::findByIdOrAlias(\Input::get('items'));
		} else {
			$objNewsletter = \NewsletterModel::findSentByParentAndIdOrAlias(\Input::get('items'), $this->nl_channels);
		}

		if ($objNewsletter === null) {
			// Do not index or cache the page
			$objPage->noSearch = 1;
			$objPage->cache = 0;

			// Send a 404 header
			header('HTTP/1.1 404 Not Found');
			$this->Template->content = '<p class="error">' . sprintf($GLOBALS['TL_LANG']['MSC']['invalidPage'], \Input::get('items')) . '</p>';
			return;
		}

		// Overwrite the page title (see #2853 and #4955)
		if ($objNewsletter->subject != '') {
			$objPage->pageTitle = strip_tags(strip_insert_tags($objNewsletter->subject));
		}

		// Add enclosure
		if ($objNewsletter->addFile) {
			$this->addEnclosuresToTemplate($this->Template, $objNewsletter->row(), 'files');
		}

		if (!$objNewsletter->sendText) {
			$nl2br = ($objPage->outputFormat == 'xhtml') ? 'nl2br_xhtml' : 'nl2br_html5';
			$strContent = '';
			$objContentElements = \ContentModel::findPublishedByPidAndTable($objNewsletter->id, 'tl_newsletter');

			if ($objContentElements !== null) {
				if (!defined('NEWSLETTER_CONTENT_PREVIEW')) {
					define('NEWSLETTER_CONTENT_PREVIEW', true);
				}
				foreach ($objContentElements as $objContentElement) {
					$strContent.= $this->getContentElement($objContentElement->id);
				}
			}
			
			// Parse simple tokens and insert tags
			$strContent = $this->replaceInsertTags($strContent);
			$strContent = \String::parseSimpleTokens($strContent, array());
	
			// Encode e-mail addresses
			$strContent = \String::encodeEmail($strContent);

			$this->Template->content = $strContent;
		} else {
			$strContent = str_ireplace(' align="center"', '', $objNewsletter->content);
		}

		// Parse simple tokens and insert tags
		$strContent = $this->replaceInsertTags($strContent);
		$strContent = \String::parseSimpleTokens($strContent, array());

		// Encode e-mail addresses
		$strContent = \String::encodeEmail($strContent);

		$this->Template->content = $strContent;
		$this->Template->subject = $objNewsletter->subject;
	}
}
