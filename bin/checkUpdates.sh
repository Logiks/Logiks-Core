#!/bin/sh

git pull origin master
echo ""

if [ -d "pluginsDev" ] 
then
    cd pluginsDev/modules/
    find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git pull origin master && echo)' \;
    cd ../
    
    cd vendors/
    find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git pull origin master && echo)' \;

    cd ../../
fi

cd pluginsDev/modules/
find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git pull origin master && echo)' \;
cd ../

cd vendors/
find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git pull origin master && echo)' \;
cd ../../

echo "Updates Checking Complete";
echo ""