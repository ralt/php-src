--TEST--
FPM: Test getallheaders() function
--SKIPIF--
<?php include "skipif.inc"; ?>
--FILE--
<?php

include "include.inc";

$logfile = __DIR__.'/php-fpm.log.tmp';
$srcfile = __DIR__.'/php-fpm.tmp.php';
$port = 9000+PHP_INT_SIZE;

$cfg = <<<EOT
[global]
error_log = $logfile
[unconfined]
listen = 127.0.0.1:$port
pm = dynamic
pm.max_children = 5
pm.start_servers = 1
pm.min_spare_servers = 1
pm.max_spare_servers = 3
EOT;

$code = <<<EOT
<?php
echo "Test Start\n";
var_dump(getallheaders());
echo "Test End\n";
EOT;
file_put_contents($srcfile, $code);

$fpm = run_fpm($cfg, $tail);
if (is_resource($fpm)) {
    fpm_display_log($tail, 2);
    try {
		$headers = ['HTTP_X_TEST' => 'Pass'];
		$req = run_request('127.0.0.1', $port, $srcfile, '', $headers);
		echo strstr($req, "Test Start");
		echo "Request ok\n";
	} catch (Exception $e) {
		echo "Request error\n";
	}
    proc_terminate($fpm);
    echo stream_get_contents($tail);
    fclose($tail);
    proc_close($fpm);
}

?>
Done
--EXPECTF--
[%s] NOTICE: fpm is running, pid %d
[%s] NOTICE: ready to handle connections
Test Start
array(1) {
  ["X-Test"]=>
  string(4) "Pass"
}
Test End

Request ok
[%s] NOTICE: Terminating ...
[%s] NOTICE: exiting, bye-bye!
Done
--CLEAN--
<?php
	$logfile = __DIR__.'/php-fpm.log.tmp';
	$srcfile = __DIR__.'/php-fpm.log.tmp';
    @unlink($logfile);
    @unlink($srcfile);
?>