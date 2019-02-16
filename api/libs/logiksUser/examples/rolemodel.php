<?php
//This code sample is used to test the various aspects of RoleModel System

RoleModel::cleanRoleModelCache();

printArray([
        checkUserRoles("masters","test","access"),
        checkUserRoles("masters","test","edit"),
        
        checkUserScope("masters.test"),
        checkUserScope("masters"),
        checkUserScope("unione"),
        
        checkUserPolicy("masters.test"),
        checkUserPolicy("content"),
        
        checkUserRoles("%7B%7BAVATAR%7D%7D","%7B%7BAVATAR%7D%7D"),
    ]);

printArray($_SESSION);//["ROLEMODEL"]
?>