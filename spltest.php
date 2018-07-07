<?php
echo((memory_get_usage()/1024)."KB \n");
$x=(new splFixedArray(2097152));
die((memory_get_usage()/1024)."KB \n");
?>