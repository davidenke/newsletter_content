<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package News
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Dynamically add the permission check and parent table
 */

if (\Input::get('do') == 'newsletter' || strpos($_SERVER['PHP_SELF'], 'help.php') !== false) {
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_newsletter';
	$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content_newsletter', 'checkPermission');
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['headerFields'] = array('subject', 'alias', 'useSMTP');

	$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_text'] = $GLOBALS['TL_DCA']['tl_content']['palettes']['text'];
	$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_image'] = $GLOBALS['TL_DCA']['tl_content']['palettes']['image'];
	$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_header'] = $GLOBALS['TL_DCA']['tl_content']['palettes']['default'];
	$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_footer'] = $GLOBALS['TL_DCA']['tl_content']['palettes']['default'];

	$GLOBALS['TL_DCA']['tl_content']['fields']['type']['default'] = 'default';

	foreach ($GLOBALS['TL_CTE'] as $k => $v) {
		if ($k != 'newsletter') {
			unset($GLOBALS['TL_CTE'][$k]);
		}
	}
} elseif (TL_MODE == 'BE') {
	unset($GLOBALS['TL_CTE']['newsletter']);
}


/**
 * Class tl_content_newsletter
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    News
 */
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

		//$id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

		// Check the current action
		switch (Input::get('act'))
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
				if ((Input::get('act') == 'cutAll' || Input::get('act') == 'copyAll') && !$this->checkAccessToElement(Input::get('pid'), $root, (Input::get('mode') == 2)))
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
				if (!$this->checkAccessToElement(Input::get('pid'), $root, (Input::get('mode') == 2)))
				{
					$this->redirect('contao/main.php?act=error');
				}
				// NO BREAK STATEMENT HERE

			default:
				// Check access to the content element
				if (!$this->checkAccessToElement(Input::get('id'), $root))
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
