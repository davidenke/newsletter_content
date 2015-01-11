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
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['newsletter']['tables'][] = 'tl_content';
$GLOBALS['BE_MOD']['content']['newsletter']['send'] = array('NewsletterContent', 'send');


/**
 * Frond end modules
 */
$GLOBALS['FE_MOD']['newsletter']['nl_reader'] = 'ModuleNewsletterContentReader';


/**
 * Newsletter elements
 */
array_insert($GLOBALS['TL_CTE'], 10, array(
	'newsletter' => array(
		'nl_header'          => 'NewsletterHeader',
		'nl_breakrow'        => 'NewsletterBreakRow',
		'nl_breaktable'      => 'NewsletterBreakTable',
		'nl_footer'          => 'NewsletterFooter',
		'nl_text'            => 'NewsletterContentText',
		'nl_image'           => 'NewsletterContentImage',
		'nl_form'            => 'NewsletterContentForm'
	)
));
