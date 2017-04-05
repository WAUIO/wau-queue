<?php namespace WAUQueue\Adapter\RabbitMQ;


use WAUQueue\Contracts\Client\ConsumerAbstract;
use WAUQueue\Contracts\Client\ConsumerInterface;
use WAUQueue\Exception\ConsumerError;

/**
 * Class Consumer
 *
 * @package WAUQueue\Adapter\RabbitMQ
 */
class Consumer extends ConsumerAbstract
{
    /**
     * @param array $strategy
     *
     * @return $this
     * @throws \WAUQueue\Exception\ConsumerError
     */
    public function swallow(array $strategy = array()) {
        // the callback should be set before consuming, even empty function
        if(!$this->callback) {
            throw new ConsumerError('No callback set for this consumer. cannot continue');
        }
    
        if(!$this->queue) {
            throw new ConsumerError('No queue attached to the consumer');
        }
        
        $this->tag = $this->channel->channel()->get()->basic_consume($this->queue->getName(),
            $this->prop('consumer_tag', ''),
            $this->prop('no_local', false),
            $this->prop('no_ack', false),
            $this->prop('exclusive', false),
            $this->prop('nowait', false),
            $this->callback,
            $this->prop('ticket'),
            $this->prop('arguments')
        );

        print_r(". Consumer with tag {$this->tag} is listening to {$this->queue->getName()}...\n");
        
        return $this;
    }
}