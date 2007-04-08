<?php
$_setting['cms.stylesheet.site'] = '';

// den her skal opdeles i fuldstændig defaults og defaults (systemnødvendige) og det som giver et standardlayout
$_setting['cms.stylesheet.default'] = '
.cms-float-left {
	float: left;
	margin-right: 0.5em;
}

.cms-float-center {
	float: center;
}

.cms-float-right {
	float: right;
	margin-left: 0.5em;	
}

.cms-align-left {
	text-align: left;
}

.cms-align-right {
	text-align: right;
}

.cms-align-center {
	text-align: center;
}
.cms-align-center .picture {
	text-align: center;
	margin: auto;
}

.cms-box {
	border: 2px solid #ccc;
	background: #eee;
	padding: 1em;
}
';

?>