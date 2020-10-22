<?php

namespace Concerto\TestBundle\Service;

use Concerto\PanelBundle\Service\TestService;
use Psr\Log\LoggerInterface;
use Concerto\PanelBundle\Service\TestSessionService;

class TestRunnerService
{

    private $logger;
    private $environment;
    private $sessionService;
    private $testService;

    public function __construct($environment, LoggerInterface $logger, TestSessionService $sessionService, TestService $testService)
    {
        $this->environment = $environment;
        $this->logger = $logger;
        $this->sessionService = $sessionService;
        $this->testService = $testService;
    }

    public function startNewSession($test_slug, $test_name, $params, $cookies, $headers, $client_ip, $client_browser, $debug, $mustBeRunnable = true)
    {
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - $test_slug, $test_name, $params, $client_ip, $client_browser, $debug, $mustBeRunnable");

        $response = $this->sessionService->startNewSession($test_slug, $test_name, $params, $cookies, $headers, $client_ip, $client_browser, $debug, $mustBeRunnable);
        $response = json_encode($response);
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - response: $response");
        return $response;
    }

    public function resumeSession($session_hash, $cookies, $client_ip, $client_browser, $debug)
    {
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - $session_hash, $client_ip, $client_browser, $debug");

        $response = $this->sessionService->resumeSession($session_hash, $cookies, $client_ip, $client_browser, $debug);
        $response = json_encode($response);
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - response: $response");
        return $response;
    }

    public function submitToSession($session_hash, $values, $cookies, $client_ip, $client_browser)
    {
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - $session_hash, $client_ip, $client_browser");

        $response = $this->sessionService->submit($session_hash, $values, $cookies, $client_ip, $client_browser);
        $response = json_encode($response);
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - response: $response");
        return $response;
    }

    public function backgroundWorker($session_hash, $values, $cookies, $client_ip, $client_browser)
    {
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - $session_hash, $client_ip, $client_browser");

        $response = $this->sessionService->backgroundWorker($session_hash, $values, $cookies, $client_ip, $client_browser);
        $response = json_encode($response);
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - response: $response");
        return $response;
    }

    public function keepAliveSession($session_hash, $client_ip, $client_browser)
    {
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - $session_hash, $client_ip, $client_browser");

        $response = $this->sessionService->keepAlive($session_hash, $client_ip, $client_browser);
        $response = json_encode($response);
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - response: $response");
        return $response;
    }

    public function killSession($session_hash, $client_ip, $client_browser)
    {
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - $session_hash, $client_ip, $client_browser");

        $response = $this->sessionService->kill($session_hash, $client_ip, $client_browser);
        $response = json_encode($response);
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - response: $response");
        return $response;
    }

    public function isBrowserValid($user_agent)
    {
        if (preg_match('/(?i)msie [1-8]\./', $user_agent)) {
            return false;
        } else {
            return true;
        }
    }

    public function uploadFile($session_hash, $files, $name)
    {
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - $session_hash, $name");

        $response = $this->sessionService->uploadFile($session_hash, $files, $name);
        $response = json_encode($response);
        $this->logger->info(__CLASS__ . ":" . __FUNCTION__ . " - response: $response");
        return $response;
    }

    public function logError($session_hash, $error, $type)
    {
        $response = $this->sessionService->logError($session_hash, $error, $type);
        $response = json_encode($response);
        return $response;
    }

    public function getBaseTemplateContent($test_slug = null, $test_name = null)
    {
        return $this->testService->getBaseTemplateContent($test_slug, $test_name);
    }
}
