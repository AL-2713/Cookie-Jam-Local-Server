<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once("handlers/globalSettings.php");
require_once("handlers/dbConHandler.php");
require_once("handlers/debugHandler.php");
require_once("handlers/playerHandler.php");
require_once("handlers/responseHandler.php");
require_once("handlers/saveHandler.php");


class cookieJamRoot extends globalSettings {

    
    use dbConHandler;
    use debugHandler;
    use playerHandler;
    use responseHandler;
    use saveHandler;


    function loadParams() {
        $paramString = file_get_contents('php://input');
        
        if (array_key_exists("HTTP_CONTENT_ENCODING", $_SERVER) && $_SERVER['HTTP_CONTENT_ENCODING'] == "gzip") {
            $paramString = gzdecode($paramString);
        }

        $this->params = json_decode($paramString,true);
    }

    function endResponse() {
        die(json_encode($this->response));
    }



    function __construct() {
        $this->requestType = $_GET['requestType'];
        $this->response = array();
        $this->params = array();
        $this->userDataSet = false;
        $this->userID = null;
        
        $this->loadParams();
        $this->initUserID();

        $this->initResponse();
        
        $this->endResponse();
        
    }

}

new cookieJamRoot();
