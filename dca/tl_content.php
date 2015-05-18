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
 * Dynamically add the permission check and parent table
 */
if ($this->Input->get('do') == 'newsletter' || (\Input::get('table') == 'tl_content' && \Input::get('field') == 'type')) {
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_newsletter';
	$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content_newsletter', 'checkPermission');
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['headerFields'] = array('subject', 'alias', 'useSMTP');

	// copy default palettes
	$arrPalettes = array(
		'nl_header' => '{type_legend},type;{expert_legend:hide},cssID,space;{invisible_legend:hide},invisible,start,stop',
		'nl_breakrow' => '{type_legend},type;{expert_legend:hide},cssID,space;{invisible_legend:hide},invisible,start,stop',
		'nl_breaktable' => '{type_legend},type;{expert_legend:hide},cssID,space;{invisible_legend:hide},invisible,start,stop',
		'nl_footer' => '{type_legend},type;{expert_legend:hide},cssID,space;{invisible_legend:hide},invisible,start,stop',
		'nl_text' => $GLOBALS['TL_DCA']['tl_content']['palettes']['text'],
		'nl_image' => $GLOBALS['TL_DCA']['tl_content']['palettes']['image'],
//		'nl_gallery' => $GLOBALS['TL_DCA']['tl_content']['palettes']['gallery'],
		'nl_gallery' => '{type_legend},type,headline;{image_legend},images,perRow,numberOfItems;{expert_legend:hide},cssID,space;{invisible_legend:hide},invisible,start,stop',
		'nl_news' => '{type_legend},type,headline;{include_legend},include_type;{expert_legend:hide},cssID,space;{invisible_legend:hide},invisible,start,stop',
		'nl_events' => '{type_legend},type,headline;{include_legend},include_type;{expert_legend:hide},cssID,space;{invisible_legend:hide},invisible,start,stop',
		'nl_form' => $GLOBALS['TL_DCA']['tl_content']['palettes']['form']
	);

	// add palettes
	foreach ($arrPalettes as $k => $strPalette) {
		$GLOBALS['TL_DCA']['tl_content']['palettes'][$k] = str_replace(
			array(
				',guests,',
				',fullsize,',
				'{template_legend:hide},customTpl;{protected_legend:hide},protected;'
			),
			array(
				',',
				',',
				''
			),
			$strPalette
		);
	}
	$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'include_type';
	$GLOBALS['TL_DCA']['tl_content']['subpalettes']['include_type_archives'] = 'include_archives,sortOrder';
	$GLOBALS['TL_DCA']['tl_content']['subpalettes']['include_type_items'] = 'include_items';

	// customize fields
	$GLOBALS['TL_DCA']['tl_content']['fields']['type']['default'] = 'nl_text';
	$GLOBALS['TL_DCA']['tl_content']['fields']['customTpl']['options_callback'] = array('tl_content_newsletter', 'getNewsletterElementTemplates');

	// remove default elements
	foreach ($GLOBALS['TL_CTE'] as $k => $v) {
		if ($k != 'newsletter') {
			unset($GLOBALS['TL_CTE'][$k]);
		}
	}

} elseif (TL_MODE == 'BE') {
	unset($GLOBALS['TL_CTE']['newsletter']);
}

