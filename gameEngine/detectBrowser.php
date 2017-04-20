<?php
/**
 * Created by PhpStorm.
 * User: Ayan Dey
 * Date: 4/19/2017
 * Time: 9:59 PM
 */

    use DeviceDetector\DeviceDetector;
    use DeviceDetector\Parser\Device\DeviceParserAbstract;
    DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $clientInfo = $botInfo = '';
    $dd = new DeviceDetector($userAgent);
    $dd->parse();
    if ($dd->isBot()) {
        $botInfo = $dd->getBot();
    }
    else {
        $clientInfo = $dd->getClient();
    }

    /*
     * Unsupported Browser list
     */
    $show = true;
    switch ($clientInfo['name']) {
        case "Internet Explorer";
        case "IE Mobile";
        case "Android Browser";
        case "UC Browser";
        case "Samsung Browser";
        case "Opera";
        case "Opera Mobile";
        case "Opera Mini";
            $show = false;
            break;
        default;
            break;
    }