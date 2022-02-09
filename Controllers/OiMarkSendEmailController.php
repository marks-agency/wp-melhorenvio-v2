<?php
namespace Controllers;

const REASON_CANCELED_USER = 'https://www.melhorrastreio.com.br/rastreio/';

class OiMarkSendEmailController
{

  public function sendEmail($postId){
    $subject = 'WC Send Mail Test';
    // load the mailer
    $mailer = WC()->mailer();

    $order = wc_get_order( $postId );

    $data = $order->get_data();

    $email_billing =  $data['billing']['email'];

    $melhor_envio_cod = get_post_meta( $postId, 'melhorenvio_tracking',true);

    $melhor_envio_cod_link = 'https://www.melhorrastreio.com.br/rastreio/'.$melhor_envio_cod;
    //echo $melhor_envio_cod_link;
    $message_for_email = '<a href="$melhor_envio_cod_link"> o teu codigo de rastreio é : $melhor_envio_cod';
    $array_sub_oi_mark = array(
      '$melhor_envio_cod_link'=>$melhor_envio_cod_link,
      '$melhor_envio_cod' =>$melhor_envio_cod
    );
    $message_for_email = strtr($message_for_email, $array_sub_oi_mark);

    //echo $message_for_email;
    $mailer->send($email_billing, $subject, $mailer->wrap_message( $subject, $message_for_email), '', '' );

    //$oi_mark_wc_emails = new WC_Emails();
	  /*$oi_mark_message = $oi_mark_wc_emails->wrap_message('email de teste','este é um email singular',true);
	  $oi_mark_wc_emails->send('claudionhangapc@gmail.com','Email de teste',$oi_mark_message,'','');*/
  }
}
