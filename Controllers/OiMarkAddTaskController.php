<?php
namespace MelhorEnvio\Controllers;

use MelhorEnvio\Services\TokenService;

class OiMarkAddTaskController
{

  public function init(){
    
    add_action( 'admin_init', [ $this, 'getTokenEXP' ]);

  }

  public function getTokenEXP(){

    $tokenData = ( new TokenService() )->get();
    
    if(!empty($tokenData) ||  !empty($tokenData["token"])){

      $token = $tokenData["token"];
      $tokenCondition = $this->checkIfTokenIsAboutToExpire($token);
    
      echo "<pre>";
        print_r($tokenCondition);
      echo "</pre>";
    }

   
    exit;
  }
  
  public function checkIfTokenIsAboutToExpire($token){
    
    $payload = $this->getPayLoad($token);

    $exp = $payload->exp;
    
    $diff = $this->getDiffInMonth($exp);

    if($diff < 6 ){
      return true ;
    } 
    return false;
  }

  public function getPayLoad($token){

    $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));

    return $payload;
  }

  public function getDiffInMonth($exp){

    $d1 = new \DateTime(date('Y-m-d', $exp));
    $d2 = new \DateTime(date('Y-m-d', time()));

    $interval = $d2->diff($d1);
    $result =  $interval->format('%m');
    $result = intval( $result );

    return  $result;

  }
}
