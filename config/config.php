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
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['newsletter']['tables'][] = 'tl_content';
$GLOBALS['BE_MOD']['content']['newsletter']['send'] = array('NewsletterContent\Classes\NewsletterContent', 'send');


/**
 * Frond end modules
 */
$GLOBALS['FE_MOD']['newsletter']['nl_reader'] = 'NewsletterContent\Modules\ContentReader';


/**
 * Newsletter elements
 */
array_insert($GLOBALS['TL_CTE'], 10, array(
	'newsletter' => array(
		'nl_header'          => 'NewsletterContent\Elements\Header',
		'nl_breakrow'        => 'NewsletterContent\Elements\BreakRow',
		'nl_breaktable'      => 'NewsletterContent\Elements\BreakTable',
		'nl_footer'          => 'NewsletterContent\Elements\Footer',
		'nl_text'            => 'NewsletterContent\Elements\Text',
		'nl_image'           => 'NewsletterContent\Elements\Image',
		'nl_news'            => 'NewsletterContent\Elements\News',
		'nl_events'          => 'NewsletterContent\Elements\Events',
		'nl_form'            => 'NewsletterContent\Elements\Form'
	)
));
