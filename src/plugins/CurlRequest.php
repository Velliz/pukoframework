<?php
/**
 * Created by PhpStorm.
 * User: didit
 * Date: 12/7/2018
 * Time: 1:59 PM
 */

namespace pukoframework\plugins;

/**
 * Class CurlRequest
 * @package pukoframework\plugins
 *
 * @copyright DV 2018
 * @author Didit Velliz diditvelliz@gmail.com
 *
 * Example:
 * $data = CurlRequest::To('example.com')->Method('POST')->Receive(array(), CurlRequest::JSON);
 */
class CurlRequest
{

    /**
     * @var string
     */
    protected $service;

    /**
     * @var string
     */
    protected $method;

    const DEF = 'default';
    const JSON = 'json';
    const UNDEFINED = '';

    /**
     * CurlRequest constructor.
     * @param $service
     */
    protected function __construct($service)
    {
        $this->service = $service;
    }

    /**
     * @param $service
     * @return CurlRequest
     */
    public static function To($service)
    {
        return new CurlRequest($service);
    }

    /**
     * @param $method
     * @return $this
     */
    public function Method($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param array $param
     * @param string $type
     * @param array $header
     * @return mixed
     */
    public function Receive($param = array(), $type = CurlRequest::DEF, $header = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $this->service);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Puko Framework CURL');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($type === CurlRequest::JSON) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($param));
        }
        if ($type === CurlRequest::DEF) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
}