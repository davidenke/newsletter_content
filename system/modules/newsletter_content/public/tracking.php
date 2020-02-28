<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

// Set the script name
\define('TL_SCRIPT', 'system/modules/newsletter_content/public/tracking.php');

// Initialize the system
\define('TL_MODE', 'FE');
$path = \dirname(__DIR__);

while (($path = \dirname($path)) && $path !== '/') {
    $init = $path.'/system/initialize.php';

    if (file_exists($init)) {
        require_once($init);
        break;
    }
}

if (!file_exists($init)) {
    die('Contao initialize.php was not found');
}

// Run the controller
$tracking = \NewsletterContent\Classes\NewsletterTracking::getInstance();
$tracking->run();
