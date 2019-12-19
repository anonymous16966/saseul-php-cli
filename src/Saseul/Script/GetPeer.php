<?php

namespace Saseul\Script;

use Saseul\CommandUtil\Tracker;
use Saseul\Common\Script;
use Saseul\Util\Logger;

class GetPeer extends Script
{
    function main()
    {
        $peers = Tracker::getPeers();

        Logger::log($peers);
    }
}
