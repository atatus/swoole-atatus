<?php
/**
 * Copyright © Atatus. All rights reserved.
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Atatus\Swoole\AtatusApm;

/**
 * Transaction of Atatus APM
 *
 * @link https://docs.atatus.com/docs/agents/php-agent/php-agent-api
 */
class Transaction
{
    /**
     * @var \Swoole\Http\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $appName;

    /**
     * @var string|null
     */
    protected $license;

    /**
     * Inject dependencies
     *
     * @param \Swoole\Http\Request $request
     * @param string $appName
     * @param string|null $license
     */
    public function __construct(\Swoole\Http\Request $request, $appName, $license)
    {
        $this->request = $request;
        $this->appName = $appName;
        $this->license = $license;
    }

    /**
     * Start monitoring of underlying request
     */
    public function start()
    {
        $snapshot = $this->emulateGlobalState($this->request);
	try {
	      if (extension_loaded('atatus')) {
		      atatus_begin_transaction();
		      atatus_set_background_transaction(false);
          }
        } finally {
            $this->restoreGlobalState($snapshot);
        }
    }

    /**
     * Stop timing underlying request but continue monitoring it
     */
    public function stop()
    {
    	if (extension_loaded('atatus')) { // ensure PHP agent is available
            atatus_end_transaction();
        }
    }

    /**
     * Finish monitoring of underlying request and report collected stats
     */
    public function finish()
    {
      if (extension_loaded('atatus')) { // ensure PHP agent is available
       	  atatus_end_transaction();
      }
    }

    /**
     * Emulate global state of a given request and return a snapshot of the original state
     *
     * @param \Swoole\Http\Request $request
     * @return array
     *
     * @todo Honor PHP settings governing super-global variables
     * @link https://www.php.net/manual/en/ini.core.php#ini.variables-order
     * @link https://www.php.net/manual/en/ini.core.php#ini.request-order
     */
    protected function emulateGlobalState(\Swoole\Http\Request $request)
    {
        $snapshot   = [$_SERVER, $_GET, $_POST, $_COOKIE, $_FILES, $_REQUEST];
        $_SERVER    = $this->extractServerVars($request);
        $_GET       = (array)$request->get;
        $_POST      = (array)$request->post;
        $_COOKIE    = (array)$request->cookie;
        $_FILES     = (array)$request->files;
        $_REQUEST   = $_COOKIE + $_POST + $_GET;
        return $snapshot;
    }

    /**
     * Restore global state from a given snapshot
     *
     * @param array $snapshot
     */
    protected function restoreGlobalState(array $snapshot)
    {
        list($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES, $_REQUEST) = $snapshot;
    }

    /**
     * Build server variables for a given request
     *
     * @param \Swoole\Http\Request $request
     * @return array
     */
    protected function extractServerVars(\Swoole\Http\Request $request)
    {
        $result = [];
        foreach ((array)$request->server as $key => $value) {
            $key = strtoupper($key);
            $result[$key] = $value;
        }
        foreach ((array)$request->header as $key => $value) {
            $key = strtoupper($key);
            $key = strtr($key, '-', '_');
            $key = 'HTTP_' . $key;
            $result[$key] = $value;
        }
        return $result;
    }
}
