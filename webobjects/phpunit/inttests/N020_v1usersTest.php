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
        $this->assertEquals(1, ($output_object->{'payload'}->{"usertimetableid"}));
        $this->assertEquals(null, ($output_object->{'payload'}->{"lastrowupdate"}));
        $this->assertEquals($test_username, ($output_object->{'payload'}->{"username"}));
        $this->assertEquals(0, ($output_object->{'payload'}->{"isloggedon"}));
        $this->assertEquals(null, ($output_object->{'payload'}->{"lastlogon"}));
        $this->assertEquals(null, ($output_object->{'payload'}->{"lastheartbeat"}));
        $this->assertEquals("-1.00", ($output_object->{'payload'}->{"timelimitminutes"}));
        $this->assertEquals("-1.00", ($output_object->{'payload'}->{"timeleftminutes"}));
        $this->assertEquals("0.00", ($output_object->{'payload'}->{"bonustimeminutes"}));
        $this->assertEquals(null, ($output_object->{'payload'}->{"computername"}));
        $this->assertEquals("0.00", ($output_object->{'payload'}->{"bonuscounters"}));

        // Test get_user without specifying user
        ob_start();
        get_users();
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Found users successfully!", ($output_object->{'status_message'}));
        $this->assertEquals(1, ($output_object->{'status'}));
        $this->assertEquals('1', ($output_object->{'payload'}[0]->{"usertimetableid"}));
        $this->assertEquals(null, ($output_object->{'payload'}[0]->{"lastrowupdate"}));
        $this->assertEquals($test_username, ($output_object->{'payload'}[0]->{"username"}));
        $this->assertEquals('0', ($output_object->{'payload'}[0]->{"isloggedon"}));
        $this->assertEquals(null, ($output_object->{'payload'}[0]->{"lastlogon"}));
        $this->assertEquals(null, ($output_object->{'payload'}[0]->{"lastheartbeat"}));
        $this->assertEquals("-1.00", ($output_object->{'payload'}[0]->{"timelimitminutes"}));
        $this->assertEquals("-1.00", ($output_object->{'payload'}[0]->{"timeleftminutes"}));
        $this->assertEquals("0.00", ($output_object->{'payload'}[0]->{"bonustimeminutes"}));
        $this->assertEquals(null, ($output_object->{'payload'}[0]->{"computername"}));
        $this->assertEquals("0.00", ($output_object->{'payload'}[0]->{"bonuscounters"}));
        $this->assertEquals('2', ($output_object->{'payload'}[1]->{"usertimetableid"}));
        $this->assertEquals(null, ($output_object->{'payload'}[1]->{"lastrowupdate"}));
        $this->assertEquals($test_username2, ($output_object->{'payload'}[1]->{"username"}));
        $this->assertEquals('0', ($output_object->{'payload'}[1]->{"isloggedon"}));
        $this->assertEquals(null, ($output_object->{'payload'}[1]->{"lastlogon"}));
        $this->assertEquals(null, ($output_object->{'payload'}[1]->{"lastheartbeat"}));
        $this->assertEquals("-1.00", ($output_object->{'payload'}[1]->{"timelimitminutes"}));
        $this->assertEquals("-1.00", ($output_object->{'payload'}[1]->{"timeleftminutes"}));
        $this->assertEquals("0.00", ($output_object->{'payload'}[1]->{"bonustimeminutes"}));
        $this->assertEquals(null, ($output_object->{'payload'}[1]->{"computername"}));
        $this->assertEquals("0.00", ($output_object->{'payload'}[1]->{"bonuscounters"}));

        // Test update_user empty username
        ob_start();
        $empty_user = '';
        update_user($empty_user, null, null, 1);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("You must include a username!", ($output_object->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'status'}));

        // Test update_user update login status true
        ob_start();
        update_user($test_username, null, null, 1);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $test_username updated successfully!", ($output_object->{'loginstatus'}->{'status_message'}));
        $this->assertEquals(1, ($output_object->{'loginstatus'}->{'status'}));

        // Test update_user update login status false
        ob_start();
        update_user($test_username, null, null, 0);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $test_username updated successfully!", ($output_object->{'loginstatus'}->{'status_message'}));
        $this->assertEquals(1, ($output_object->{'loginstatus'}->{'status'}));

        // Test update_user update login status user doesn't exist
        ob_start();
        $nonexistant_user = 'idontexist';
        update_user($nonexistant_user, null, null, 0);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'loginstatus'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'loginstatus'}->{'status'}));
        $this->assertEquals(0, ($output_object->{'bonusminutes'}->{'status'}));

        // Test update_user set bonus minutes to some value
        ob_start();
        $test_bonusminutes = 30;
        update_user($test_username, null, null, null, $test_bonusminutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Set bonusminutes to $test_bonusminutes for $test_username successfully!", ($output_object->{'bonusminutes'}->{'status_message'}));
        $this->assertEquals($test_bonusminutes, ($output_object->{'bonusminutes'}->{'status'}));

        // Test update_user set bonus minutes to some value for username that doesn't exist
        ob_start();
        $test_bonusminutes = 30;
        update_user($nonexistant_user, null, null, null, $test_bonusminutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'bonusminutes'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'bonusminutes'}->{'status'}));


    }
}
