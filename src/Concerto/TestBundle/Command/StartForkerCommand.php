<?php

namespace Concerto\TestBundle\Command;

use Concerto\TestBundle\Service\ASessionRunnerService;
use Concerto\TestBundle\Service\SerializedSessionRunnerService;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class StartForkerCommand extends Command
{

    private $testRunnerSettings;
    private $doctrine;
    private $sessionRunnerService;

    public function __construct($testRunnerSettings, RegistryInterface $doctrine, ASessionRunnerService $sessionRunnerService)
    {
        parent::__construct();

        $this->doctrine = $doctrine;
        $this->testRunnerSettings = $testRunnerSettings;
        $this->sessionRunnerService = $sessionRunnerService;
    }

    protected function configure()
    {
        $this->setName("concerto:forker:start")->setDescription("Start forker process.");
    }

    private function getCommand()
    {
        $forkerPath = realpath(dirname(__FILE__) . "/../Resources/R/forker.R");
        $logPath = realpath(dirname(__FILE__) . "/../Resources/R") . "/forker.log";
        $fifoPath = $this->sessionRunnerService->getFifoDir();
        $publicDir = $this->sessionRunnerService->getPublicDirPath();
        $connection = json_encode($this->sessionRunnerService->getConnection());
        $platformUrl = $this->sessionRunnerService->getPlatformUrl();
        $appUrl = $this->sessionRunnerService->getAppUrl();
        $maxExecTime = $this->testRunnerSettings["max_execution_time"];
        $maxIdleTime = $this->testRunnerSettings["max_idle_time"];
        $keepAliveToleranceTime = $this->testRunnerSettings["keep_alive_tolerance_time"];

        $cmd = "nohup " . $this->testRunnerSettings["rscript_exec"] . " --no-save --no-restore --quiet "
            . "'$forkerPath' "
            . "'$fifoPath' "
            . "'$publicDir' "
            . "'$platformUrl' "
            . "'$appUrl' "
            . "'$connection' "
            . "$maxExecTime "
            . "$maxIdleTime "
            . "$keepAliveToleranceTime "
            . ">> "
            . "'" . $logPath . "' "
            . "2>&1 & echo $!";
        return $cmd;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("starting forker...");

        $cmd = $this->getCommand();
        $process = new Process($cmd);
        $env = array(
            "R_GC_MEM_GROW" => 0
        );
        if ($this->testRunnerSettings["r_environ_path"] != null) {
            $env["R_ENVIRON"] = $this->testRunnerSettings["r_environ_path"];
        }
        $process->setEnv($env);
        $process->mustRun();
        if ($process->getExitCode() == 0) {
            $output->writeln("forker started");
        } else {
            $output->writeln("something went wrong: non zero exit code");
        }
    }

}
