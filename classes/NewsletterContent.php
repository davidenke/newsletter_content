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
class NewsletterContent extends \Newsletter {
	public function send(\DataContainer $objDc) {

		if (TL_MODE == 'BE') {
			$GLOBALS['TL_CSS'][] = 'system/modules/newsletter_content/assets/style.css';
		}

		$objNewsletter = $this->Database->prepare("SELECT n.*, c.useSMTP, c.smtpHost, c.smtpPort, c.smtpUser, c.smtpPass FROM tl_newsletter n LEFT JOIN tl_newsletter_channel c ON n.pid=c.id WHERE n.id=?")
										->limit(1)
										->execute($objDc->id);

		// Return if there is no newsletter
		if ($objNewsletter->numRows < 1) {
			return '';
		}

		// Overwrite the SMTP configuration
		if ($objNewsletter->useSMTP) {
			$GLOBALS['TL_CONFIG']['useSMTP'] = true;

			$GLOBALS['TL_CONFIG']['smtpHost'] = $objNewsletter->smtpHost;
			$GLOBALS['TL_CONFIG']['smtpUser'] = $objNewsletter->smtpUser;
			$GLOBALS['TL_CONFIG']['smtpPass'] = $objNewsletter->smtpPass;
			$GLOBALS['TL_CONFIG']['smtpEnc']  = $objNewsletter->smtpEnc;
			$GLOBALS['TL_CONFIG']['smtpPort'] = $objNewsletter->smtpPort;
		}

		// Add default sender address
		if ($objNewsletter->sender == '') {
			list($objNewsletter->senderName, $objNewsletter->sender) = \String::splitFriendlyEmail($GLOBALS['TL_CONFIG']['adminEmail']);
		}

		$arrAttachments = array();
		$blnAttachmentsFormatError = false;

		// Add attachments
		if ($objNewsletter->addFile) {
			$files = deserialize($objNewsletter->files);

			if (!empty($files) && is_array($files)) {
				$objFiles = \FilesModel::findMultipleByUuids($files);

				if ($objFiles === null) {
					if (!\Validator::isUuid($files[0])) {
						$blnAttachmentsFormatError = true;
						\Message::addError($GLOBALS['TL_LANG']['ERR']['version2format']);
					}
				} else {
					while ($objFiles->next()) {
						if (is_file(TL_ROOT . '/' . $objFiles->path)) {
							$arrAttachments[] = $objFiles->path;
						}
					}
				}
			}
		}

		// Get content
		$html = '';
		$objContentElements = \ContentModel::findPublishedByPidAndTable($objNewsletter->id, 'tl_newsletter');

		if ($objContentElements !== null) {
			if (!defined('NEWSLETTER_CONTENT_PREVIEW')) {
				define('NEWSLETTER_CONTENT_PREVIEW', true);
			}

			while ($objContentElements->next()) {
				$html.= $this->getContentElement($objContentElements->id);
			}
		}

		// Convert relative URLs
		$html = $this->convertRelativeUrls($html);

		// Replace insert tags
		$text = $this->replaceInsertTags($objNewsletter->text);
		$html = $this->replaceInsertTags($html);

		// Set back to object
		$objNewsletter->content = $html;

		// Send newsletter
		if (!$blnAttachmentsFormatError && \Input::get('token') != '' && \Input::get('token') == $this->Session->get('tl_newsletter_send')) {
			$referer = preg_replace('/&(amp;)?(start|mpc|token|recipient|preview)=[^&]*/', '', \Environment::get('request'));

			// Preview
			if (isset($_GET['preview'])) {
				// Check the e-mail address
				if (!\Validator::isEmail(\Input::get('recipient', true))) {
					$_SESSION['TL_PREVIEW_MAIL_ERROR'] = true;
					$this->redirect($referer);
				}

				$arrRecipient['email'] = urldecode(\Input::get('recipient', true));

				// Send
				$objEmail = $this->generateEmailObject($objNewsletter, $arrAttachments);
				$objNewsletter->email = $arrRecipient['email'];
				$this->sendNewsletter($objEmail, $objNewsletter, $arrRecipient, $text, $html);

				// Redirect
				\Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_newsletter']['confirm'], 1));
				$this->redirect($referer);
			}

			// Get the total number of recipients
			$objTotal = $this->Database->prepare("SELECT COUNT(DISTINCT email) AS count FROM tl_newsletter_recipients WHERE pid=? AND active=1")
									   ->execute($objNewsletter->pid);

			// Return if there are no recipients
			if ($objTotal->count < 1) {
				$this->Session->set('tl_newsletter_send', null);
				\Message::addError($GLOBALS['TL_LANG']['tl_newsletter']['error']);
				$this->redirect($referer);
			}

			$intTotal = $objTotal->count;

			// Get page and timeout
			$intTimeout = (\Input::get('timeout') > 0) ? \Input::get('timeout') : 1;
			$intStart = \Input::get('start') ? \Input::get('start') : 0;
			$intPages = \Input::get('mpc') ? \Input::get('mpc') : 10;

			// Get recipients
			$objRecipients = $this->Database->prepare("SELECT *, r.email FROM tl_newsletter_recipients r LEFT JOIN tl_member m ON(r.email=m.email) WHERE r.pid=? AND r.active=1 GROUP BY r.email ORDER BY r.email")
											->limit($intPages, $intStart)
											->execute($objNewsletter->pid);

			echo '<div style="font-family:Verdana,sans-serif;font-size:11px;line-height:16px;margin-bottom:12px">';

			// Send newsletter
			if ($objRecipients->numRows > 0) {
				// Update status
				if ($intStart == 0) {
					$this->Database->prepare("UPDATE tl_newsletter SET sent=1, date=? WHERE id=?")
								   ->execute(time(), $objNewsletter->id);

					$_SESSION['REJECTED_RECIPIENTS'] = array();
				}

				while ($objRecipients->next()) {
					$objEmail = $this->generateEmailObject($objNewsletter, $arrAttachments);
					$objNewsletter->email = $objRecipients->email;
					$this->sendNewsletter($objEmail, $objNewsletter, $objRecipients->row(), $text, $html);

					echo 'Sending newsletter to <strong>' . $objRecipients->email . '</strong><br>';
				}
			}

			echo '<div style="margin-top:12px">';

			// Redirect back home
			if ($objRecipients->numRows < 1 || ($intStart + $intPages) >= $intTotal) {
				$this->Session->set('tl_newsletter_send', null);

				// Deactivate rejected addresses
				if (!empty($_SESSION['REJECTED_RECIPIENTS']))
				{
					$intRejected = count($_SESSION['REJECTED_RECIPIENTS']);
					\Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_newsletter']['rejected'], $intRejected));
					$intTotal -= $intRejected;

					foreach ($_SESSION['REJECTED_RECIPIENTS'] as $strRecipient)
					{
						$this->Database->prepare("UPDATE tl_newsletter_recipients SET active='' WHERE email=?")
									   ->execute($strRecipient);

						$this->log('Recipient address "' . $strRecipient . '" was rejected and has been deactivated', __METHOD__, TL_ERROR);
					}
				}

				\Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_newsletter']['confirm'], $intTotal));

				echo '<script>setTimeout(\'window.location="' . \Environment::get('base') . $referer . '"\',1000)</script>';
				echo '<a href="' . \Environment::get('base') . $referer . '">Please click here to proceed if you are not using JavaScript</a>';
			}

			// Redirect to the next cycle
			else {
				$url = preg_replace('/&(amp;)?(start|mpc|recipient)=[^&]*/', '', \Environment::get('request')) . '&start=' . ($intStart + $intPages) . '&mpc=' . $intPages;

				echo '<script>setTimeout(\'window.location="' . \Environment::get('base') . $url . '"\',' . ($intTimeout * 1000) . ')</script>';
				echo '<a href="' . \Environment::get('base') . $url . '">Please click here to proceed if you are not using JavaScript</a>';
			}

			echo '</div></div>';
			exit;
		}

		$strToken = md5(uniqid(mt_rand(), true));
		$this->Session->set('tl_newsletter_send', $strToken);
		$sprintf = ($objNewsletter->senderName != '') ? $objNewsletter->senderName . ' &lt;%s&gt;' : '%s';
		$this->import('BackendUser', 'User');

		$preview = $html;
		// prepare preview
		if (!$objNewsletter->sendText) {
			// Default template
			if ($objNewsletter->template == '') {
				$objNewsletter->template = 'mail_default';
			}

			// Load the mail template
			$objTemplate = new \BackendTemplate($objNewsletter->template);
			$objTemplate->setData($objNewsletter->row());

			$objTemplate->title = $objNewsletter->subject;
			$objTemplate->body = \String::parseSimpleTokens($html, $arrRecipient);
			$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
			$objTemplate->css = $css; // Backwards compatibility

			// Parse template
			$preview = $objTemplate->parse();
		}

		// Cache preview
		if (!file_exists(TL_ROOT . '/system/cache/newsletter')) {
			mkdir(TL_ROOT . '/system/cache/newsletter');
			file_put_contents(TL_ROOT . '/system/cache/newsletter/.htaccess',
'<IfModule !mod_authz_core.c>
  Order allow,deny
  Allow from all
</IfModule>
<IfModule mod_authz_core.c>
  Require all granted
</IfModule>');
		}
		file_put_contents(TL_ROOT . '/system/cache/newsletter/' . $objNewsletter->alias . '.html', preg_replace('/^\s+|\n|\r|\s+$/m', '', $preview));

		// Preview newsletter
		$return = '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['tl_newsletter']['send'][1], $objNewsletter->id).'</h2>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('script'), true).'" id="tl_newsletter_send" class="tl_form" method="get">
<div class="tl_formbody_edit tl_newsletter_send">
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
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_newsletter']['template'][0] . '</td>
    <td class="col_1">' . $objNewsletter->template . '</td>
  </tr>' . ((!empty($arrAttachments) && is_array($arrAttachments)) ? '
  <tr class="row_3">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_newsletter']['attachments'] . '</td>
    <td class="col_1">' . implode(', ', $arrAttachments) . '</td>
  </tr>' : '') . '
</table>' . (!$objNewsletter->sendText ? '
<iframe class="preview_html" id="preview_html" seamless border="0" width="703px" height="503px" style="padding:0" src="system/cache/newsletter/' . $objNewsletter->alias . '.html"></iframe>
' : '') . '
<div class="preview_text">
' . nl2br_html5($text) . '
</div>

<div class="tl_tbox">
<div class="w50">
  <h3><label for="ctrl_mpc">' . $GLOBALS['TL_LANG']['tl_newsletter']['mailsPerCycle'][0] . '</label></h3>
  <input type="text" name="mpc" id="ctrl_mpc" value="10" class="tl_text" onfocus="Backend.getScrollOffset()">' . (($GLOBALS['TL_LANG']['tl_newsletter']['mailsPerCycle'][1] && $GLOBALS['TL_CONFIG']['showHelp']) ? '
  <p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['tl_newsletter']['mailsPerCycle'][1] . '</p>' : '') . '
</div>
<div class="w50">
  <h3><label for="ctrl_timeout">' . $GLOBALS['TL_LANG']['tl_newsletter']['timeout'][0] . '</label></h3>
  <input type="text" name="timeout" id="ctrl_timeout" value="1" class="tl_text" onfocus="Backend.getScrollOffset()">' . (($GLOBALS['TL_LANG']['tl_newsletter']['timeout'][1] && $GLOBALS['TL_CONFIG']['showHelp']) ? '
  <p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['tl_newsletter']['timeout'][1] . '</p>' : '') . '