$GLOBALS['TL_DCA']['tl_content']['fields']['include_type'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['include_type'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'default'                 => 'archives',
	'options'                 => array('archives', 'items'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_content']['include_types'],
	'eval'                    => array('chosen'=>true, 'submitOnChange'=>true, 'mandatory'=>true),
	'sql'                     => "varchar(32) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_content']['fields']['include_archives'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['include_archives'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'options_callback'        => array('tl_content_newsletter', 'getIncludeArchives'),
	'eval'                    => array('multiple'=>true, 'mandatory'=>true),
	'sql'                     => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_content']['fields']['include_items'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['include_items'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'options_callback'        => array('tl_content_newsletter', 'getIncludeItems'),
	'eval'                    => array('multiple'=>true, 'mandatory'=>true),
	'sql'                     => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_content']['fields']['images'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['images'],
	'exclude'                 => true,
	'inputType'               => 'multiColumnWizard',
	'eval'                    => array(
		'mandatory'               => true,
		'minCount'                => 2,
		'buttonPos'               => 'middle',
		'columnFields'            => array(
			'singleSRC'               => array(
				'label'                   => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
				'exclude'                 => true,
				'inputType'               => 'fileTree',
				'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'extensions'=>\Config::get('validImageTypes'))
			),
			'alt' => array
			(
				'label'                   => &$GLOBALS['TL_LANG']['tl_content']['alt'],
				'exclude'                 => true,
				'search'                  => true,
				'inputType'               => 'text',
				'eval'                    => array('maxlength'=>255, 'columnPos'=>2, 'style'=>'width:90%'),
			),
			'imageUrl' => array
			(
				'label'                   => &$GLOBALS['TL_LANG']['tl_content']['imageUrl'],
				'exclude'                 => true,
				'search'                  => true,
				'inputType'               => 'text',
				'eval'                    => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'wizard', 'columnPos'=>2, 'style'=>'width:90%'),
				'wizard' => array
				(
					array('tl_content', 'pagePicker')
				)
			),
			'caption' => array
			(
				'label'                   => &$GLOBALS['TL_LANG']['tl_content']['caption'],
				'exclude'                 => true,
				'search'                  => true,
				'inputType'               => 'text',
				'eval'                    => array('maxlength'=>255, 'allowHtml'=>true, 'columnPos'=>2, 'style'=>'width:90%')
			)
		)
	),
	'sql'                     => "blob NULL"
);

class tl_content_newsletter extends Backend {

	/**
	 * Import the back end user object
	 */
	public function __construct() {
		parent::__construct();
		$this->import('BackendUser', 'User');

		$GLOBALS['TL_CSS'][] = 'system/modules/newsletter_content/assets/css/multicolumnwizard.css';
	}


	/**
	 * Check permissions to edit table tl_content
	 */
	public function checkPermission() {
		if ($this->User->isAdmin) {
			return;
		}

		// Set the root IDs
		if (!is_array($this->User->newsletters) || empty($this->User->newsletters)) {
			$root = array(0);
		}
		else {
			$root = $this->User->newsletters;
		}

		//$id = strlen($this->Input->get('id')) ? $this->Input->get('id') : CURRENT_ID;

		// Check the current action
		switch ($this->Input->get('act'))
		{
			case 'paste':
				// Allow
				break;

			case '': // empty
			case 'create':
			case 'select':
				// Check access to the news item
				if (!$this->checkAccessToElement(CURRENT_ID, $root, true))
				{
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
			case 'cutAll':
			case 'copyAll':
				// Check access to the parent element if a content element is moved
				if (($this->Input->get('act') == 'cutAll' || $this->Input->get('act') == 'copyAll') && !$this->checkAccessToElement($this->Input->get('pid'), $root, ($this->Input->get('mode') == 2)))
				{
					$this->redirect('contao/main.php?act=error');
				}

				$objCes = $this->Database->prepare("SELECT id FROM tl_content WHERE ptable='tl_newsletter' AND pid=?")
										 ->execute(CURRENT_ID);

				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objCes->fetchEach('id'));
				$this->Session->setData($session);
				break;

			case 'cut':
			case 'copy':
				// Check access to the parent element if a content element is moved
				if (!$this->checkAccessToElement($this->Input->get('pid'), $root, ($this->Input->get('mode') == 2)))
				{
					$this->redirect('contao/main.php?act=error');
				}
				// NO BREAK STATEMENT HERE

			default:
				// Check access to the content element
				if (!$this->checkAccessToElement($this->Input->get('id'), $root))
				{
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * Check access to a particular content element
	 * @param integer
	 * @param array
	 * @param boolean
	 * @return boolean
	 */
	protected function checkAccessToElement($id, $root, $blnIsPid=false) {
		if ($blnIsPid) {
			$objArchive = $this->Database->prepare("SELECT a.id, n.id AS nid FROM tl_newsletter n, tl_newsletter_channel a WHERE n.id=? AND n.pid=a.id")
										 ->limit(1)
										 ->execute($id);
		}
		else {
			$objArchive = $this->Database->prepare("SELECT a.id, n.id AS nid FROM tl_content c, tl_newsletter n, tl_newsletter_channel a WHERE c.id=? AND c.pid=n.id AND n.pid=a.id")
										 ->limit(1)
										 ->execute($id);
		}

		// Invalid ID
		if ($objArchive->numRows < 1) {
			$this->log('Invalid newsletter content element ID ' . $id, __METHOD__, TL_ERROR);
			return false;
		}

		// The news archive is not mounted
		if (!in_array($objArchive->id, $root)) {
			$this->log('Not enough permissions to modify article ID ' . $objArchive->nid . ' in newsletter channel ID ' . $objArchive->id, __METHOD__, TL_ERROR);
			return false;
		}

		return true;
	}


	/**
	 * Return all newsletter content element templates as array
	 * @return array
	 */
	public function getNewsletterElementTemplates() {
		return $this->getTemplateGroup('nl_');
	}


	public function getIncludeArchives(\DataContainer $dc) {
		$arrReturn = array();
		$strTable = '';
		$strTitleKey = '';
		$strPatternUrl = '%s';

		if (!$dc->activeRecord->type) {
			return $arrReturn;
		}

		switch ($dc->activeRecord->type) {
			case 'nl_news':
				$strTable = 'tl_news_archive';
				$strTitleKey = 'title';
				$strPatternUrl = 'contao/main.php?do=news&id=%s&act=edit&popup=1&nb=1&rt=%s';
				$objArchives = \NewsArchiveModel::findAll(array('order'=>$strTable . '.' . $strTitleKey));
				break;

			case 'nl_events':
				$strTable = 'tl_calendar';
				$strTitleKey = 'title';
				$strPatternUrl = 'contao/main.php?do=calendar&id=%s&act=edit&popup=1&nb=1&rt=%s';
				$objArchives = \CalendarModel::findAll(array('order'=>$strTable . '.' . $strTitleKey));
				break;

			default:
				return $arrReturn;
				break;
		}

		if (!is_null($objArchives)) {
			foreach ($objArchives as $objArchive) {
				$strDo = ampersand(sprintf($strPatternUrl, $objArchive->id, REQUEST_TOKEN));

				$arrReturn[$objArchive->id] = sprintf(
					'<strong><a href="%s" title="%s" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'%s\',\'url\':this.href});return false">%s</a></strong>',
					$strDo,
					sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $objArchive->id),
					sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $objArchive->id),
					$objArchive->$strTitleKey
				);
			}
		}

		return $arrReturn;
	}


	public function getIncludeItems(\DataContainer $dc) {
		$arrReturn = array();
		$strTable = '';
		$strDateKey = '';
		$strTitleKeyArchive = '';
		$strTitleKeyItem = '';
		$strPatternArchiveUrl = '%s';
		$strPatternItemUrl = '%s';

		if (!$dc->activeRecord->type) {
			return $arrReturn;
		}

		switch ($dc->activeRecord->type) {
			case 'nl_news':
				$strTable = 'tl_news';
				$strDateKey = 'date';
				$strTitleKeyItem = 'headline';
				$strTitleKeyArchive = 'title';
				$strPatternArchiveUrl = 'contao/main.php?do=news&id=%s&act=edit&popup=1&nb=1&rt=%s';
				$strPatternItemUrl    = 'contao/main.php?do=news&id=%s&act=edit&popup=1&nb=1&rt=%s&table=%s';
				$objItems = \NewsModel::findAll(array('order'=>$strTable . '.' . $strDateKey));
				break;

			case 'nl_events':
				$strTable = 'tl_calendar_events';
				$strDateKey = 'startDate';
				$strTitleKeyItem = 'title';
				$strTitleKeyArchive = 'title';
				$strPatternArchiveUrl = 'contao/main.php?do=calendar&id=%s&act=edit&popup=1&nb=1&rt=%s';
				$strPatternItemUrl    = 'contao/main.php?do=calendar&id=%s&act=edit&popup=1&nb=1&rt=%s&table=%s';
				$objItems = \CalendarEventsModel::findAll(array('order'=>$strTable . '.' . $strDateKey));
				break;

			default:
				return $arrReturn;
				break;
		}

		if (!is_null($objItems)) {
			foreach ($objItems as $objItem) {
				$objArchive = $objItem->getRelated('pid');
				$strDoArchive = ampersand(sprintf($strPatternArchiveUrl, $objItem->pid, REQUEST_TOKEN));
				$strDoItem = ampersand(sprintf($strPatternItemUrl, $objItem->id, REQUEST_TOKEN, $strTable));
				$strDateField = \Date::parse(\Config::get('dateFormat') ?: 'd.m.Y', $objItem->$strDateKey) . ' - ';

				$time = time();
				//"($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
				$blnPublished = (!$objItem->start || $objItem->start < $time) && (!$objItem->stop || $objItem->stop > $time) && $objItem->published;

				$arrReturn[$objItem->id] = sprintf(
					'%s<strong><a%s href="%s" title="%s" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'%s\',\'url\':this.href});return false">%s</a></strong> (<a href="%s" title="%s" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'%s\',\'url\':this.href});return false">%s</a>)',
					$strDateField,
					$blnPublished ? '' : ' style="color:#c33"',
					$strDoItem,
					sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $objItem->id),
					sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $objItem->id),
					$objItem->$strTitleKeyItem,
					$strDoArchive,
					sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $objArchive->id),
					sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $objArchive->id),
					$objArchive->$strTitleKeyArchive
				);
			}
		}

		return $arrReturn;
	}
}
