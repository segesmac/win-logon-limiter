<?php

echo "Beginning testing!";

#[PHPUnit\Framework\Attributes\CoversNothing]
class N020_v1usersTest extends PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        // Testing get_users with empty user table
        echo "before buffer";
        ob_start();
        echo "Within buffer";
        require_once '/var/www/html/api/v1/users.php';
        echo "Included users.php";
        get_users();
        $output = ob_get_clean();
        echo $output;
        $output_object = json_decode($output);
        $this->assertStringEquals("No users exist!", ($output_object["status_message"]));
        $this->assertEquals(-1, ($output_object["status"]));
    }
}
