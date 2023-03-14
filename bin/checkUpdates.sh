#!/bin/sh

git pull origin $(git rev-parse --abbrev-ref HEAD)
echo ""

if [ -d "pluginsDev" ] 
then
    cd pluginsDev/modules/
    find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git pull origin $(git rev-parse --abbrev-ref HEAD) && echo)' \;
    cd ../
    
    cd vendors/
    find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git pull origin $(git rev-parse --abbrev-ref HEAD) && echo)' \;

    cd ../../
fi

cd plugins/modules/
find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git pull origin $(git rev-parse --abbrev-ref HEAD) && echo)' \;
cd ../

cd vendors/
find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git pull origin $(git rev-parse --abbrev-ref HEAD) && echo)' \;
cd ../../

echo "Updates Checking Complete";
echo ""
