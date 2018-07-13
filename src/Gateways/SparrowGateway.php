<?php 

namespace Xilwal\Sms\Gateways;

use Illuminate\Support\Facades\Config;

class SparrowGateway implements SmsGatewayInterface {

    protected $param = array();
    protected $url = 'api.sparrowsms.com/v2/sms/?';
    protected $request = '';
    public $status = false;
    public $response = '';
    public $countryCode = '+977';

    function __construct()
    {
        $this->param['to'] = '';
        $this->param['text'] = '';
        $this->param['token'] = Config::get('sms.sparrow.token');
        $this->param['from'] = Config::get('sms.sparrow.from');
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
            $this->composeBulkMobile($mobile, $message);
        } else {
            $this->composeSingleMobile($mobile, $message);
        }

        return $this;
    }

    /**
     * Create Send to Mobile for Bulk Messaging
     * @param $mobile
     * @return string
     */
    private function composeSingleMobile($mobile, $message)
    {
        $this->param['to'] = $mobile;
        $this->param['text'] = $message;
        $client = new \GuzzleHttp\Client();
        $this->response = $client->post($this->getUrl(),['form_params'=>$this->param])->getBody()->getContents();
        return $this->response;
    }

    /**
     * Create Send to Mobile for Bulk Messaging
     * @param $mobile
     * @return string
     */
    private function composeBulkMobile($mobile, $message)
    {
        foreach ($mobile as $mobile_no) {
            $this->composeSingleMobile($mobile_no, $message);
        }
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
        $response = json_decode($this->response);
        return $response;
    }

}