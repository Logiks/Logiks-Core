#!/bin/sh

cd ..;
cp -R config/jsonConfig ../
find . -type d -name .git -exec sh -c "cd \"{}\"/../ && pwd && git pull origin master" >tmp/update.log \;
cp -Rv ../jsonConfig/* config/jsonConfig/
rm -R ../jsonConfig
