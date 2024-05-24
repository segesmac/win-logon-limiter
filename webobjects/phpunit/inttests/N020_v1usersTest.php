<?php

require_once '/var/www/html/api/v1/users.php';

#[PHPUnit\Framework\Attributes\CoversNothing]
class N020_v1usersTest extends PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        // Testing get_users with empty user table
        ob_start();
        get_users();
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("No users exist!", ($output_object->{'status_message'}));
        $this->assertEquals(-1, ($output_object->{'status'}));

        // Test get_user with specific username that doesn't exist
        ob_start();
        $test_username = 'jdoe';
        get_users($test_username);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $test_username doesn't exist!", ($output_object->{'status_message'}));
        $this->assertEquals(-1, ($output_object->{'status'}));

        // Test insertion of new user
        ob_start();
        insert_user($test_username);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $test_username inserted successfully!", ($output_object->{'status_message'}));
        $this->assertEquals(1, ($output_object->{'status'}));

        // Test insertion of duplicate user
        ob_start();
        insert_user($test_username);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $test_username already exists.", ($output_object->{'status_message'}));
        $this->assertEquals(1, ($output_object->{'status'}));

    }
}
