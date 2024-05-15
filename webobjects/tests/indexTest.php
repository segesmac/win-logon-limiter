<?php

require_once '../html/index.php';

class IndexTest extends PHPUnit\Framework\TestCase
{
 public function testOutput()
 {
    // Capture the output of hello.php
    ob_start();
    include '../html/index.php';
    $output = ob_get_clean();

    // Assert that the output is "Hello, Docker!"
    $this->assertEquals("Hello, Docker!", $output);
 }
}
?>