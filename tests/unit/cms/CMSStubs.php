<?php

class FakeUser {
    function get() {
        return 1;
    }
    function hasModuleAccess() {
        return true;
    }
}

class FakeIntranet {
    function get() {
        return 1;
    }
    function hasModuleAccess() {
        return true;
    }
}

class FakeSetting {
    function get() {
        return 1;
    }
    function set() {
        return true;
    }
}

class FakePage {
    public $kernel;
    function __construct($site) {
        $this->cmssite = $site;
        $this->kernel = $site->kernel;
    }
    function get() {
        return 1;
    }
}

class FakeSite {
    public $kernel;
    function __construct($kernel) {
        $this->kernel = $kernel;
    }
    function get() {
        return 1;
    }
}

?>