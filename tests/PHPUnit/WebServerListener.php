<?php

/*
 * This file was originally part of the FOSHttpCache package and released under
 * the MIT license.
 */

namespace Prezent\Soap\Client\Tests\PHPUnit;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use Throwable;

/**
 * A PHPUnit test listener that starts and stops the PHP built-in web server.
 *
 * This listener is configured with a couple of constants from the phpunit.xml
 * file. To define constants in the phpunit file, use this syntax:
 * <php>
 *     <const name="WEB_SERVER_HOSTNAME" value="localhost" />
 * </php>
 *
 * WEB_SERVER_HOSTNAME host name of the web server (required)
 * WEB_SERVER_PORT     port to listen on (required)
 * WEB_SERVER_DOCROOT  path to the document root for the server (required)
 */
class WebServerListener implements TestListener
{
    /**
     * PHP web server PID.
     *
     * @var int
     */
    protected $pid;

    /**
     * Make sure the PHP built-in web server is running for tests with group
     * 'webserver'.
     */
    public function startTestSuite(TestSuite $suite): void
    {
        // Only run on PHP >= 5.4 as PHP below that and HHVM don't have a
        // built-in web server
        if (defined('HHVM_VERSION')) {
            return;
        }

        if (!in_array('webserver', $suite->getGroups()) || null !== $this->pid) {
            return;
        }

        $this->pid = $pid = $this->startPhpWebServer();

        register_shutdown_function(function () use ($pid) {
            exec('kill '.$pid);
        });
    }

    /**
     *  We don't need these.
     */
    public function addError(Test $test, Throwable $t, float $time): void
    {
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
    }

    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
    }

    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
    }

    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
    }

    public function endTestSuite(TestSuite $suite): void
    {
    }

    public function startTest(Test $test): void
    {
    }

    public function endTest(Test $test, float $time): void
    {
    }

    /**
     * Get web server hostname.
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getHostName()
    {
        if (!defined('WEB_SERVER_HOSTNAME')) {
            throw new \Exception('Set WEB_SERVER_HOSTNAME in your phpunit.xml');
        }

        return WEB_SERVER_HOSTNAME;
    }

    /**
     * Get web server port.
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getPort()
    {
        if (!defined('WEB_SERVER_PORT')) {
            throw new \Exception('Set WEB_SERVER_PORT in your phpunit.xml');
        }

        return WEB_SERVER_PORT;
    }

    /**
     * Get web server port.
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getDocRoot()
    {
        if (!defined('WEB_SERVER_DOCROOT')) {
            throw new \Exception('Set WEB_SERVER_DOCROOT in your phpunit.xml');
        }

        return WEB_SERVER_DOCROOT;
    }

    /**
     * Start PHP built-in web server.
     *
     * @return int PID
     */
    protected function startPhpWebServer()
    {
        $command = sprintf(
            'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
            '127.0.0.1', // on travis, localhost is not 127.0.0.1 but IPv6 ::1
            $this->getPort(),
            $this->getDocRoot()
        );
        exec($command, $output);

        $this->waitFor($this->getHostName(), $this->getPort(), 2000);

        return $output[0];
    }

    /**
     * Wait for the server to be started up and reachable.
     *
     * @param string $ip
     * @param int    $port
     * @param int    $timeout Timeout in milliseconds
     *
     * @throws \RuntimeException If proxy is not reachable within timeout
     */
    protected function waitFor($ip, $port, $timeout)
    {
        for ($i = 0; $i < $timeout; ++$i) {
            if (@fsockopen($ip, $port)) {
                return;
            }

            usleep(1000);
        }

        throw new \RuntimeException(
            sprintf(
                'Webserver cannot be reached at %s:%s',
                $ip,
                $port
            )
        );
    }
}
