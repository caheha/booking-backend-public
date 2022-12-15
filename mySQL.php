<?php
    $mySQL = new MySQL();
    $mySQL->SetDatabase("YOUR_DATABASE_NAME");
    $mySQL->SetServer("YOUR_SERVER", "YOUR_USERNAME", "YOUR_PASSWORD");
    $mySQL->Connect();

    class MySQL {
        public $mySQL;
        protected $database;
        protected $hostname;
        protected $username;
        protected $password;

        public function SetDatabase($database) {
            $this->database = $database;
        }

        public function SetServer($hostname, $username, $password) {
            $this->hostname = $hostname;
            $this->username = $username;
            $this->password = $password;
        }

        public function Connect() {
            $this->mySQL = new mysqli($this->hostname, $this->username, $this->password, $this->database);
        }

        public function Disconnect() {
            $this->mySQL = null;
        }

        public function Query($query, $returnAsJSON = true) {
            $json = [];
            
            $result = $this->mySQL->query($query);

            if(!$result){ return $result; }

            if ($returnAsJSON) {
                $data = [];
                while($row = $result->fetch_object()) {
                    $data[] = $row;
                }
                
                $json["data"] = $data;
                return json_encode($json);
            } else {
                return $result;
            }
        }
    }
?>