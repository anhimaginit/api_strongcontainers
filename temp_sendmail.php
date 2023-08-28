<?php
    include_once './lib/class.common.php';

   $Object = new Common();

   $email="";
  // if(isset($_GET["email"])) $email = $_GET["email"];
   $Object->generateEmailToAdminClaimSub("anh@at1ts.com","test","","");



