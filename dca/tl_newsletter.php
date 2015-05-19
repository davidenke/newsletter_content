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
 * Table tl_newsletter
 */
$GLOBALS['TL_DCA']['tl_newsletter']['config']['ctable'] = array('tl_content');
$GLOBALS['TL_DCA']['tl_newsletter']['config']['switchToEdit'] = true;
$GLOBALS['TL_DCA']['tl_newsletter']['config']['onload_callback'] = array(array('tl_newsletter_content', 'checkPermission'));
$GLOBALS['TL_DCA']['tl_newsletter']['config']['ondelete_callback'][] = array('tl_newsletter_content', 'removeTrackedData');
$GLOBALS['TL_DCA']['tl_newsletter']['list']['sorting']['child_record_callback'] = array('tl_newsletter_content', 'listNewsletterArticles');
$GLOBALS['TL_DCA']['tl_newsletter']['list']['operations']['edit']['href'] = 'table=tl_content';
array_insert($GLOBALS['TL_DCA']['tl_newsletter']['list']['operations'], 1, array(
	'editheader' => array(
		'label'               => &$GLOBALS['TL_LANG']['tl_newsletter']['editmeta'],
		'href'                => 'act=edit',
		'icon'                => 'header.gif'
	)
));
array_insert($GLOBALS['TL_DCA']['tl_newsletter']['list']['operations'], 99, array(
	'stats' => array(
		'label'               => &$GLOBALS['TL_LANG']['tl_newsletter']['stats'],
		'href'                => 'key=stats',
		'icon'                => 'system/modules/newsletter_content/assets/img/stats.png',
		'button_callback'     => array('tl_newsletter_content', 'showStats')
	)
));
$GLOBALS['TL_DCA']['tl_newsletter']['palettes']['default'] = str_replace(';{html_legend},content;', ',nl_date;', $GLOBALS['TL_DCA']['tl_newsletter']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['recipients'] = array(
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['rejected'] = array(
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);
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
		$strStats = '';
		$strContents = '';

		$objContents = \ContentModel::findPublishedByPidAndTable($arrRow['id'], 'tl_newsletter');
		if (!is_null($objContents)) {
			foreach ($objContents as $objContent) {
				$strContents.= $this->getContentElement($objContent->id) . '<hr>';
			}
		}

		$intTotal = $arrRow['recipients'] + $arrRow['rejected'];
//		$intTracked = NewsletterContent\Models\NewsletterTrackingModel::countTrackedByPid($arrRow['id']);
		$objTracked = NewsletterContent\Models\NewsletterTrackingModel::findTrackedInteractionsByPid($arrRow['id']);
		$intTracked = !is_null($objTracked) ? $objTracked->count() : 0;
		$intPercent = @round($intTracked / $intTotal * 100);
		$strStats = sprintf(
			$GLOBALS['TL_LANG']['tl_newsletter']['sentTo'],
			$arrRow['recipients'],
			strval($intTotal),
			strval($intTracked),
			strval($intPercent)
		);

		return '
<div class="cte_type ' . (($arrRow['sent'] && $arrRow['date']) ? 'published' : 'unpublished') . '"><strong>' . $arrRow['subject'] . '</strong> - ' . (($arrRow['sent'] && $arrRow['date']) ? sprintf($GLOBALS['TL_LANG']['tl_newsletter']['sentOn'], Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $arrRow['date'])) . '<br>' . $strStats : $GLOBALS['TL_LANG']['tl_newsletter']['notSent']) . '</div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h128' : '') . '">
' . (!$arrRow['sendText'] && strlen($strContents) ? '
' . $strContents : '' ) . '
' . nl2br_html5($arrRow['text']) . '
</div>' . "\n";

		return '<div class="tl_content_left">' . $arrRow['subject'] . ' <span style="color:#b3b3b3;padding-left:3px">[' . $arrRow['senderName'] . ' &lt;' . $arrRow['sender'] . '&gt;]</span></div>';
	}


	public function checkPermission() {
		if (Input::get('key') == 'stats') {
			// Set root IDs
			if (!is_array($this->User->newsletters) || empty($this->User->newsletters)) {
				$root = array(0);
			} else {
				$root = $this->User->newsletters;
			}

			$id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

			$objChannel = $this->Database->prepare("SELECT pid FROM tl_newsletter WHERE id=?")
										 ->limit(1)
										 ->execute($id);

			if ($objChannel->numRows < 1) {
				$this->log('Invalid newsletter ID "'.$id.'"', __METHOD__, TL_ERROR);
				$this->redirect('contao/main.php?act=error');
			}

			if (!in_array($objChannel->pid, $root)) {
				$this->log('Not enough permissions to show stats of newsletter ID "'.$id.'" of newsletter channel ID "'.$objChannel->pid.'"', __METHOD__, TL_ERROR);
				$this->redirect('contao/main.php?act=error');
			}
		} else {
			parent::checkPermission();
		}
	}


	public function showStats($row, $href, $label, $title, $icon, $attributes, $table) {
		if (!$row['sent']) {
			return '';
		}

		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}


	public function removeTrackedData(\DataContainer $dc, $intId) {
		$objTracking = NewsletterContent\Models\NewsletterTrackingModel::findByPid($dc->activeRecord->id);
		if (!is_null($objTracking)) {
			while ($objTracking->next()) {
				$objTracking->delete();
			}
		}
	}
}
