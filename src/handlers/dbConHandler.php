<?php

trait dbConHandler {

    function connectDB($query, $params = []) {
        
        if (!file_exists(self::USER_DB)) {
			$this->initDatabase();
		}
		
		$db = new PDO('sqlite:' . self::USER_DB);
		$statement = $db->prepare($query);
		$statement->execute($params);
		$result = $statement->fetchAll(PDO::FETCH_NAMED);
		return $result;
    }

    function initDatabase() {
        $db = new PDO('sqlite:' . self::USER_DB);
		
		foreach (self::DB_TABLES as $tableQuery) {
            $statement = $db->prepare($tableQuery);
		    $statement->execute();
        }
    }

    


}