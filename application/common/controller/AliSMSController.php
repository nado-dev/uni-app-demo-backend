<?php

namespace app\common\controller;
// 引入阿里sdk
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use app\lib\exception\BaseException;

class AliSMSController 
{
   static public function SendSMS($phone, $code){
      AlibabaCloud::accessKeyClient(config('api.alisms.accessKeyId'), config('api.alisms.accessSecret'))
                        ->regionId(config('api.alisms.regionId'))
                        ->asDefaultClient();
      try {
         $result = AlibabaCloud::rpc()
                              ->product(config('api.alisms.product'))
                              // ->scheme('https') // https | http
                              ->version(config('api.alisms.version'))
                              ->action('SendSms')
                              ->method('POST')
                              ->host('dysmsapi.aliyuncs.com')
                              ->options([
                                             'query' => [
                                                'RegionId' => config('api.alisms.regionId'),
                                                'PhoneNumbers' => $phone,
                                                'SignName' => config('api.alisms.SignName'),
                                                'TemplateCode' =>  config('api.alisms.TemplateCode'),
                                                'TemplateParam' => '{"code":"'.$code.'"}',
                                             ],
                                          ])
                              ->request();
         return $result->toArray();
      } catch (ClientException $e) {
         throw new BaseException(['code'=>200, 'msg'=>$e->getErrorMessage(),
         'errorCode'=>30000]);
      } catch (ServerException $e) {
         throw new BaseException(['code'=>200, 'msg'=>$e->getErrorMessage(),
         'errorCode'=>30000]);
      }
   }
}
