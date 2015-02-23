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
		'nl_header' => $GLOBALS['TL_DCA']['tl_content']['palettes']['default'],
		'nl_breakrow' => $GLOBALS['TL_DCA']['tl_content']['palettes']['default'],
		'nl_breaktable' => $GLOBALS['TL_DCA']['tl_content']['palettes']['default'],
		'nl_footer' => $GLOBALS['TL_DCA']['tl_content']['palettes']['default'],
		'nl_text' => $GLOBALS['TL_DCA']['tl_content']['palettes']['text'],
		'nl_image' => $GLOBALS['TL_DCA']['tl_content']['palettes']['image'],
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

	// customize fields
	$GLOBALS['TL_DCA']['tl_content']['fields']['type']['default'] = 'nl_text';

	// remove default elements
	foreach ($GLOBALS['TL_CTE'] as $k => $v) {
		if ($k != 'newsletter') {
			unset($GLOBALS['TL_CTE'][$k]);
		}
	}

} elseif (TL_MODE == 'BE') {
	unset($GLOBALS['TL_CTE']['newsletter']);
}

class tl_content_newsletter extends Backend {

	/**
	 * Import the back end user object
	 */
	public function __construct() {
		parent::__construct();
		$this->import('BackendUser', 'User');
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
}
