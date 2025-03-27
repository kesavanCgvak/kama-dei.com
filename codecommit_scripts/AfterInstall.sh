#!/bin/bash

# Change directory to the path where you want to do the git pull

# check out the branch
git -C /var/www/html/login.kama-dei.com checkout prep

# ignore local changes
git -C /var/www/html/login.kama-dei.com reset --hard


# Pull changes from the remote repository
git -C /var/www/html/login.kama-dei.com pull origin  prep

cd /tmp/CodeDeploy

echo "The AfterInstall deployment lifecycle event successfully completed." > after-install.txt
echo $(date) >> after-install.txt
