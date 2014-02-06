<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Newsletter_content
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Elements
	'Contao\NewsletterBreakTable'          => 'system/modules/newsletter_content/elements/NewsletterBreakTable.php',
	'Contao\NewsletterContentImage'        => 'system/modules/newsletter_content/elements/NewsletterContentImage.php',
	'Contao\NewsletterBreakRow'            => 'system/modules/newsletter_content/elements/NewsletterBreakRow.php',
	'Contao\NewsletterContentText'         => 'system/modules/newsletter_content/elements/NewsletterContentText.php',
	'Contao\NewsletterFooter'              => 'system/modules/newsletter_content/elements/NewsletterFooter.php',
	'Contao\NewsletterHeader'              => 'system/modules/newsletter_content/elements/NewsletterHeader.php',

	// Classes
	'Contao\NewsletterContent'             => 'system/modules/newsletter_content/classes/NewsletterContent.php',

	// Modules
	'Contao\ModuleNewsletterContentReader' => 'system/modules/newsletter_content/modules/ModuleNewsletterContentReader.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'nl_header'     => 'system/modules/newsletter_content/templates',
	'nl_breaktable' => 'system/modules/newsletter_content/templates',
	'nl_breakrow'   => 'system/modules/newsletter_content/templates',
	'nl_ce_text'    => 'system/modules/newsletter_content/templates',
	'mail_default'  => 'system/modules/newsletter_content/templates',
	'nl_footer'     => 'system/modules/newsletter_content/templates',
	'nl_ce_image'   => 'system/modules/newsletter_content/templates',
));
