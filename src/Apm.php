<?php
/**
 * Copyright © Atatus. All rights reserved.
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Atatus\Swoole\Atatus;

/**
 * Atatus APM (Application Performance Monitoring) instrumentation
 */
class Apm
{
    /**
     * @var Apm\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * Inject dependencies
     *
     * @param Apm\TransactionFactory $transactionFactory
     */
    public function __construct(Apm\TransactionFactory $transactionFactory)
    {
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Install monitoring instrumentation
     *
     * @param \Swoole\Http\Server $server
     * @throws \UnexpectedValueException
     */
    public function instrument(\Swoole\Http\Server $server)
    {
        // Dismiss monitoring unaware of transaction boundaries of the event loop execution model
       // atatus_end_transaction();

      if (extension_loaded('atatus')) { // ensure PHP agent is available
         atatus_end_transaction();
      }

        $server = new \Upscale\Swoole\Reflection\Http\Server($server);
        $server->setMiddleware($this->monitor($server->getMiddleware()));
    }

    /**
     * Decorate a given middleware with monitoring instrumentation
     *
     * @param callable $middleware
     * @return callable
     */
    public function monitor(callable $middleware)
    {
        return new Apm\TransactionDecorator($middleware, $this->transactionFactory);
    }
}
