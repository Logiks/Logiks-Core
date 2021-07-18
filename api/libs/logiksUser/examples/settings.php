<?php
//This code sample is used to test the various aspects of Settings System


printArray([
        getUserConfig("A1"),
        setUserConfig("A1", "123"),
        getUserConfig("A1"),
    ]);
    
printArray([
        getSiteSettings("A1"),
        setSiteSettings("A1", "XXXX"),
        getSiteSettings("A1"),
    ]);
    
printArray([
        getUserConfig("A1"),
        getSiteSettings("A1"),
    ]);
?>