<?php

class globalSettings {

    const DB_TABLES = array('CREATE TABLE IF NOT EXISTS "users" ("userID" INTEGER,"saveData" TEXT, "saveDataMain" TEXT)',
                            'CREATE TABLE IF NOT EXISTS "logs" ("message" TEXT, "requestType" TEXT, "content" TEXT, "response" TEXT, "time" TEXT)');

    const USER_DB = "cookieJam.db";

    const LOG_DATA = true;

    function loadJson($fileName, $jsonDecode = true) {
        $fileLocation = "resources/json/$fileName.json";
        $fileContents = file_get_contents($fileLocation);

        if ($jsonDecode) {
            $fileContents = json_decode($fileContents,true);
        }
        
        return $fileContents;
    }


}