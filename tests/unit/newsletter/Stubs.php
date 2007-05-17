<?php

class FakeNewsletterList {
    public $kernel;
    function get() {
        return 1;
    }
}

class FakeKernel {
    public $intranet;
    public $user;
}

class FakeIntranet {
    public function get() {
        return 1;
    }
}

class FakeUser {
    public function get() {
        return 1;
    }
}

class FakeAddress {
    function get() {
        return 'lars@legestue.net';
    }
}

class FakeContact {
    public $address;
    function __construct() {
        $this->address = new FakeAddress;
    }
    function get() {
        return 1;
    }
}


?>