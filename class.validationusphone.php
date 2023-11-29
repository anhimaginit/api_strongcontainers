<?php

class ValidationPhone{
  public function validatedPhone($phone_number){
      $mobile = preg_replace('/\D/', '', $phone_number);
      $mobiletrim = ltrim($mobile, '1');

      if (preg_match ('/^[2-9]\d{9}$/', $mobiletrim) || empty($trimmed['mobile'])) {
          $mp = true;
          if (!empty($trimmed['mobile'])) { $mobiletrim = "1" . $mobiletrim; }
      } else {
          $profile_errors[] = "Please enter a valid U.S. phone number.";
          $mp =false;
      }
      return array("valid"=>$mp,"phone_number"=>$mobiletrim);
  }
    /////////////////////////////////////////////////////////
}