</div>
<div class="w50">
  <h3><label for="ctrl_start">' . $GLOBALS['TL_LANG']['tl_newsletter']['start'][0] . '</label></h3>
  <input type="text" name="start" id="ctrl_start" value="0" class="tl_text" onfocus="Backend.getScrollOffset()">' . (($GLOBALS['TL_LANG']['tl_newsletter']['start'][1] && $GLOBALS['TL_CONFIG']['showHelp']) ? '
  <p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['tl_newsletter']['start'][1] . '</p>' : '') . '
</div>
<div class="w50">
  <h3><label for="ctrl_recipient">' . $GLOBALS['TL_LANG']['tl_newsletter']['sendPreviewTo'][0] . '</label></h3>
  <input type="text" name="recipient" id="ctrl_recipient" value="'.$this->User->email.'" class="tl_text" onfocus="Backend.getScrollOffset()">' . (isset($_SESSION['TL_PREVIEW_MAIL_ERROR']) ? '
  <div class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['email'] . '</div>' : (($GLOBALS['TL_LANG']['tl_newsletter']['sendPreviewTo'][1] && $GLOBALS['TL_CONFIG']['showHelp']) ? '
  <p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['tl_newsletter']['sendPreviewTo'][1] . '</p>' : '')) . '
</div>
<div class="clear"></div>
</div>
</div>';

		// Do not send the newsletter if there is an attachment format error
		if (!$blnAttachmentsFormatError) {
			$return .= '

<div class="tl_formbody_submit">
<div class="tl_submit_container">
<input type="submit" name="preview" class="tl_submit" accesskey="p" value="'.specialchars($GLOBALS['TL_LANG']['tl_newsletter']['preview']).'">
<input type="submit" id="send" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_newsletter']['send'][0]).'" onclick="return confirm(\''. str_replace("'", "\\'", $GLOBALS['TL_LANG']['tl_newsletter']['sendConfirm']) .'\')">
</div>
</div>';
		}

		$return .= '

</form>';

		unset($_SESSION['TL_PREVIEW_MAIL_ERROR']);
		return $return;
	}
}