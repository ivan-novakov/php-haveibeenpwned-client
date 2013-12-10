<?php

namespace InoHibp\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use InoHibp\Service;


class Check extends Command
{


    protected function configure()
    {
        $this->setName('check')
            ->setDescription('Check if an email has been pwned')
            ->addArgument('email', InputArgument::REQUIRED, 'The email to be checked')
            ->addOption('ssl', null, InputOption::VALUE_NONE, 'Use SSL when connecting to the remote service')
            ->addOption('ca-file', null, InputOption::VALUE_REQUIRED, 'Use an alternative CA file')
            ->addOption('plain', null, InputOption::VALUE_NONE, 'Use simple output, suitable for parsing')
            ->addOption('show-exceptions', null, InputOption::VALUE_NONE, 'Show the exception (if any) instead of the error message, suitable for debugging');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceOptions = array();
        $email = $input->getArgument('email');
        
        $useSsl = $input->getOption('ssl');
        if ($useSsl) {
            $serviceOptions[Service::OPT_USE_SSL] = true;
        }
        
        $caFile = $input->getOption('ca-file');
        if ($caFile) {
            $serviceOptions[Service::OPT_CA_FILE] = $caFile;
        }
        
        $plain = $input->getOption('plain');
        $showExceptions = $input->getOption('show-exceptions');
        
        $service = new Service($serviceOptions);
        
        try {
            $result = $service->checkEmail($email);
        } catch (\Exception $e) {
            $this->outputException($output, $e, $showExceptions, $plain);
            return;
        }
        
        $this->outputResult($result, $output, $plain);
    }


    protected function outputException(OutputInterface $output,\Exception $e, $showExceptions, $plain)
    {
        $output->writeln(sprintf("%s %s", $this->formatError('[ERROR]', $plain), $e->getMessage()));
        
        if ($showExceptions) {
            $output->writeln("$e");
        }
    }


    protected function outputResult($result, OutputInterface $output, $plain = false)
    {
        if (null === $result) {
            $output->writeln($this->formatOk('[OK]', $plain));
            return;
        }
        
        $output->writeln(sprintf("%s %s", $this->formatPwned('[PWNED]', $plain), implode(', ', $result)));
    }


    protected function formatError($text, $plain)
    {
        if ($plain) {
            return $text;
        }
        
        return '<fg=red>' . $text . '</fg=red>';
    }


    protected function formatPwned($text, $plain)
    {
        if ($plain) {
            return $text;
        }
        
        return '<fg=yellow;options=bold>' . $text . '</fg=yellow;options=bold>';
    }


    protected function formatOk($text, $plain)
    {
        if ($plain) {
            return $text;
        }
        
        return '<fg=green>' . $text . '</fg=green>';
    }
}