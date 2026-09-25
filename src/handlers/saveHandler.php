<?php

// Handle save data requests in specific post params, then comit the changes

trait saveHandler {

    function proccessSaveData_D() {
        if (!array_key_exists("d", $this->params)) {
            return;
        }

        $this->getUser(true);
        $exampleSave = $this->userData['saveData']['d'] ?? array();

        $this->logMsg("- Save requested", $this->params['d']);

        foreach ($this->params['d'] as $dataEntry) {
            $key = str_replace("\\", "", $dataEntry[0]);
            $val = $dataEntry[1];

            // Keys starting with 'k' doesn't split any parts after the first .
            if (substr($key,0,2) == "k.") {
                $exampleSave['k'][substr($key,2)] = $val;
            
            } elseif (substr($key,-1) == "]") {
                $keyParts = explode(".", $key);
                $exampleSave[$keyParts[0]][$keyParts[1]][explode("[", $keyParts[2])[0]] = [$val];

            } elseif (strpos($key, ".") !== false) {
                $exampleSave = $this->setNestedArrayValue($key, $val, $exampleSave);
            
            } else {
                $exampleSave[$key] = $val;
            }
  
        }

        $this->userData['saveData']['d'] = $exampleSave;
        $this->userData['saveData']['v'] = $this->params['v'];
        $this->saveUserData();

        $this->logMsg("Testing save D", $exampleSave);
    }



    function proccessSaveData_SD() {
        if (!array_key_exists("sd", $this->params)) {
            return;
        }

        $this->getUser(true);
        $exampleSave = $this->userData['saveDataMain']['d'];

        foreach ($this->params['sd'] as $dataEntry) {
            $key = str_replace("\\", "", $dataEntry[0]);
            $val = $dataEntry[1] ?? [];
            $exampleSave = $this->setNestedArrayValue($key, $val, $exampleSave);
        }

        $this->userData['saveDataMain']['d'] = $exampleSave;
        $this->saveUserData();

        $this->logMsg("Testing save SD", $this->userData['saveDataMain']);
    }



    function proccessSaveData_SC() {
        if (!array_key_exists("sc", $this->params)) {
            return;
        }

        $this->getUser(true);
        $exampleSave = $this->userData['saveDataMain']['d']['u']['user'];

        foreach ($this->params['sc'] as $dataEntry) {
            $pDat = $dataEntry['p'] ?? array();

            switch($dataEntry['n']) {
                case "LevelScoreOp":
                    $exampleSave['user_game_scores'][$pDat['level']] = $pDat['score'];
                    break;
                
                case "StartLevelOp":
                    $levelString = $pDat['episode'] . "_" . $pDat['game'];
                    $currentLevelStarts = $exampleSave['user_stats']['counter_level_played'][$levelString] ?? 0;
                    $exampleSave['user_stats']['counter_level_played'][$levelString] = $currentLevelStarts + 1;
                    break;
                
                case "UnlockLevelOp":
                    $exampleSave['user_game_data']['last_unlocked_level_timestamp'] = $pDat['timestamp'];
                    break;
                
                case "TutorialLevelCompletedOp":
                    $exampleSave['user_game_data']['tutorial_level_completed'] = $pDat['level'];
                    break;
                
                case "LevelFailedOp":
                    $levelString = $pDat['episode'] . "_" . $pDat['game'];
                    $currentLevelStarts = $exampleSave['user_stats']['counter_level_failed'][$levelString] ?? 0;
                    $exampleSave['user_stats']['counter_level_failed'][$levelString] = $currentLevelStarts + 1;
                    break;
                
                default:
                    $this->logMsg("! MISSING SC LOGIC", $dataEntry);
                    break;
            }

        }

        $this->logMsg("Testing save SC", $this->userData['saveDataMain']);

        $this->userData['saveDataMain']['d']['u']['user'] = $exampleSave;
        $this->saveUserData();
    }



    // Comit any changes to the save data to the database
    function saveUserData() {
        $saveQuery = "UPDATE users SET saveData=?, saveDataMain=? WHERE userID=?";
        $this->connectDB($saveQuery, [json_encode($this->userData['saveData']), json_encode($this->userData['saveDataMain']), $this->userID]);
    }

}