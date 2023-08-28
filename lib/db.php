<?php
class dbConnect{

    // specify your own database credentials
    private $host = "35.238.134.226";
    private $db_name = "freedomhw_crm_production";
    private $username = "freedom-crm-web";
    private $password = "FreedomIsKey!";
    protected $con;

    function __construct(){
        $this->con=new mysqli($this->host,$this->username,$this->password,$this->db_name);
        //$this->con->set_charset('utf8');
        mysqli_query($this->con,"SET NAMES 'utf8'");
        mysqli_query($this->con,"SET CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'");
        if ($this->con->connect_error){
            die ("Failed to connect to MySQL: " . mysqli_connect_error());
        }

    }
}?>