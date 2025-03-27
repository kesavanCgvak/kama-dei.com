#!/bin/bash
export FOLDER=/tmp/CodeDeploy

if [ -d $FOLDER ]
then
	 rm -rf $FOLDER
fi

mkdir -p $FOLDER
