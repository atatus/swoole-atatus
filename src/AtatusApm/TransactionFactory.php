<?php
/**
 * Copyright © Atatus. All rights reserved.
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Atatus\Swoole\AtatusApm;

class TransactionFactory
{
    /**
     * @var string
     */
    protected $appName;

    /**
     * @var string
     */
    protected $license;

    /**
     * Inject dependencies
     *
     * @param string|null $appName
     * @param string|null $license
     */
    public function __construct($appName = null, $license = null)
    {
        $this->appName = $appName ?: ini_get('atatus.appname');
        $this->license = $license;
    }

    /**
     * Create a new transaction monitoring a given request
     *
     * @param \Swoole\Http\Request $request
     * @return Transaction
     */
    public function create(\Swoole\Http\Request $request)
    {
        return new Transaction($request, $this->appName, $this->license);
    }
}