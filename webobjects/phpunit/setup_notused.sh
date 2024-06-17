#!/bin/bash
# Argument = -p password -r root_password -v

usage()
{
cat << EOF
usage: $0 options

This script runs docker compose to set up the win logon limiter environment. It assumes the docker_compose.yml file is in the same directory as this script.

OPTIONS:
   -h      Show this message.
   -p      The win logon limiter database password. Can be passed as environment variable DB_PASSWD instead.
   -r      The database root password. Can be passed as environment variable DB_RT_PASSWD instead.
   -v      Verbose.
EOF
}

DB_PASSWD=$DB_PASSWD
DB_RT_PASSWD=$DB_RT_PASSWD
VERBOSE=
while getopts "hp:r:v" OPTION
do
     case $OPTION in
         h)
             usage
             exit 0
             ;;
         p)
             DB_PASSWD=$OPTARG
             ;;
         r)
             DB_RT_PASSWD=$OPTARG
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

if [[ -z $DB_PASSWD ]] || [[ -z $DB_RT_PASSWD ]]
then
     usage
     exit 1
fi

echo "$DB_PASSWD" > db_password.txt
echo "$DB_RT_PASSWD" > db_root_password.txt

docker-compose -f docker-compose-inttests.yml up -d
