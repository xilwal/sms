<?php 

namespace Xilwal\Sms\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class GupshupGateway implements SmsGatewayInterface {

    protected $param = array();
    protected $url = 'http://enterprise.smsgupshup.com/GatewayAPI/rest?';
    protected $request = '';
    public $status = false;
    public $response = '';
    public $countryCode='';

    function __construct()
    {
        $this->param['send_to'] = '';
        $this->param['msg'] = '';
        $this->param['method'] = 'sendMessage';
        $this->param['userid'] = Config::get('sms.gupshup.userid');
        $this->param['password'] = Config::get('sms.gupshup.password');
        $this->param['v'] = "1.1";
        $this->param['msg_type'] = "TEXT";
        $this->param['auth_scheme'] = "PLAIN";
        $this->countryCode = Config::get('sms.countryCode');
    }

    public function getUrl()
    {
        foreach($this->param as $key=>$val) {
            $this->request.= $key."=".urlencode($val);
            $this->request.= "&";
        }
        $this->request = substr($this->request, 0, strlen($this->request)-1);
        return $this->url.$this->request;

    }

    public function sendSms($mobile,$message)
    {
        $mobile = $this->addCountryCode($mobile);

        if(is_array($mobile)){
            $mobile = $this->composeBulkMobile($mobile);
        }

        $this->param['send_to'] = $mobile;
        $this->param['msg'] = $message;
        $client = new \GuzzleHttp\Client();
        $this->response = $client->get($this->getUrl())->getBody()->getContents();
        Log::info('Gupshup SMS Response: '.$this->response);
        return $this;

    }



    /**
     * Create Send to Mobile for Bulk Messaging
     * @param $mobile
     * @return string
     */
    private function composeBulkMobile($mobile)
    {
        return implode(',',$mobile);
    }

    /**
     * Prepending Country Code to Mobile Numbers
     * @param $mobile
     * @return array|string
     */
    private function addCountryCode($mobile)
    {
        if(is_array($mobile)){
            array_walk($mobile, function(&$value, $key) { $value = $this->countryCode.$value; });
            return $mobile;
        }

        return $this->countryCode.$mobile;
    }



    /**
     * Check Response
     * @return array
     */
    public function response(){
        $success = substr_count($this->response,'success');
        $error = substr_count($this->response,'error');

        return ['status'=>['success'=>$success,'error'=>$error],'response'=>$this->response];
    }

}