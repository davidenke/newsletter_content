<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Newsletter_content
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
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
	'NewsletterContent\Classes\NewsletterContent'      => 'system/modules/newsletter_content/classes/NewsletterContent.php',

	// Elements
	'NewsletterContent\Elements\ContentBoundaries'     => 'system/modules/newsletter_content/elements/ContentBoundaries.php',
	'NewsletterContent\Elements\ContentBreakRow'       => 'system/modules/newsletter_content/elements/ContentBreakRow.php',
	'NewsletterContent\Elements\ContentBreakTable'     => 'system/modules/newsletter_content/elements/ContentBreakTable.php',
	'NewsletterContent\Elements\ContentEvents'         => 'system/modules/newsletter_content/elements/ContentEvents.php',
	'NewsletterContent\Elements\ContentFooter'         => 'system/modules/newsletter_content/elements/ContentFooter.php',
	'NewsletterContent\Elements\ContentForm'           => 'system/modules/newsletter_content/elements/ContentForm.php',
	'NewsletterContent\Elements\ContentHeader'         => 'system/modules/newsletter_content/elements/ContentHeader.php',
	'NewsletterContent\Elements\ContentImage'          => 'system/modules/newsletter_content/elements/ContentImage.php',
	'NewsletterContent\Elements\ContentIncludes'       => 'system/modules/newsletter_content/elements/ContentIncludes.php',
	'NewsletterContent\Elements\ContentNews'           => 'system/modules/newsletter_content/elements/ContentNews.php',
	'NewsletterContent\Elements\ContentText'           => 'system/modules/newsletter_content/elements/ContentText.php',

	// Modules
	'NewsletterContent\Modules\ModuleNewsletterReader' => 'system/modules/newsletter_content/modules/ModuleNewsletterReader.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mail_default'  => 'system/modules/newsletter_content/templates',
	'nl_breakrow'   => 'system/modules/newsletter_content/templates',
	'nl_breaktable' => 'system/modules/newsletter_content/templates',
	'nl_events'     => 'system/modules/newsletter_content/templates',
	'nl_footer'     => 'system/modules/newsletter_content/templates',
	'nl_form'       => 'system/modules/newsletter_content/templates',
	'nl_header'     => 'system/modules/newsletter_content/templates',
	'nl_image'      => 'system/modules/newsletter_content/templates',
	'nl_news'       => 'system/modules/newsletter_content/templates',
	'nl_text'       => 'system/modules/newsletter_content/templates',
));
