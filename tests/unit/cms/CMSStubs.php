<?php

class FakeCMSUser {
    function get() {
        return 1;
    }
    function hasModuleAccess() {
        return true;
    }
}

class FakeCMSIntranet {
    function get() {
        return 1;
    }
    function hasModuleAccess() {
        return true;
    }
}

class FakeCMSSetting {
    function get() {
        return 1;
    }
    function set() {
        return true;
    }
}

class FakeCMSPage {
    public $kernel;
    function __construct($site) {
        $this->cmssite = $site;
        $this->kernel = $site->kernel;
    }
    function get() {
        return 1;
    }
}

class FakeCMSSite {
    public $kernel;
    function __construct($kernel) {
        $this->kernel = $kernel;
    }
    function get() {
        return 1;
    }
}

?>