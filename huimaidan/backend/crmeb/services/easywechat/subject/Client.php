<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace crmeb\services\easywechat\subject;

use crmeb\services\easywechat\BaseClient;
use think\exception\ValidateException;

/**
 * Class Client.
 *
 * @author ClouderSky <clouder.flow@gmail.com>
 */
class Client extends BaseClient
{

    const CAPITALLHH_BANKS = '/v3/capital/capitallhh/banks/';
    const AREAS_PROVINCES = '/v3/capital/capitallhh/areas/provinces';
    const SUBJECT_APPLYMENT = '/v3/applyment4sub/applyment/';

    protected $isService = true;

    /**
     * 特约商户申请
     * @param $params
     * @return mixed
     * @author Qinii
     */
    public function submitApplication($params)
    {
        $params = $this->processParams($params);
        $res = $this->request(self::SUBJECT_APPLYMENT, 'POST', ['sign_body' => json_encode($params,
            JSON_UNESCAPED_UNICODE)], true);
        if(isset($res['code'])) throw new ValidateException('[微信接口返回]:' . $res['message']);

        return $res;
    }

    /**
     * 申请单ID查询申请状态
     * @param $id
     * @return mixed
     * @author Qinii
     */
    public function getApplicationById($id)
    {
        $url = '/v3/applyment4sub/applyment/applyment_id/'.$id;
        $res = $this->request($url, 'GET');

        if(isset($res['code'])) throw new ValidateException('[微信接口返回]:' . $res['message']);

        return $res;
    }

    public function getApplicationByNo($no)
    {
        $url = '/v3/applyment4sub/applyment/business_code/'.$no;
        $res = $this->request($url, 'GET');

        if(isset($res['code'])) throw new ValidateException('[微信接口返回]:' . $res['message']);

        return $res;
    }


    public function getBanks($type, $offset, $limit = 100)
    {
        $params = [
            'personal-banking',
            'corporate-banking'
        ];
        $url = self::CAPITALLHH_BANKS.$params[$type].'?offset='.$offset.'&limit='.$limit;
        $res = $this->request($url, 'GET', [], true);
        if(isset($res['code'])) throw new ValidateException('[微信接口返回]:' . $res['message']);
        return $res;
    }

    public function branches($bank_alias_code,$city_code, $offset= 0, $limit = 100)
    {

        $url = '/v3/capital/capitallhh/banks/'.$bank_alias_code .'/branches?city_code='.$city_code .'&offset='
            .$offset.'&limit='.$limit;
        $res = $this->request($url, 'GET', [], true);
        if(isset($res['code'])) throw new ValidateException('[微信接口返回]:' . $res['message']);
        return $res;
    }

    public function areas($province_code = '')
    {
        if ($province_code) {
            $url = self::AREAS_PROVINCES .'/'. $province_code. '/cities';
        } else {
            $url = self::AREAS_PROVINCES;
        }
        $res = $this->request($url, 'GET', [], true);
        if(isset($res['code'])) throw new ValidateException('[微信接口返回]:' . $res['message']);
        return $res;
    }

}
