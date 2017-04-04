<?php namespace WAUQueue\Payload;


class JsonPayload extends AbstractPayload
{
    public function raw() {
        return json_encode($this->payload);
    }
}