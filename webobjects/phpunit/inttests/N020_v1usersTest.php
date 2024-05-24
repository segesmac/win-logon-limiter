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

        // Test insertion of another new user
        ob_start();
        $test_username2 = 'adoe';
        insert_user($test_username2);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $test_username2 inserted successfully!", ($output_object->{'status_message'}));
        $this->assertEquals(1, ($output_object->{'status'}));

        // Test get_user with specific username that exists
        ob_start();
        get_users($test_username);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("$test_username found successfully!", ($output_object->{'status_message'}));
        $this->assertEquals(1, ($output_object->{'status'}));
        #var_dump($output_object->{'payload'}); 
        $this->assertEquals(1, ($output_object->{'payload'}["usertimetableid"]));
        /*["usertimetableid"]=>
        int(1)
        ["lastrowupdate"]=>
        NULL
        ["username"]=>
        string(4) "jdoe"
        ["isloggedon"]=>
        int(0)
        ["lastlogon"]=>
        NULL
        ["lastheartbeat"]=>
        NULL
        ["timelimitminutes"]=>
        string(5) "-1.00"
        ["timeleftminutes"]=>
        string(5) "-1.00"
        ["bonustimeminutes"]=>
        string(4) "0.00"
        ["computername"]=>
        NULL
        ["bonuscounters"]=>
        string(4) "0.00"*/

        // Test get_user without specifying user
        ob_start();
        get_users($test_username);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Found users successfully!", ($output_object->{'status_message'}));
        $this->assertEquals(1, ($output_object->{'status'}));
        var_dump($output_object->{'payload'}); 

    }
}
