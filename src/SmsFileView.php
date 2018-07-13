<?php 

namespace Xilwal\Sms;

class SmsFileView implements SmsViewInterface {

    public function getView($view,$params)
    {
        return view($view,$params);
    }
}