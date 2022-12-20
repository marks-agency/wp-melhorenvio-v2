<?php
namespace MelhorEnvio\Controllers;

use MelhorEnvio\Services\TokenService;
use MelhorEnvio\Services\SessionNoticeService;

class OiMarkAddTaskController
{

  public function init(){
    
    add_action( 'admin_init', [ $this, 'getTokenEXP' ]);

  }

  public function getTokenEXP(){

    $tokenData = ( new TokenService() )->get();
    
    if(!empty($tokenData) ||  !empty($tokenData["token"])){

      $token = $tokenData["token"];
      $IsAboutToExpire = $this->checkIfTokenIsAboutToExpire($token);
    
      if($IsAboutToExpire){
        add_filter( "markshop_add_tasks",  [ $this, 'custom_task' ], 10, 1 );
      }else{
        ( new SessionNoticeService() )->removeNoticeTokenInvalid();
      } 
    }
  }
  
  public function checkIfTokenIsAboutToExpire($token){
    
    $payload = $this->getPayLoad($token);

    $exp = $payload->exp;
    
    $diff = $this->getDiffInMonth($exp);

    if($diff < 6 ){
      return true;
    } 
    
    return false;
  }

  public function getPayLoad($token){

    $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));

    return $payload;
  }

  public function getDiffInMonth($exp){

    // Declare two dates
    $start_date = strtotime(date('Y-m-d', time()));
    $end_date = strtotime(date('Y-m-d', $exp));
    $interval_in_days = (($end_date - $start_date)/60/60/24);
    $result = ceil($interval_in_days/30);
    return  $result;

  }

  function custom_task($tasks){
      $new_task = array(
        'id' => 'markshop-tasks-add-new-token-from-melhor-envio',
        'title' => "Renovar o Token da Melhor Envio",
        'content' => 'Renove e configure o seu Token em seu site',
        'description' => 'Renove e configure o seu Token em seu site',
        'containerContent' => file_get_contents($this->getTemplateDir()),
        'timeToComplete' => "5 minutos",
        'isDismissable' => false,
        'isComplete' => false,
        'verify' => false,
      );

    array_push($tasks, ...array($new_task));
    return $tasks;
  } 

  public function getTemplateDir(){
    $dir = plugin_dir_path( __DIR__ );
    $newDir = str_replace('wp-melhorenvio-v2','markshop-tasks',$dir);

    return $newDir."/views/melhor_envio.php";
  }
}
