<?php

// flag is shpctf{easy_filter_again}
system(\$_GET['marcus']); 
$page = "home";

if (isset($_GET["page"])) {
    $page = $_GET["page"];
}

$page .= ".php";

include $page;
?>
