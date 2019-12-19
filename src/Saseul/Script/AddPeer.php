<?php

namespace Saseul\Script;

use Saseul\CommandUtil\Tracker;
use Saseul\Common\Script;
use Saseul\Util\Logger;
use Saseul\Util\RestCall;

class AddPeer extends Script
{
    function main()
    {
        $rest = RestCall::GetInstance();
        $ip = $this->ask('Please type host (ip) address. ');

        $rs = $rest->get('http://'.$ip.'/ping');
        $rs = json_decode($rs, true);

        $ok = $rs['data'] ?? 'no';

        if ($ok === 'ok') {
            Tracker::setPeer($ip);
            Logger::log('Host added successfully: '. $ip);
        } else {
            Logger::log('Fail: '. $ip);
        }
    }
}
