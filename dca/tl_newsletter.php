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
 * Table tl_newsletter
 */
$GLOBALS['TL_DCA']['tl_newsletter']['config']['ctable'] = array('tl_content');
$GLOBALS['TL_DCA']['tl_newsletter']['config']['switchToEdit'] = true;
$GLOBALS['TL_DCA']['tl_newsletter']['list']['sorting']['child_record_callback'] = array('tl_newsletter_content', 'listNewsletterArticles');
$GLOBALS['TL_DCA']['tl_newsletter']['list']['operations']['edit']['href'] = 'table=tl_content';
array_insert($GLOBALS['TL_DCA']['tl_newsletter']['list']['operations'], 1, array(
	'editheader' => array(
		'label'               => &$GLOBALS['TL_LANG']['tl_newsletter']['editmeta'],
		'href'                => 'act=edit',
		'icon'                => 'header.gif'
	)
));
$GLOBALS['TL_DCA']['tl_newsletter']['palettes']['default'] = str_replace(';{html_legend},content;', ',nl_date;', $GLOBALS['TL_DCA']['tl_newsletter']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['nl_date'] = array(
	'exclude'                 => true,
	'label'                   => &$GLOBALS['TL_LANG']['tl_newsletter']['nl_date'],
	'inputType'               => 'text',
	'eval'                    => array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
	'sql'                     => "varchar(10) NOT NULL default ''"
);


class tl_newsletter_content extends tl_newsletter {

	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function listNewsletterArticles($arrRow) {
		$strContents = '';
		$objContents = \ContentModel::findPublishedByPidAndTable($arrRow['id'], 'tl_newsletter');

		if ($objContents !== null) {
			while ($objContents->next()) {
				$strContents.= $this->getContentElement($objContents->id) . '<hr>';
			}
		}

		return '
<div class="cte_type ' . (($arrRow['sent'] && $arrRow['date']) ? 'published' : 'unpublished') . '"><strong>' . $arrRow['subject'] . '</strong> - ' . (($arrRow['sent'] && $arrRow['date']) ? sprintf($GLOBALS['TL_LANG']['tl_newsletter']['sentOn'], Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $arrRow['date'])) : $GLOBALS['TL_LANG']['tl_newsletter']['notSent']) . '</div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h128' : '') . '">
' . (!$arrRow['sendText'] && strlen($strContents) ? '
' . $strContents : '' ) . '
' . nl2br_html5($arrRow['text']) . '
</div>' . "\n";

		return '<div class="tl_content_left">' . $arrRow['subject'] . ' <span style="color:#b3b3b3;padding-left:3px">[' . $arrRow['senderName'] . ' &lt;' . $arrRow['sender'] . '&gt;]</span></div>';
	}
}
