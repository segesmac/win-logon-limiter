<?php
$is_test = 1;
require_once '/var/www/html/api/v1/admin_users.php';

#[PHPUnit\Framework\Attributes\CoversNothing]
class N030_v1admin_usersTest extends PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        // Test modify_user empty username
        ob_start();
        $empty_user = '';
        modify_user($empty_user, null, null, 1);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("You must include a username!", ($output_object->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'status'}));

        // Set variables
        $test_username = 'jdoe';
        $nonexistant_user = 'idontexist';

        // Test modify_user set bonus minutes to some value
        ob_start();
        $test_bonusminutes = 30;
        modify_user($test_username, null, null, null, $test_bonusminutes);
        $output = ob_get_clean();
	$output_object = json_decode($output);
        $this->assertEquals("Set <span style='color: orange;'>bonusminutes</span>: <span style='color: red;'>0.00</span> → <span style='color: green;'>$test_bonusminutes</span> for <span style='color: blue;'>$test_username</span> successfully!", ($output_object->{'bonusminutes'}->{'status_message'}));
        $this->assertEquals($test_bonusminutes, ($output_object->{'bonusminutes'}->{'status'}));

        // Test modify_user set bonus minutes to some value for username that doesn't exist
        ob_start();
        modify_user($nonexistant_user, null, null, null, $test_bonusminutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'bonusminutes'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'bonusminutes'}->{'status'}));

        // Test modify_user add minutes to the bonus pool
        ob_start();
        modify_user($test_username, null, $test_bonusminutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Added $test_bonusminutes bonus minute(s) to 30.00 for $test_username successfully!", ($output_object->{'bonusminutesadd'}->{'status_message'}));
        $this->assertEquals($test_bonusminutes, ($output_object->{'bonusminutesadd'}->{'status'}));

        // Test modify_user add minutes to the bonus pool for username that doesn't exist
        ob_start();
        modify_user($nonexistant_user, null, $test_bonusminutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'bonusminutesadd'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'bonusminutesadd'}->{'status'}));

        // Test modify_user set regular minutes to some value
        ob_start();
        $test_minutes = 30;
        modify_user($test_username, null, null, null, null, $test_minutes);
        $output = ob_get_clean();
	$output_object = json_decode($output);
        $this->assertEquals("Set <span style='color: orange;'>timeleftminutes</span>: <span style='color: red;'>-1.00</span> → <span style='color: green;'>$test_minutes</span> for <span style='color: blue;'>$test_username</span> successfully!", ($output_object->{'timeleftminutes'}->{'status_message'}));
        $this->assertEquals($test_minutes, ($output_object->{'timeleftminutes'}->{'status'}));

        // Test modify_user set regular minutes to some value for username that doesn't exist
        ob_start();
        modify_user($nonexistant_user, null, null, null, null, $test_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'timeleftminutes'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'timeleftminutes'}->{'status'}));

        // Test modify_user add minutes to the regular pool
        ob_start();
        modify_user($test_username, null, null, null, null, null, $test_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Added $test_minutes timeleft minute(s) to 30.00 for $test_username successfully!", ($output_object->{'timeleftminutesadd'}->{'status_message'}));
        $this->assertEquals($test_minutes, ($output_object->{'timeleftminutesadd'}->{'status'}));

        // Test modify_user add minutes to the regular pool for username that doesn't exist
        ob_start();
        modify_user($nonexistant_user, null, null, null, null, null, $test_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'timeleftminutesadd'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'timeleftminutesadd'}->{'status'}));

        // Test modify_user set time limit minutes to some value
        ob_start();
        $test_limit_minutes = 60;
        modify_user($test_username, $test_limit_minutes);
        $output = ob_get_clean();
	$output_object = json_decode($output);

        $this->assertEquals("Set <span style='color: orange;'>limitminutes</span>: <span style='color: red;'>0.00</span> → <span style='color: green;'>$test_limit_minutes</span> for <span style='color: blue;'>$test_username</span> successfully!", ($output_object->{'timelimit'}->{'status_message'}));
        $this->assertEquals($test_limit_minutes, ($output_object->{'timelimit'}->{'status'}));

        // Test modify_user set time limit minutes to some value for username that doesn't exist
        ob_start();
        modify_user($nonexistant_user, $test_limit_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'timelimit'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'timelimit'}->{'status'}));


    }
}
