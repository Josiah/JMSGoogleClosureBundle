<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\GoogleClosureBundle\Command;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Starts the Plovr server.
 *
 * @see http://plovr.com/docs.html
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class BuildPlovrCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('plovr:build')
            ->setDescription('Builds a Javascript app using the Plovr server')
            ->addArgument('config', InputArgument::REQUIRED, 'The configuration file to use.')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $javaBin = $this->locateJavaBin($input);
        $plovrJar = $this->locatePlovrJar($input);
        $config = $this->loadPlovrConfig($input->getArgument('config'));

        if (!isset($config['output-file'])) {
            throw new \RuntimeException('You must specify "output-file" in your plovr configuration file.');
        }
        if (!is_string($config['output-file'])) {
            throw new \RuntimeException('"output-file" must be a string.');
        }
        $outputFile = $this->normalizePath($config['output-file']);

        $dir = dirname($outputFile);
        if (!file_exists($dir)) {
            $output->writeln(sprintf('Creating output directory "%s"...', $dir));
            @mkdir($dir, 0777, true);
        }

        if (!is_writable($dir)) {
            throw new \RuntimeException(sprintf('Output path "%s" is not writable.', $dir));
        }

        $path = $this->writeTempConfig($config);
        $this->runJar($output, $javaBin, $plovrJar, 'build '.escapeshellarg($tempFile).' > '.escapeshellarg($outputFile));
        unlink($tempFile);
    }
}
