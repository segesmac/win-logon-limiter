# winlogonlimiter
A tool to log people off after x number of minutes, rather than limiting someone's time to certain hours of the day.  Scenario - a person is allotted 90 minutes a day to use the computer and wants to be able to use those 90 minutes any time during the day throughout the day.


# TODO
  - update webobjects/cron/manage_internet.php to allow a configurable value for the IP address of the ubiquiti firewall
  - update webobjects/password.php to allow a configurable value for password
  - update webobjects/sqlobjects/040-create_timeuser_user.sql to allow a configurable value for password
  - create a runner php script that will execute the sql files in the webobjects/sqlobjects folder
  - set up a way to continually update database schemas without losing data - maybe do this with versioned folders containing sql update scripts and a version value in a sql table

# How to install
  - Set up a raspberry pi or ubuntu system with the following commands:
    - #Set up docker - usually sudo apt install docker.io, but you might need to set up the apt repo first, too
    - #add user to docker group
    - #install docker-compose plugin - usually sudo apt-get install docker-compose-plugin
  - Run the setup.sh script