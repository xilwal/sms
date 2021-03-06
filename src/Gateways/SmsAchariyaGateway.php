<?php 

namespace Xilwal\Sms\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SmsAchariyaGateway implements SmsGatewayInterface {

    protected $param = array();
    protected $url = '';
    protected $request = '';
    public $status = false;
    public $response = '';
    public $countryCode='';

    function __construct()
    {
        $this->param['uid'] = Config::get('sms.smsachariya.uid');
        $this->param['pin'] = Config::get('sms.smsachariya.pin');
        $this->param['sender'] = '';
        $this->param['route'] = '0';
        $this->param['mobile'] = '';
        $this->param['message'] = '';
        $this->param['push_id'] = 1;
        $this->url = "http://".Config::get('sms.smsachariya.domain')."/api/sms.php?";
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

        $this->param['mobile'] = $mobile;
        $this->param['message'] = $message;
        $client = new \GuzzleHttp\Client();
        $this->response = $client->post($this->getUrl(),['form_params'=>$this->param])->getBody()->getContents();
        Log::info('SMS Achariya Response: '.$this->response);
        return $this;
    }


    /**
     * Set the Route
     * @param int $route
     * @return $this
     */
    public function route($route=0){
        $this->param['route'] = $route;
        return $this;
    }


    /**
     * Set the Sender ID
     * @param $sender
     * @return $this
     */
    public function sender($sender){
        $this->param['sender'] = $sender;
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
        $client = new \GuzzleHttp\Client();
        $report = $client->post("http://".Config::get('sms.smsachariya.domain')."/api/dlr.php?",[
            'form_params'=>[
                'uid'=>$this->param['uid'],
                'pin'=>$this->param['pin'],
                'msgid'=>$this->response
            ]])->getBody()->getContents();
        $report = trim($report,',');
        Log::info('SMS Achariya Delivery Report: '.$report);
        $exrepos = explode(',',$report);
        $sent = 0;
        $delivered = 0;
        $dnd = 0;
        $error = 0;
        foreach($exrepos as $exrepo){
            if($exrepo=='Sent'){
                $sent++;
            }elseif($exrepo=='Delivered'){
                $delivered++;
            }elseif($exrepo=='DND'){
                $dnd++;
            }else{
                $error++;
            }
        }

        return ['status'=>[
            'sent'=>$sent,'delivered'=>$delivered,'dnd'=>$dnd,'error'=>$error
        ],'response'=>$this->response,'report'=>$report,'mobile'=>$this->param['mobile']];
    }


}