<?php
namespace WAUQueue;
use WAUQueue\Contracts\AbstractStagePipeline;

/**
 * Description of ApiRateLimiteStage
 *
 * @author Andrianina OELIMAHEFASON
 */
class ApiRateLimiteStage extends AbstractStagePipeline{
    public function __invoke($payload) {
        return $payload && 1;
    }
}
