<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Core
 * @link	https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace NewsletterContent\Elements;


/**
 * Class ContentImage
 *
 * Newsletter content element "gallery".
 * @copyright    David Enke 2015
 * @author       David Enke <post@davidenke.de>
 * @package      newsletter_content
 */
class ContentGallery extends \ContentElement {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_gallery';

	protected $arrImages = array();


	/**
	 * Initialize the object
	 * @param object
	 * @param string
	 */
	public function __construct($objElement, $strColumn='main') {
		parent::__construct($objElement, $strColumn);

		if ($this->customTpl != '') {
			$this->strTemplate = $this->customTpl;
		}
	}


	/**
	 * Return if there are no files
	 * @return string
	 */
	public function generate()
	{
		// Get the file entries from the database
		$this->arrImages = deserialize($this->images, true);

		if (!sizeof($this->arrImages))
		{
			return '';
		}

		foreach ($this->arrImages as $k=>$arrImage)
		{
			$objFile = \FilesModel::findByUuid($arrImage['singleSRC']);

			if ($objFile === null)
			{
				if (!\Validator::isUuid($arrImage['singleSRC']))
				{
					return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
				}
	
				return '';
			}

			if (!is_file(TL_ROOT . '/' . $objFile->path))
			{
				return '';
			}
	
			$this->arrImages[$k]['singleSRC'] = $objFile->path;
		}

		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		global $objPage;

		if (is_null($objPage) || TL_MODE == 'BE')
		{
			$objPage = \PageModel::findFirstPublishedRootByHostAndLanguage(\Environment::get('host'), 'de');
		}

		// Limit the total number of items (see #2652)
		if ($this->numberOfItems > 0)
		{
			$this->arrImages = array_slice($this->arrImages, 0, $this->numberOfItems);
		}

		$offset = 0;
		$total = count($this->arrImages);
		$limit = $total;
		$rowcount = 0;
		$colwidth = floor(100/$this->perRow);
		$intMaxWidth = (TL_MODE == 'BE') ? floor((640 / $this->perRow)) : floor((\Config::get('maxImageWidth') / $this->perRow));
		$body = array();

		// Rows
		for ($i=$offset; $i<$limit; $i=($i+$this->perRow))
		{
			$class_tr = '';

			if ($rowcount == 0)
			{
				$class_tr .= ' row_first';
			}

			if (($i + $this->perRow) >= $limit)
			{
				$class_tr .= ' row_last';
			}

			$class_eo = (($rowcount % 2) == 0) ? ' even' : ' odd';

			// Columns
			for ($j=0; $j<$this->perRow; $j++)
			{
				$class_td = '';

				if ($j == 0)
				{
					$class_td .= ' col_first';
				}

				if ($j == ($this->perRow - 1))
				{
					$class_td .= ' col_last';
				}

				$objCell = new \stdClass();
				$key = 'row_' . $rowcount . $class_tr . $class_eo;

				// Empty cell
				if (!is_array($this->arrImages[($i+$j)]) || ($j+$i) >= $limit)
				{
					$objCell->colWidth = $colwidth . '%';
					$objCell->class = 'col_'.$j . $class_td;
				}
				else
				{
					$this->addImageToTemplate($objCell, $this->arrImages[($i+$j)], $intMaxWidth, $strLightboxId);

					// Add column width and class
					$objCell->href = $this->arrImages[($i+$j)]['imageUrl'];
					$objCell->colWidth = $colwidth . '%';
					$objCell->class = 'col_'.$j . $class_td;
				}

				$body[$key][$j] = $objCell;
			}

			++$rowcount;
		}

		$this->Template->body = $body;
	}
}
