<?php

namespace Concerto\TestBundle\Service;

use Concerto\PanelBundle\Entity\TestSession;

abstract class ASessionRunnerService
{
    abstract public function startNew(TestSession $session, $params, $client_ip, $client_browser, $debug = false);

    abstract public function submit(TestSession $session, $values, $client_ip, $client_browser, $time = null);
}