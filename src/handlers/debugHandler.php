<?php

// Handle logging of data to the sql database, intended for debugging requests

trait debugHandler {

    function logMsg($msgDescriptor, $msgContent = [], $logResponse = false) {
        if (!self::LOG_DATA) {
            return;
        }

        $logQuery = "INSERT INTO logs (message, requestType, content, response, time) VALUES (?,?,?,?,?)";
        $responseString = null;

        if (gettype($msgContent) == "array") {
            $msgContent = json_encode($msgContent);
        }

        if ($logResponse) {
            $responseString = json_encode($this->response,true);
        }

        $this->connectDB($logQuery, [$msgDescriptor, $this->requestType, $msgContent, $responseString, date("Y-m-d H:i:s")]);
    }


}