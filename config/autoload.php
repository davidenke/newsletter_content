<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 */

/**
 * @package newsletter_content
 *
 * @copyright  David Enke 2015
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'NewsletterContent',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'NewsletterContent\Classes\NewsletterContent' => 'system/modules/newsletter_content/classes/NewsletterContent.php',

	// Elements
	'NewsletterContent\Elements\Boundaries'       => 'system/modules/newsletter_content/elements/Boundaries.php',
	'NewsletterContent\Elements\BreakRow'         => 'system/modules/newsletter_content/elements/BreakRow.php',
	'NewsletterContent\Elements\BreakTable'       => 'system/modules/newsletter_content/elements/BreakTable.php',
	'NewsletterContent\Elements\Footer'           => 'system/modules/newsletter_content/elements/Footer.php',
	'NewsletterContent\Elements\Form'             => 'system/modules/newsletter_content/elements/Form.php',
	'NewsletterContent\Elements\Header'           => 'system/modules/newsletter_content/elements/Header.php',
	'NewsletterContent\Elements\Image'            => 'system/modules/newsletter_content/elements/Image.php',
	'NewsletterContent\Elements\Text'             => 'system/modules/newsletter_content/elements/Text.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mail_default'  => 'system/modules/newsletter_content/templates',
	'nl_breakrow'   => 'system/modules/newsletter_content/templates',
	'nl_breaktable' => 'system/modules/newsletter_content/templates',
	'nl_ce_form'    => 'system/modules/newsletter_content/templates',
	'nl_ce_image'   => 'system/modules/newsletter_content/templates',
	'nl_ce_text'    => 'system/modules/newsletter_content/templates',
	'nl_footer'     => 'system/modules/newsletter_content/templates',
	'nl_header'     => 'system/modules/newsletter_content/templates',
));
