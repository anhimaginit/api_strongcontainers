<?php
class EmailAdress{
    public $admin_email;
    public $admin_name;
    public $admin_id;
    public $accountant_email;
    public $accountant_name;
    public $domain_path;
    public $api_path;

    function __construct() {
        $this->admin_email = "marketing@freedomhw.com";
        $this->admin_name = "Admin system";
        $this->admin_id = 2;
        $this->accountant_email = "marketing@at1ts.com";
        $this->accountant_name = "Accountant";
        $this->domain_path='https://salescontrolcenter.com';
        //$this->domain_path='https://strongtransportllc.com';
        $this->api_path='https://api.salescontrolcenter.com';
    }
}

?>
