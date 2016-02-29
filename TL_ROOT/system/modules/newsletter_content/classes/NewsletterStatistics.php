<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015Leo Feyer
 *
 * @package Newsletter
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace NewsletterContent\Classes;


/**
 * Class NewsletterContent
 *
 * Front end module "newsletter content reader".
 * @copyright  David Enke 2015
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
class NewsletterStatistics extends \Newsletter {

	protected $isFlexible = false;

	protected function __construct() {
		parent::__construct();
		$this->import('BackendUser');
		$this->isFlexible = $this->BackendUser->backendTheme == 'flexible';
	}


	public function show(\DataContainer $objDc) {

		if (\Input::get('key') != 'stats')
		{
			return '';
		}

		if (\Input::get('close')) {
			$this->redirect($this->getReferer(true));
		}

		if (TL_MODE == 'BE') {
			$GLOBALS['TL_JAVASCRIPT'][] = 'assets/mootools/milkchart/1.5.9.0/MilkChart.yc.js';
			$GLOBALS['TL_CSS'][] = 'system/modules/newsletter_content/assets/css/style.css';

			if ($this->isFlexible) {
				$GLOBALS['TL_CSS'][] = 'system/modules/newsletter_content/assets/css/style-flexible.css';
			}
		}

		$objNewsletter = $this->Database->prepare("SELECT n.*, c.useSMTP, c.smtpHost, c.smtpPort, c.smtpUser, c.smtpPass FROM tl_newsletter n LEFT JOIN tl_newsletter_channel c ON n.pid=c.id WHERE n.id=?")
										->limit(1)
										->execute($objDc->id);

		// Return if there is no newsletter
		if ($objNewsletter->numRows < 1) {
			return '';
		}

		// !Header
		$sprintf = ($objNewsletter->senderName != '') ? $objNewsletter->senderName . ' &lt;%s&gt;' : '%s';
		$return = '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['tl_newsletter']['stats'][1], $objNewsletter->id).'</h2>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('script'), true).'" id="tl_newsletter_send" class="tl_form" method="get">
<div class="tl_formbody_edit tl_newsletter_send tl_newsletter_stats">
<input type="hidden" name="do" value="' . \Input::get('do') . '">
<input type="hidden" name="table" value="' . \Input::get('table') . '">
<input type="hidden" name="key" value="' . \Input::get('key') . '">
<input type="hidden" name="id" value="' . \Input::get('id') . '">
<input type="hidden" name="token" value="' . $strToken . '">
<table class="prev_header">
  <tr class="row_0">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_newsletter']['from'] . '</td>
    <td class="col_1">' . sprintf($sprintf, $objNewsletter->sender) . '</td>
  </tr>
  <tr class="row_1">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_newsletter']['subject'][0] . '</td>
    <td class="col_1">' . $objNewsletter->subject . '</td>
  </tr>
  <tr class="row_2">
    <td class="col_0">Empfänger</td>
    <td class="col_1">' . ($objNewsletter->recipients - $objNewsletter->rejected) . ' von ' . $objNewsletter->recipients . '</td>
  </tr>
</table>';

		// Mining data
		$objDatabase = \Database::getInstance();
		$objTrackedInteractions = $objDatabase->prepare("SELECT UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(tstamp))) as track_time FROM tl_newsletter_tracking WHERE pid=? GROUP BY email")->execute($objDc->id);
		$objTrackedLinks = $objDatabase->prepare("SELECT UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(tstamp))) as track_time FROM tl_newsletter_tracking WHERE pid=? AND type='link' GROUP BY email")->execute($objDc->id);
		$objTrackedOpened = $objDatabase->prepare("SELECT UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(tstamp))) as track_time FROM tl_newsletter_tracking WHERE pid=? AND type!='link' GROUP BY email")->execute($objDc->id);
		$objTrackedLinkClicks = $objDatabase->prepare("SELECT UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(tstamp))) as track_time,link FROM tl_newsletter_tracking WHERE pid=? AND link!='' AND type='link'")->execute($objDc->id);

		$arrCount = array();
		$arrLinks = array();
		if ($objTrackedInteractions->numRows) {
    		while ($objTrackedInteractions->next()) {
	    		if (!$arrCount[$objTrackedInteractions->track_time]) {
		    		$arrCount[$objTrackedInteractions->track_time] = array(
			    		'inter' => 0,
			    		'clicks' => 0,
			    		'opened' => 0,
			    		'links' => array()
		    		);
	    		}
	    		++$arrCount[$objTrackedInteractions->track_time]['inter'];
			}
		}
		if ($objTrackedLinks->numRows) {
    		while ($objTrackedLinks->next()) {
	    		if (!$arrCount[$objTrackedLinks->track_time]) {
		    		$arrCount[$objTrackedLinks->track_time] = array(
			    		'inter' => 0,
			    		'clicks' => 0,
			    		'opened' => 0,
			    		'links' => array()
		    		);
	    		}
	    		++$arrCount[$objTrackedLinks->track_time]['clicks'];
			}
		}
		if ($objTrackedOpened->numRows) {
    		while ($objTrackedOpened->next()) {
	    		if (!$arrCount[$objTrackedOpened->track_time]) {
		    		$arrCount[$objTrackedOpened->track_time] = array(
			    		'inter' => 0,
			    		'clicks' => 0,
			    		'opened' => 0,
			    		'links' => array()
		    		);
	    		}
	    		++$arrCount[$objTrackedOpened->track_time]['opened'];
			}
		}
		if ($objTrackedLinkClicks->numRows) {
    		while ($objTrackedLinkClicks->next()) {
	    		if (!$arrCount[$objTrackedLinkClicks->track_time]) {
		    		$arrCount[$objTrackedLinkClicks->track_time] = array(
			    		'inter' => 0,
			    		'clicks' => 0,
			    		'opened' => 0,
			    		'links' => array()
		    		);
	    		}
	    		if (!$arrCount[$objTrackedLinkClicks->track_time]['links'][$objTrackedLinkClicks->link]) {
		    		$arrCount[$objTrackedLinkClicks->track_time]['links'][$objTrackedLinkClicks->link] = 0;
	    		}
	    		if (!$arrLinks[$objTrackedLinkClicks->link]) {
		    		$arrLinks[$objTrackedLinkClicks->link] = 0;
	    		}
	    		++$arrCount[$objTrackedLinkClicks->track_time]['links'][$objTrackedLinkClicks->link];
	    		++$arrLinks[$objTrackedLinkClicks->link];
			}
		}
		arsort($arrLinks);

		// !Interactions
		$return.= '
<table class="stats" id="TrackedInteractions">
    <thead>
    	<tr>
    		<th>Interaktionen</th>
    		<th>Links aufgerufen</th>
    		<th>Geöffnet</th>
	    </tr>
    </thead>
    <tbody>
    	';
		$strSent = strtotime(date('Y-m-d', $objNewsletter->date));
		if (!$arrCount[$strSent]) {
			$return.= '<tr><td>0</td><td>0</td><td>0</td></tr>';
		}
		foreach ($arrCount as $arrData) {
			$return.= '<tr><td>' . $arrData['inter'] . '</td><td>' . $arrData['clicks'] . '</td><td>' . $arrData['opened'] . '</td></tr>';
		}
		$return.= '
</tbody>
<tfoot>
    <tr>
		';
		if (!$arrCount[$strSent]) {
			$return.= '<td>' . \Date::parse(\Config::get('dateFormat'), $strSent) . '</td>';
		}
		foreach ($arrCount as $intCount=>$arrData) {
			$return.= '<td>' . \Date::parse(\Config::get('dateFormat'), $intCount) . '</td>';
		}
		$return.= '
    </tr>
</tfoot>
</table>
<script type="text/javascript">
window.addEvent("domready", function() {
if (!window.charts) window.charts = [];
window.charts.push(function(vw) {
	return new MilkChart.Line($("TrackedInteractions"), {
		useZero: true,
	    border: false,
        background: "#F0F0F0",
        width: ' . ($this->isFlexible ? '(vw >= 1200 ? 883 : (vw >= 768 ? 703 : vw - 46))' : '703') . ',
        lineWeight: 1.3,
        rowTicks: false,
        labelTicks: true
    });
});
});
</script>
		';

		// Links
		$arrLinksShort = array_slice($arrLinks, 0, 10, true);
		$return.= '
<table class="stats" id="TrackedLinks">
    <thead>
    	<tr>';
		foreach ($arrLinksShort as $strLink=>$intCount) {
			$return.= "<th>" . $intCount . ' ' . $strLink . '</th>';
		};
		$return.= '
	    </tr>
    </thead>
    <tbody>
    	<tr>
    	';
		foreach ($arrLinksShort as $strLink=>$intCount) {
			$return.= '<td>' . $intCount . '</td>';
		}
		$return.= '
	</tr>
</tbody>
</table>
<script type="text/javascript">
window.addEvent("domready", function() {
if (!window.charts) window.charts = [];
window.charts.push(function(vw) {
	return new MilkChart.Pie($("TrackedLinks"), {
		useZero: true,
	    border: false,
        background: "#F0F0F0",
        width: ' . ($this->isFlexible ? '(vw >= 1200 ? 883 : (vw >= 768 ? 703 : vw - 46))' : '703') . ',
        height: 350,
        strokeWeight: 1.3,
        strokeColor: "#F0F0F0",
        chartLineWeight: 0
    });
});
});
</script>
		';

		// !Footer
		$return.= '
</div>
<div class="tl_formbody_submit">
  <div class="tl_submit_container">
    <input type="submit" name="close" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['close']).'">
  </div>
</div>
</form>

<script type="text/javascript">
window.addEvent("domready", function() {
	window.createCharts = function() {
		if (window.charts && window.charts.length) {
			var vw = window.getSize().x;
			if (!window.els) window.els = [];
			for (i = 0; i < window.charts.length; ++i) {
				if (window.els[i] && window.els[i].container) window.els[i].container.destroy();
				window.els[i] = window.charts[i](vw);
				window.els[i].container.addClass("canvas n-" + (i+1));
			}
		}
	}
	window.createCharts();
		';
		if ($this->isFlexible) {
			$return.= 'window.addEvent("resize", window.createCharts);';
		}
		$return.= '
});
</script>';

		return $return;
	}

	public function clean($strContent, $strTemplate) {
		// disable markup compression
		if ($strTemplate == 'be_main' && \Input::get('key') == 'stats') {
			\Config::set('minifyMarkup', false);
		}

		return $strContent;
	}
}
