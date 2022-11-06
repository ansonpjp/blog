<?php

namespace App\Libraries\APLYiD;

use App\Facility;
use GuzzleHttp\Client;
use App\Exceptions\WebServiceResponseException;
use Illuminate\Support\Facades\Log;
// use App\Libraries\StormanWebService\OptionWebService;
// use App\Schedule;
// use Illuminate\Support\Arr;


class Aplyid
{
    protected $facility;
    protected $accessControl;
    protected $endpoint = 'https://integration.aplyid.com/api/v2/'; //APLYiD endpoint

    protected $secret = 'WtBAQ55E4DUmmu1J2NFh2ma6gzDZNZhj';         //API Secret shared by APLYiD


    /**
     * Construct the API Call
     *
     * @param  string  $facility_code
     * @return  void
     */
    public function __construct($facility_code)
    {
        $this->facility = Facility::fetch($facility_code);
        $this->accessControl = $this->facility->theAccessControl;

        $this->client = new Client();
    }
    
    public function callAplyid($agreement_no, $customer_code)
    {
        $body = [
            'reference'             =>  $agreement_no,
            'external_id'           =>  $this->facility->facility_code,
            'contact_phone'         => ''
        ];

        return $this->send("POST", "send_text", $body);
    }
    
    /**
     * Send the request to the APLYiD endpoint and handle the response
     *
     * @param $method
     * @param $uri
     * @param  array  $body
     * @return \Illuminate\Support\Collection|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function send($method, $uri, $body = [])
    {
        try {
            $response = $this->client->request($method, $this->endpoint.$uri, [
                'verify'    => false, //todo remove after dev done
                'timeout'   => 60,
                'headers'   =>  [
                    'Aply-API-Key' => 'DBzb2eiX6Yt1Fi9kYy96mE3w',
                    'Aply-Secret' => '1111111111111'   // chane in originall
                ],
                'json'      => $body
            ]);
            // handle the error here
        } catch (\Exception $e) {
            Log::channel('noke')->error('Aplyid Error'.$e->getCode().' Error message: '. $e->getMessage());
            throw new WebServiceResponseException('Aplyid Error'.$e->getCode().' Error message: '. $e->getMessage());
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        switch ($method) {
            case "GET":
                return collect($responseData);
            case "POST":
                return collect($responseData);
            case "PUT":
            case "DELETE":
                return Arr::get($responseData, 'id');
            default:
                return $responseData;
        }
    }

}