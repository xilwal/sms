<?php 

namespace Xilwal\Sms\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ClickatellGateway implements SmsGatewayInterface {

    protected $param = array();
    protected $url = 'http://api.clickatell.com/http/sendmsg?';
    protected $request = '';
    public $status = false;
    public $response = '';

    function __construct()
    {
        $this->param['to'] = '';
        $this->param['text'] = '';
        $this->param['api_id'] = Config::get('sms.clickatell.api_id');
        $this->param['user'] = Config::get('sms.clickatell.user');
        $this->param['password'] = Config::get('sms.clickatell.password');
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
        if(is_array($mobile)){
            $mobile = $this->composeBulkMobile($mobile);
        }

        $this->param['to'] = $mobile;
        $this->param['text'] = $message;
        $client = new \GuzzleHttp\Client();
        $this->response = $client->get($this->getUrl())->getBody()->getContents();
        Log::info('Clickatell SMS Response: '.$this->response);
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
     * Check Response
     * @return array
     */
    public function response(){
       return $this->response;
    }

}