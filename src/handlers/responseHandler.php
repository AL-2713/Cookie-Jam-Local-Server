<?php

trait responseHandler {

    // Generate a random string for use in the "v" key in the response
    private function generateVString() {
        $chars = "abcdefghijklmnopqrstuvwxyz1234567890";
        $finalString = "";
        $i = 0;
        $stringLength = 7;
        while ($i < $stringLength) {
            $finalString .= substr($chars, rand(0, strlen($chars) - 1), 1);
            $i++;
        }

        return $finalString;
    }

    private function setAdObj($adName, $adObj) {
        $fullObj = array($adName, $adObj);
        $this->response['ad'][] = $fullObj;
    }


    private function formatSData() {
        if (array_key_exists("_", $this->params) && array_key_exists("c", $this->params['_']) && array_key_exists("cmd", $this->params['_']['c']) && $this->params['_']['c']['cmd'] == "bsp" && array_key_exists("s", $this->params)) {
            return $this->userData['saveDataMain'];
        }

        return true;
    }




    function initTimeStampResponse() {

        $timeStampObj = array();
        $timeStampObj['ts'] = array("ts"=>time());

        if ($this->requestType == "stu") {
            $snValue = $this->params['sn'] ?? 0;
            $this->response['v'] = $this->params['v'] + $snValue;
        
        } elseif ($this->requestType == "rsc") {
            // i "d" is in the request, generate a new "v" value, else return the "v" value in the request
            $this->response['v'] = array_key_exists("d", $this->params) ? $this->generateVString() : $this->params['v'];
        }


        if (array_key_exists("_", $this->params) && array_key_exists("h", $this->params['_'])) {
            $timeStampObj['h'] = true;
        }


        if (array_key_exists("an", $this->params)) {
            $this->response['ad'] = array();

            if (array_key_exists("ap", $this->params)) {
                $adObj = array();
                $adObj['connected'] = true;
                $adObj['networkId'] = $this->params['ap']['i'];
                $adObj['network'] = $this->params['ap']['n'];

                $this->setAdObj("social", $adObj);
            }
        }

        
        $this->response['s'] = $this->formatSData();
        $this->response['_'] = $timeStampObj;
    }

    function initSettingsRequest() {
        $this->initTimeStampResponse();
        $settingsJson = $this->loadJson("cJson");

        $settings = array();
        $settings['d'] = $settingsJson;
        $settings['v'] = 0;

        $hObj = array();
        $hObj['X-GS-ClientId'] = $this->params['p']['k'];

        $tsConfig = array();
        $tsConfig['cf'] = "eadf87b7d2bc595a9130b48d1fa226ea";
        $tsConfig['c'] = $settingsJson;
        $tsConfig['v'] = 8;
        $tsConfig['l'] = ["configFingerprint:eadf87b7d2bc595a9130b48d1fa226ea"];

        $playerObj = array();
        $playerObj['p'] = 129600000;
        $playerObj['a'] = "_80ea";
        $playerObj['d'] = $this->userData['saveData'];
        $playerObj['v'] = "a51bfc2";

        $this->response['p'] = $playerObj;
        $this->response['c'] = $settings;
        $this->response['_h'] = $hObj;
        $this->response['_']['c'] = $tsConfig;
    }

    function initMessageResponse() {
        $messages = array();
        $messages['messages'] = [];
        $messages['status'] = "ok";

        $this->response['r'] = array("r" => $messages);
    }


    // 
    function onError($errorString = "") {
        $this->response = "";
        
        $this->logMsg("! ERROR", $errorString);

        $this->endResponse();
    }

    



    // Determine and set the type of resposne
    function initResponse() {

        

        switch($this->requestType) {

            case "rsc":
            case "stu":
                $this->initTimeStampResponse();
                break;
            
            case "aOp":
                $this->initMessageResponse();
                break;
            
            case "srcv":
                $this->getUser(true);
                $this->initTimeStampResponse();
                $this->response['_']['c'] = $this->params['_']['c'];
                $this->response['u'] = $this->userData['saveDataMain']['d']['u'];
                break;
            
            case "sbs":
                $this->getUser(true);
                $this->initSettingsRequest();
                break;
            
            default:
                $this->onError("An unknown request was sent: " . $this->requestType);
                break;
        }

        $this->proccessSaveData_D();
        $this->proccessSaveData_SD();
        $this->proccessSaveData_SC();

        $this->logMsg("- Request sent", $this->params, true);
    }


}