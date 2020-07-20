<?php

namespace Apiz\Traits;

trait Hookable
{
    /**
     * @var callable
     */
    private $preHookFn = null;

    /**
     * @var callable
     */
    private $successHookFn = null;

    /**
     * @var callable
     */
    private $failsHookFn = null;

    /**
     * @return callable
     */
    public function getPreHookFn()
    {
        return $this->preHookFn;
    }

    /**
     * @param callable
     *
     * @return Hookable
     */
    public function bindPreHook(callable $fn)
    {
        $this->preHookFn = $fn;

        return $this;
    }

    /**
     * @return callable
     */
    public function getSuccessHookFn()
    {
        return $this->successHookFn;
    }

    /**
     * @param callable
     *
     * @return Hookable
     */
    public function bindSuccessHook(callable $fn)
    {
        $this->successHookFn = $fn;

        return $this;
    }

    /**
     * @return callable
     */
    public function getFailsHookFn()
    {
        return $this->failsHookFn;
    }

    /**
     * @param callable
     *
     * @return Hookable
     */
    public function bindFailsHook(callable $fn)
    {
        $this->failsHookFn = $fn;

        return $this;
    }

    protected function preHook($request)
    {
        return;
    }

    protected function successHook($response, $request)
    {
        return;
    }

    protected function failsHook($exception)
    {
        return;
    }

    /**
     * @param $request
     */
    private function executePreHooks($request)
    {
        if (is_null($this->preHookFn)) {
            $this->preHook($request);
        }

        if(is_callable($this->preHookFn)) {
            $preHookFn = $this->preHookFn;
            $preHookFn($request);
        }
    }

    /**
     * @param $response
     * @param $request
     */
    private function executeSuccessHooks($response, $request)
    {
        if (is_null($this->successHookFn)) {
            $this->successHook($response, $request);
        }

        if(is_callable($this->successHookFn)) {
            $successHookFn = $this->successHookFn;
            $successHookFn($response, $request);
        }
    }

    /**
     * @param $exceptions
     */
    private function executeFailHooks($exceptions)
    {
        if (is_null($this->failsHookFn)) {
            $this->failsHook($exceptions);
        }

        if(is_callable($this->failsHookFn)) {
            $failsHookFn= $this->failsHookFn;
            $failsHookFn($exceptions);
        }
    }
}
