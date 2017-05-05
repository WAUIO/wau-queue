<?php

namespace WAUQueue\Helpers;

/**
 *
 * @author Andrianina OELIMAHEFASON
 */
trait PayloadTrait {
    public function unserializePayload($payload) {
        return json_decode($payload);
    }
}
