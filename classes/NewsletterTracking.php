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
 * Front end module "newsletter tracking".
 * @copyright  David Enke 2015
 * @author     David Enke <post@davidenke.de>
 * @package    newsletter_content
 */
class NewsletterTracking extends \Controller
{

    // Singelten pattern
    protected static $instance = null;

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();
	}

    /**
     * Singelton Pattern
     *
     * @return \CtoCommunication
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new NewsletterTracking();
        }

        return self::$instance;
    }

	public function run()
	{
		// return
		if (!\Input::get('t'))
		{
			exit;
		}

		// get allready tracked
		if (\Input::get('preview'))
		{
			$intAccessed = 0;
			if (\Input::get('n') && \Input::get('e'))
			{
				$intAccessed = \NewsletterContent\Models\NewsletterTrackingModel::countTrackedByPidAndEmail(\Input::get('n'), \Input::get('e'));
			}
			header('Access-Count: ' . strval($intAccessed));
		}

		// track
		if (\Input::get('t') && \Input::get('n') && \Input::get('e')/*  && !\Input::get('preview') */)
		{
			$objTracking = new \NewsletterContent\Models\NewsletterTrackingModel();

			$objTracking->tstamp = time();
			$objTracking->pid = \Input::get('n');
			$objTracking->type = \Input::get('t');
			$objTracking->email = \Input::get('e');

			$objTracking->ip = \Environment::get('ip');
			$objTracking->agent = \Environment::get('httpUserAgent');
			$objTracking->language = \Environment::get('httpAcceptLanguage');

			if (\Input::get('t') == 'link' && \Input::get('l'))
			{
				$objTracking->link = base64_decode(str_pad(strtr(\Input::get('l'), '-_', '+/'), strlen(\Input::get('l')) % 4, '=', STR_PAD_RIGHT));
			}

			$objTracking->save();
		}

		// output
		switch (\Input::get('t'))
		{
			default:
				break;

			case 'png':
				header('Content-Type: image/png');
				echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
				break;

			case 'gif':
				header('Content-Type: image/gif');
				echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
				break;

			case 'css':
				header('Content-Type: text/css');
				break;

			case 'js':
				header('Content-Type: text/javascript');
				break;

			case 'link':
				header('Location: ' . base64_decode(str_pad(strtr(\Input::get('l'), '-_', '+/'), strlen(\Input::get('l')) % 4, '=', STR_PAD_RIGHT)), true, 301);
				break;
		}

		// exit script
		exit();
	}
}
