# Logiks Messaging Services

This library helps Logiks Framework communicate with the world vai:

+ EMAIL
+ SMS
+ Notifications
+ Messaging
+ (Other API based mechs)


## Objecttive

Any api method that can be invoked by 3 input parms (max) ie. to, subject & body/template can be used
with logiksMsg system. The system calls upon the required api and returns the status. These system are 
syncronous and need to comlete the processing before the main thread continues. For aysnc messaging, please
install logiksQueue plugin.