cd pluginsDev/modules/
find . -maxdepth 1 -mindepth 1 -type d -exec sh -c '(echo {} && cd {} && pwd && git push origin master && echo)' \;
