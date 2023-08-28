<?php
class Config{
    public $jwt_key;
    public $jwt_iss;
    public $jwt_aud;
    public $jwt_issuedAt;
    public $jwt_notBefore;
    public $jwt_expire;

    function __construct() {
        $this->jwt_key = "d2FycmFudHlfYnJhbmRvbl9wcm9qZWN0";
        $this->jwt_iss = "http://warrantyproject.com";
        $this->jwt_aud = "http://warrantyproject.com";
        $this->jwt_issuedAt = time();
        $this->jwt_notBefore = $this->jwt_issuedAt + 10;
        $this->jwt_expire = $this->jwt_notBefore + 90*60;
    }
}

?>
