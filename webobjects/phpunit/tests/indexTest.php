<?php

require_once '/app/html/index.php';

#[PHPUnit\Framework\Attributes\CoversNothing]
class IndexTest extends PHPUnit\Framework\TestCase
{
 public function testOutput()
 {
    // Capture the output of index.php
    ob_start();
    include '/app/html/index.php';
    $output = ob_get_clean();

    // Assert that the output starts and ends with <html></html>
    $this->assertStringStartsWith("<html>", trim($output));
    $this->assertStringEndsWith("</html>", trim($output));
 }
}
