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
 * Class NewsletterContentForm
 *
 * Newsletter content element "form".
 * @copyright  David Enke 2014
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
class NewsletterContentForm extends \ContentElement {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_ce_form';


	/**
	 * Generate the content element
	 */
	protected function compile() {
		$strForm = $this->getForm($this->form);
		$strForm = preg_replace("/(action\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)/", '$1' . \Environment::get('base') . '$2$3', $strForm);

		$this->Template->form = $strForm;
	}
}
