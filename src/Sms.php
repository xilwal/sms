<?php 

namespace Xilwal\Sms;

use Xilwal\Sms\Gateways\ClickatellGateway;
use Xilwal\Sms\Gateways\CustomGateway;
use Xilwal\Sms\Gateways\GupshupGateway;
use Xilwal\Sms\Gateways\ItexmoGateway;
use Xilwal\Sms\Gateways\LogGateway;
use Xilwal\Sms\Gateways\MockerGateway;
use Xilwal\Sms\Gateways\MVaayooGateway;
use Xilwal\Sms\Gateways\SmsAchariyaGateway;
use Xilwal\Sms\Gateways\SmsCountryGateway;
use Xilwal\Sms\Gateways\SmsGatewayInterface;
use Xilwal\Sms\Gateways\SmsLaneGateway;
use Xilwal\Sms\Gateways\NexmoGateway;
use Xilwal\Sms\Gateways\SparrowGateway;

class Sms {

    protected $gateway;
    protected $view;

    /**
     * @param SmsGatewayInterface $gateway
     * @param SmsViewInterface $view
     */
    function __construct(SmsGatewayInterface $gateway,SmsViewInterface $view)
    {
        $this->gateway = $gateway;
        $this->view = $view;
    }

    public function send($mobile,$view,$params=[]){

        $message = $this->view->getView($view,$params)->render();
        return $this->gateway->sendSms($mobile,$message);
    }

    public function send_raw($mobile,$message){
        return $this->gateway->sendSms($mobile,$message);
    }

    public function gateway($name)
    {
        // Gateways : Log / Clickatell / Gupshup / MVaayoo / SmsAchariya / SmsCountry / SmsLane / Nexmo / Mocker/ Custom
        switch($name)
        {
            case 'Log':
                $this->gateway = new LogGateway();
                break;
            case 'Clickatell':
                $this->gateway = new ClickatellGateway();
                break;
            case 'Gupshup':
                $this->gateway = new GupshupGateway();
                break;
            case 'Itexmo':
                $this->gateway = new ItexmoGateway();
            case 'MVaayoo':
                $this->gateway = new MVaayooGateway();
                break;
            case 'SmsAchariya':
                $this->gateway = new SmsAchariyaGateway();
                break;
            case 'SmsCountry':
                $this->gateway = new SmsCountryGateway();
                break;
            case 'SmsLane':
                $this->gateway = new SmsLaneGateway();
                break;
            case 'Nexmo':
                $this->gateway = new NexmoGateway();
                break;
            case 'Mocker':
                $this->gateway = new MockerGateway();
                break;
            case 'Sparrow':
                $this->gateway = new SparrowGateway();
                break;
            case 'Custom':
                $this->gateway = new CustomGateway();
                break;
        }
        return $this;
    }
}