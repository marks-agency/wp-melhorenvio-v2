<?php

namespace Controllers;
use Models\Address;


class UsersController {

    public function __construct(){

    }

    public function getFrom() {

        $info = $this->getInfo();
        return (object) [
            "name" => $info->data->firstname . ' ' . $info->data->lastname,
            "phone" => $this->mask($info->data->phone->phone, "(##)####-####"),
            "email" => $info->data->email,
            "document" => $info->data->document,
            "company_document" => null, // TODO
            "state_register" => null, // TODO
            "address" => $info->data->address->address,
            "complement" => $info->data->address->complement,
            "number" => $info->data->address->number,
            "district" => $info->data->address->district,
            "city" => $info->data->address->city->city,
            "state_abbr" => $info->data->address->city->state->state_abbr,
            "country_id" => $info->data->address->city->state->country->id,
            "postal_code" => $info->data->address->postal_code
        ];
    }

    public function getInfo() {

        $dataUser = get_option('melhorenvio_user_info');
        if (!$dataUser) {
            $token = get_option('melhorenvio_token');

            $params = array('headers'=>[
                'Content-Type' => 'application/json',
                'Accept'=>'application/json',
                'Authorization' => 'Bearer '.$token],
            );

            $response = wp_remote_retrieve_body(wp_remote_get('https://www.melhorenvio.com.br/api/v2/me', $params));

            if (is_null($response)) {
                return [
                    'error' => true,
                    'message' => 'Erro ao consultar o servidor'
                ];  
            }

            $data = get_object_vars(json_decode($response));
            add_option('melhorenvio_user_info', $data);
            return [
                'success' => true,
                'data' => $data
            ];
        } 

        return  (object) [
            'success' => true,
            'data' => (object) $dataUser
        ];

    }

    public function getTo($order_id) {
        
        $order = new \WC_Order($order_id);

        return (object) [
            "name" => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            "phone" => $order->get_billing_phone(),
            "email" => $order->get_billing_email(),
            "document" => null,
            "company_document" => null, // (opcional) (a menos que seja transportadora e logística reversa)
            "state_register" => null, // (opcional) (a menos que seja transportadora e logística reversa)
            "address" => $order->get_billing_address_1(),
            "complement" => $order->get_billing_address_2(),
            "number" => null,
            "district" => null,
            "city" => $order->get_billing_city(),
            "state_abbr" => $order->get_billing_state(),
            "country_id" => $order->get_billing_country(),
            "postal_code" => str_replace('-', '', $order->get_billing_postcode()),  
        ];

    }

    public function getBalance() {
        $usr = new \Models\User();
        echo json_encode(
            $usr->getBalance()
        );
        die;
    }

    private function mask($val, $mask){
        $maskared = '';
        $k = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++) {
            if($mask[$i] == '#') {
                if(isset($val[$k]))
                    $maskared .= $val[$k++];
                }
                else
                {
                if(isset($mask[$i]))
                $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    public function getAddressShopping() {
        $address = new Address();
        echo json_encode($address->getAddressesShopping());
        die;
    }
}
