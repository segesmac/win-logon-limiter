#!/bin/sh
# Argument = -p password -r root_password -v

usage()
{
cat << EOF
usage: $0 options

This script runs docker compose to set up the win logon limiter environment. It assumes the docker_compose.yml file is in the same directory as this script.

OPTIONS:
   -h      Show this message.
   -j      JWT secret key path to file. Can be passed as environment variable WEB_JWT_SECRET instead.
   -k      SSH Known Hosts path to file. Can be passed as environment variable CRON_SSH_KNOWN_HOSTS instead.
   -p      The win logon limiter database password. Can be passed as environment variable DB_PASSWD instead.
   -r      The database root password. Can be passed as environment variable DB_RT_PASSWD instead.
   -s      SSH Private Key path to file. Can be passed as environment variable CRON_SSH_PRIVATE_KEY instead.
   -v      Verbose.
EOF
}

DB_PASSWD=$DB_PASSWD
DB_RT_PASSWD=$DB_RT_PASSWD
CRON_SSH_KNOWN_HOSTS=$CRON_SSH_KNOWN_HOSTS
CRON_SSH_PRIVATE_KEY=$CRON_SSH_PRIVATE_KEY
WEB_JWT_SECRET=$WEB_JWT_SECRET
WEB_JC_API_KEY=$WEB_JC_API_KEY
VERBOSE=
while getopts "a:hj:k:p:r:s:v" OPTION
do
     case $OPTION in
         a)  WEB_JC_API_KEY=$OPTARG
             ;;
         h)
             usage
             exit 0
             ;;
         j)
             WEB_JWT_SECRET=$OPTARG
             ;;
         k)
             CRON_SSH_KNOWN_HOSTS=$OPTARG
             ;;
         p)
             DB_PASSWD=$OPTARG
             ;;
         r)
             DB_RT_PASSWD=$OPTARG
             ;;
         s)
             CRON_SSH_PRIVATE_KEY=$OPTARG
             ;;
         v)
             VERBOSE=1
             ;;
         ?)
             usage
             exit 1
             ;;
     esac
done

if [ -z $DB_PASSWD ] || [ -z $DB_RT_PASSWD ] || [ -z $CRON_SSH_KNOWN_HOSTS ] || [ -z $CRON_SSH_PRIVATE_KEY ] || [ -z $WEB_JC_API_KEY ]
then
     usage
     exit 1
fi

if [ -z $WEB_JWT_SECRET ]
then
     # generate random string
     echo "Generating random string for web_jwt_secret"
     head /dev/urandom | tr -dc A-Za-z0-9 | head -c44 > web_jwt_secret.txt
else
     cp -f $WEB_JWT_SECRET web_jwt_secret.txt
fi

echo "$DB_PASSWD" > db_password.txt
echo "$DB_RT_PASSWD" > db_root_password.txt
cp -f $CRON_SSH_KNOWN_HOSTS ssh_known_hosts.txt
cp -f $CRON_SSH_PRIVATE_KEY ssh_private_key.txt
cp -f $WEB_JC_API_KEY web_jc_api_key.txt

docker-compose -f docker-compose.yml up -d
