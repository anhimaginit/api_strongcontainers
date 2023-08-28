<?php
    include_once './lib/emailaddress.php';
    include_once './lib/class.common.php';
   $Object = new Common();

    $Ob_manager = new EmailAdress();
    $ccAdd = $Ob_manager->admin_email;

   //$email="";
  // if(isset($_GET["email"])) $email = $_GET["email"];
   $Object->requiredUpdateDoc($ccAdd);



