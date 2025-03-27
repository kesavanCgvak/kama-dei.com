#!/bin/bash
cd /var/www/html/login.kama-dei.com

# Not undestanding why this does not work on the first run
git pull origin prep

# get the status in to the logfile
git status | tee /tmp/CodeDeploy/validate-service.txt                                                                                                                          

cd /tmp/CodeDeploy

echo "The ValidateService deployment lifecycle event successfully completed." | tee -a validate-service.txt

unset FOLDER
# Define the URL to check
url="https://prep.kama-dei.com/"

# Try to fetch the URL and store the response in a variable
response=$(curl -sSL -w "%{http_code}" $url -o /dev/null)

# Check if the response code indicates success (2xx)
if [[ "$response" =~ ^2 ]]; then
	  echo "Success: $url is available with response code $response" | tee -a /tmp/CodeDeploy/validate-service.txt
	    exit 0
    else
	      echo "Error: $url is not available with response code $response" | tee -a /tmp/CodeDeploy/validate-service.txt
	        exit 1
fi
