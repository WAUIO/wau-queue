<?php namespace WAUQueue\Adapter\RabbitMQ;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use WAUQueue\Contracts\ClosableInterface;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\ObservableInterface;
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
    
    protected $isUnique = true;
    
    public function __construct(AMQPStreamConnection $stream, $unique = true) {
        $this->stream   = $stream;
        $this->isUnique = $unique;
        $this->channel  = $this->get();
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
        print_r("Waiting for messages...\n");
        
        $worker->behave($this);
        
        $worker->getQueues()->each(function(QueueInterface $queue, $name, AMQPChannel $channel) use($worker){
            $channel->basic_consume($name,
                $worker->prop('consumer_tag', ''),
                $worker->prop('no_local', false),
                $worker->prop('no_ack', false),
                $worker->prop('exclusive', false),
                $worker->prop('nowait', false),
                $worker->getCallback(),
                $worker->prop('ticket'),
                $worker->prop('arguments')
            );
        }, $channel);
        
        while(count($channel->callbacks)) {
            $channel->wait();
        }
        
        return $this;
    }
}