<?php

require_once '/var/www/html/api/v1/admin_users.php';

#[PHPUnit\Framework\Attributes\CoversNothing]
class N030_v1admin_usersTest extends PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        // Test update_user empty username
        ob_start();
        $empty_user = '';
        update_user($empty_user, null, null, 1);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("You must include a username!", ($output_object->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'status'}));
        $test_username = 'jdoe';
        $nonexistant_user = 'idontexist';

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
        update_user($nonexistant_user, null, null, null, $test_bonusminutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'bonusminutes'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'bonusminutes'}->{'status'}));

        // Test update_user add minutes to the bonus pool
        ob_start();
        update_user($test_username, null, $test_bonusminutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Added $test_bonusminutes bonus minute(s) to $test_username successfully!", ($output_object->{'bonusminutesadd'}->{'status_message'}));
        $this->assertEquals($test_bonusminutes, ($output_object->{'bonusminutesadd'}->{'status'}));

        // Test update_user add minutes to the bonus pool for username that doesn't exist
        ob_start();
        update_user($nonexistant_user, null, $test_bonusminutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'bonusminutesadd'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'bonusminutesadd'}->{'status'}));

        // Test update_user set regular minutes to some value
        ob_start();
        $test_minutes = 30;
        update_user($test_username, null, null, null, null, $test_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Set timeleftminutes to $test_minutes for $test_username successfully!", ($output_object->{'timeleftminutes'}->{'status_message'}));
        $this->assertEquals($test_minutes, ($output_object->{'timeleftminutes'}->{'status'}));

        // Test update_user set regular minutes to some value for username that doesn't exist
        ob_start();
        update_user($nonexistant_user, null, null, null, null, $test_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'timeleftminutes'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'timeleftminutes'}->{'status'}));

        // Test update_user add minutes to the regular pool
        ob_start();
        update_user($test_username, null, null, null, null, null, $test_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Added $test_minutes timeleft minute(s) to $test_username successfully!", ($output_object->{'timeleftminutesadd'}->{'status_message'}));
        $this->assertEquals($test_minutes, ($output_object->{'timeleftminutesadd'}->{'status'}));

        // Test update_user add minutes to the regular pool for username that doesn't exist
        ob_start();
        update_user($nonexistant_user, null, null, null, null, null, $test_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'timeleftminutesadd'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'timeleftminutesadd'}->{'status'}));

        // Test update_user set time limit minutes to some value
        ob_start();
        $test_limit_minutes = 60;
        update_user($test_username, $test_limit_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("Set timelimitminutes to $test_limit_minutes for $test_username successfully!", ($output_object->{'timelimit'}->{'status_message'}));
        $this->assertEquals($test_limit_minutes, ($output_object->{'timelimit'}->{'status'}));

        // Test update_user set time limit minutes to some value for username that doesn't exist
        ob_start();
        update_user($nonexistant_user, $test_limit_minutes);
        $output = ob_get_clean();
        $output_object = json_decode($output);
        $this->assertEquals("User $nonexistant_user doesn't exist!", ($output_object->{'timelimit'}->{'status_message'}));
        $this->assertEquals(0, ($output_object->{'timelimit'}->{'status'}));


    }
}
