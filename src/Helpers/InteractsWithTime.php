<?php namespace WAUQueue\Helpers;

use DateTimeInterface;

trait InteractsWithTime
{
    /**
     * Get the number of seconds until the given DateTime.
     *
     * @param  \DateTimeInterface $delay
     *
     * @return int
     */
    protected function secondsUntil($delay) {
        return $delay instanceof DateTimeInterface ? max(0,
            $delay->getTimestamp() - $this->currentTime()
        ) : (int)$delay;
    }
}
