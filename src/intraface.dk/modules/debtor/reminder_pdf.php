<?php
require('../../include_first.php');

$kernel->module("debtor");
$mainInvoice = $kernel->useModule("invoice");

$reminder = new Reminder($kernel, intval($_GET["id"]));
$reminder->pdf();