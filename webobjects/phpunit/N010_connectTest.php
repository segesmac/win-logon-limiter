<?php

require_once '/var/www/html/api/connect.php';

#[PHPUnit\Framework\Attributes\CoversNothing]
class N010_connectTest extends PHPUnit\Framework\TestCase
{
 public function testOutput()
 {
    // Capture the output of connect.php
    ob_start();
    include '/var/www/html/api/connect.php';
    $output = ob_get_clean();

    // Assert that the output starts with Connection failed:, since there's no db to connect to
    $this->assertStringStartsNotWith("Connection failed: ", $output);
    $this->assertStringStartsNotWith("You must include a password variable", $output);
 }
}
?>