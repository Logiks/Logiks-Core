#!/bin/sh

cd pluginsDev/modules/
find . -type d  -maxdepth 1 -exec git --git-dir={}/.git --work-tree=$PWD/{} pull origin master \;

cd ../vendors/
find . -type d  -maxdepth 1 -exec git --git-dir={}/.git --work-tree=$PWD/{} pull origin master \;

