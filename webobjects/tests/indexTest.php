<?php

require_once '/app/html/index.php';

class IndexTest extends PHPUnit\Framework\TestCase
{
 public function testOutput()
 {
    // Capture the output of hello.php
    ob_start();
    include '/app/html/index.php';
    $output = ob_get_clean();

    // Assert that the output is "Hello, Docker!"
    $content = <<<'EOD'
<script src="includes/jquery.min.js" />
<link rel="stylesheet" href="includes/style.css" />

</head>
<body>
<!--Your candidate is: <h1 id=list>-</h1> Make the locals proud.
<h2 id=list2>-</h2> So-so-->
<div id="timetable">No data received.</div>
<div id="internetstatus">No data received.</div>
<script src="includes/timetable.js" />
</body>

</html>
EOD;
    $this->assertEquals($content, $output);
 }
}
?>