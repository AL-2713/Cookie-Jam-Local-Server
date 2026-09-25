<?php

// Handle loading user data, and creating user data when the userID does not exist

trait playerHandler {

    private function setNestedArrayValue($nestString, $value, $existingArray) {

        $temp =& $existingArray;

        foreach(explode(".", $nestString) as $key) {
            $temp =& $temp[$key];
        }
        $temp = $value;

        return $existingArray;
    }


    function initUserID() {
        $entries = array($_SERVER['HTTP_X_GS_CLIENTID'] ?? null, 
                        $this->params['p']['k'] ?? null);
        
        while ($this->userID == null) {
            foreach ($entries as $entry) {
                if ($entry != null) {
                    $this->userID = $entry;
                }
            }
        }
    }

    function getUser($createUserIfNotExists = false) {

        if ($this->userDataSet == true) {
            return;
        }

        $userData = $this->connectDB("SELECT * FROM users WHERE userID=?", [$this->userID]);

        if (count($userData) == 0 && $createUserIfNotExists) {
            $this->createUser($this->userID);
            return $this->getUser(false);
        }
        $userData = $userData[0];

        $saveDataString = $userData['saveData'];
        $userData['saveData'] = json_decode($saveDataString,true);

        $saveDataMainString = $userData['saveDataMain'];
        $userData['saveDataMain'] = json_decode($saveDataMainString,true);

        $this->userData = $userData;
        $this->userDataSet = true;

        $this->logMsg("- got user data", $this->userData);

    }


    function createUser($userID) {
        if ($userID == null) {
            return;
        }
        $userQuery = "INSERT INTO users (userID,saveData,saveDataMain) VALUES (?,?,?)";
        $defaultSave = $this->loadJson("baseSave",false);
        $defaultSaveMain = $this->loadJson("alternateSJson",false);

        $this->connectDB($userQuery, [$userID, $defaultSave, $defaultSaveMain]);
    }


}