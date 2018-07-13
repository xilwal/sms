<?php 

namespace Xilwal\Sms\Gateways;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SmsCountryGateway implements SmsGatewayInterface {

    protected $param = array();
    protected $url = 'http://api.smscountry.com/SMSCwebservice_bulk.aspx?';
    protected $request = '';
    public $status = false;
    public $response = '';
    public $countryCode='';

    function __construct()
    {
        $this->param['mobilenumber'] = '';
        $this->param['message'] = '';
        $this->param['User'] = Config::get('sms.smscountry.user');
        $this->param['passwd'] = Config::get('sms.smscountry.passwd');
        $this->param['sid'] = "";
        $this->param['mtype'] = "N";
        $this->param['DR'] = "Y";
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

        $this->param['mobilenumber'] = $mobile;
        $this->param['message'] = $message;
        $client = new \GuzzleHttp\Client();
        $this->response = $client->get($this->getUrl())->getBody()->getContents();
        Log::info('SmsCountry Response: '.$this->response);
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
        return $this->response;
    }

}