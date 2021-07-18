<?php
//This code sample is used to test the various aspects of RoleModel System

RoleModel::cleanRoleModelCache();

printArray([
        "ROLES-masters.test.access"=> checkUserRoles("masters","test","access"),
        "ROLES-masters.test"=> checkUserRoles("masters","test"),
        "ROLES-masters.test.create"=> checkUserRoles("masters","test", "create"),
        "ROLES-masters.test.edit"=> checkUserRoles("masters","test","edit"),
        "ROLES-masters.main"=> checkUserRoles("masters", "main"),
        
        "SCOPE-masters.test.access"=> checkUserScope("masters.test.access"),
        "SCOPE-masters.test"=> checkUserScope("masters.test"),
        "SCOPE-masters"=> checkUserScope("masters"),
        "SCOPE-masters.main"=> checkUserScope("masters.main"),
        "SCOPE-masters.test.create"=> checkUserScope("masters.test.create"),
        "SCOPE-masters.test.edit"=> checkUserScope("masters.test.edit"),
        
        "SCOPE-unione"=> checkUserScope("unione"),
        
        "POLICY-masters.test.access"=> checkUserPolicy("masters.test.access"),
        "POLICY-masters.test"=> checkUserPolicy("masters.test"),
        "POLICY-masters"=> checkUserPolicy("masters"),
        "POLICY-masters.test.edit"=> checkUserPolicy("masters.test.edit"),
        
        "POLICY-content"=> checkUserPolicy("content"),
        
        "DUMMY"=> checkUserRoles("%7B%7BAVATAR%7D%7D","%7B%7BAVATAR%7D%7D"),
    ]);

printArray([
        $_SESSION["ROLEMODEL_VERSION"],
        $_SESSION["ROLEMODEL"],
        $_SESSION["ROLEMODEL2"],
        $_SESSION["ROLESCOPE"],
        $_SESSION["ROLESGLOBAL"],
        $_SESSION["ROLESCOPEMAP"],
]);
?>