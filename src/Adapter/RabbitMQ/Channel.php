<?php namespace WAUQueue\Adapter\RabbitMQ;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use WAUQueue\Contracts\ClosableInterface;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\ObservableInterface;
use WAUQueue\Helpers\CollectionSet;
use WAUQueue\Worker;

class Channel implements ObservableInterface, ClosableInterface
{
    /**
     * @var AMQPStreamConnection
     */
    protected $stream;
    
    /**
     * @var null|AMQPChannel
     */
    protected $channel;
    
    protected $isUnique = false;
    
    public function __construct(AMQPStreamConnection $stream, $unique = true) {
        $this->stream   = $stream;
        $this->isUnique = $unique;
        $this->channel  = $this->get();
    }
    
    public function create() {
        return new self($this->stream, $this->isUnique);
    }
    
    public function channel() {
        return $this;
    }
    
    public function close() {
        if(!is_null($this->channel))
            $this->channel->close();
        
        if(!is_null($this->stream)){
            $this->stream->close();
        }
    }
    
    /**
     * @param null $id
     *
     * @return AMQPChannel
     */
    public function get($id = null) {
        if(is_null($id) && $this->isUnique) {
            if(!is_null($this->channel))
                return $this->channel;
        }
        
        return $this->stream->channel($id);
    }
    
    /**
     * @param \WAUQueue\Worker $worker
     *
     * @return Channel
     */
    public function consume(Worker $worker) {
        $channel = $this->get();
        print("Waiting for messages (Ctrl + C to exit)...\n");
        
        if ($worker->prop('prefetch.size', 0 ) > 0 || $worker->prop('prefetch.count', 0) > 0) {
            $channel->basic_qos(
                $worker->prop('prefetch.size', null),
                $worker->prop('prefetch.count', 1),
                null
            );
            print("== Prefetch [size={$worker->prop('prefetch.size', 'NULL')}, count={$worker->prop('prefetch.count', 1)}]\n");
        }
        
        $worker->applySetup($this);
        
        $worker->getConsumers()->each(function(Consumer $consumer){
            $consumer->consume();
        });
        
        while(count($channel->callbacks)) {
            $channel->wait();
        }
        
        return $this;
    }
}