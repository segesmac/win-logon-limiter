<?php

require_once '/app/html/api/connect.php';

#[PHPUnit\Framework\Attributes\CoversNothing]
class IndexTest extends PHPUnit\Framework\TestCase
{
 public function testOutput()
 {
    // Capture the output of connect.php
    ob_start();
    include '/app/html/api/connect.php';
    $output = ob_get_clean();

    // Assert that the output starts with Connection failed:, since there's no db to connect to
    $this->assertStringStartsWith("Connection failed: ", $output);
 }
}
?>