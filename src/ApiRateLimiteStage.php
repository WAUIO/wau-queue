<?php
namespace WAUQueue;
use WAUQueue\Contracts\AbstractStagePipeline;

/**
 * Description of ApiRateLimiteStage
 *
 * @deprecated
 * (No more in use from 2.x)
 *
 * @author Andrianina OELIMAHEFASON
 */
class ApiRateLimiteStage extends AbstractStagePipeline{
    public function __invoke($payload) {
        return $payload && 1;
    }
}
