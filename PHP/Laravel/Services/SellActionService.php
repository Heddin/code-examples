<?php

namespace App\Services;


use ErrorException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Exception\NotFoundException;
use Ixudra\Curl\Facades\Curl;
use stdClass;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;





/**
 * Class SellActionService
 * @package App\Services
 *
 * Service for interaction with SellAction ( https://sellaction.net )
 *
 * @documentation for API located @ https://sellaction.net/sellaction/doc/advertiser_api.php
 */
class SellActionService {

   const API_URL = 'https://sellaction.net/api';
   const METHOD_GET = 0;
   const METHOD_POST = 1;
   const METHOD_PUT = 2;
   const ACTION_STATUS_WAIT = 'status';
   const ACTION_STATUS_CONFIRM = 'confirm';
   const ACTION_STATUS_CANCEL = 'cancel';
   const ACTION_STATUS_DELIVERY = 'delivery';
   const LOST_ORDER_STATUS_CONFIRM = 1;
   const LOST_ORDER_STATUS_DECLINE = 2;
   const LOST_ORDER_ACTION_STATUS_WAITING = 0;
   const LOST_ORDER_ACTION_STATUS_CONFIRM = 3;
   const LOST_ORDER_ACTION_STATUS_DECLINE = 4;

   public static $tariffs = [
       980 => [2447 => 21.00], //2447: Оплаченный курс Россия	RU	21 %
       643 => [2446 => 21.00]  //2446: Оплаченный курс Украина	UA	21 %
   ];


   private $key;

    /**
     * SellAction constructor.
     *
     * @throws ErrorException, when API key is null;
     */

    public function __construct(){

       $this->key = trim(config('app.sellaction_api_key'));

        if(!isset($this->key)) {
            throw new ErrorException('API-key Not Specified');
        }
    }

    /**
     * getter for  SellAction API-key
     *
     * @return string
     */
    public function getKey() : string{
        return $this->key;
    }


    /**
     * get collection of "Actions" from provider
     *
     * @param array $filters - for filter variants, please refer to @documentation
     *
     * @return Collection
     *
     * @throws  Exception $exc, if $response is Error-object
     */
    public function getActions(array $filters = []) : Collection{

         $uri = $this::API_URL. '/actions';

         return $this->massRequest($this::METHOD_GET,$uri,$filters);
    }

    /**
     * get "Action" object from provider;
     *
     * @param int $id - ID of "action"
     *
     * @return mixed "Action" object
     *
     * @throws  Exception $exc, if $response is Error-object
     */
    public function getAction(int $id) : stdClass  {

        $uri = $this::API_URL."/action/".$id;

        return $this->singleRequest($this::METHOD_GET,$uri);
    }

    /**
     * get collection of "Campaigns" from provider
     *
     * @param int|null $per_page
     *
     * @return Collection
     *
     * @throws  Exception $exc, if $response is Error-object
     */
    public function getCampaigns(int $per_page = null) : Collection{

        $filters = [];

        if(isset($per_page)){
            $filters['per-page'] = $per_page;
        }

        $uri = $this::API_URL. '/campaigns/my';

        return $this->massRequest($this::METHOD_GET,$uri,$filters);
    }

    /**
     * get "Campaign" object from provider;
     *
     * @param int $id - ID of "Campaign"
     *
     * @return mixed "Campaign" object
     *
     * @throws  Exception $exc, if $response is Error-object
     */
    public function getCampaign(int $id){

        $uri = $this::API_URL. '/campaigns/' .$id;

        return $this->singleRequest($this::METHOD_GET,$uri);
    }

    /**
     * register "Action" to provider
     *
     * @param string $sauid - User ID in providers system;
     * @param array $tariffs - array of tariffs (exp. [TARIFF_ID => TARIFF_PARAM])
     * @param int $order_id - ID of Order
     * @param array $data - array of unnecessary params;
     *        accepts: 'created','ip','country','user_agent','referrer','client_id'
     *
     * @return mixed $response.  ($response->code == 201 means OK, $response->code == 422 -> error, with message)
     * @throws  Exception $exc, if $response is Error-object
     */
    public function registerAction(string $sauid, array $tariffs, int $order_id, array $data = []) {

        $uri = $this::API_URL. '/actions';

        $payload = compact('sauid','tariffs','order_id');
        if(!empty($data)){
           $payload = array_merge($payload,$data);
        }
        return $this->singleRequest($this::METHOD_POST,$uri,$payload);
    }

    /**
     * edits some params in already registered "Actions"
     *
     * @param int $id - ID of "Action"
     * @param int $campaign_id
     * @param string $status
     * @param array $data
     *
     *
     * @return mixed
     * @throws  Exception $exc, if $response is Error-object
     */
    public function alterAction(int $id, int $campaign_id, string $status, array $data = []){

        $uri = $this::API_URL. '/actions/' .$id;

        $payload = compact('campaign_id','status');
        if(!empty($data)){
            $payload = array_merge($payload,$data);
        }

        return $this->singleRequest($this::METHOD_PUT,$uri,$payload);
    }

    /**
     *
     * get collection of "Lost Orders" from provider
     *
     * @param array $filters - for filter variants, please refer to @documentation
     *
     * @return Collection
     *
     * @throws  Exception $exc, if $response is Error-object
     */
    public function getLostOrders(array $filters = []) : Collection{

        $uri  = $this::API_URL. '/lost-orders';

        return $this->massRequest($this::METHOD_GET,$uri,$filters);

    }

    /**
     * get "Lost Order" object from provider;
     *
     * @param int $id - ID of "Lost Order"
     *
     * @return mixed "Lost Order" object
     * @throws  Exception $exc, if $response is Error-object
     */
    public function getLostOrder(int $id)  {

        $uri = $this::API_URL. '/lost-orders/' .$id;

        return $this->singleRequest($this::METHOD_GET,$uri);
    }

    /**
     * confirms or declines "Lost Order" in providers system
     *
     * @param int $id - ID of "Lost Order"
     * @param array $params - array of required parameters
     *        for $params list, please refer @documentation
     *
     * @return mixed
     *
     * @throws  Exception $exc, if $response is Error-object
     */
    public function alterLostOrder(int $id, array $params) {

        $uri = $this::API_URL. '/actions/' .$id;
        $payload = $params;

        return $this->singleRequest($this::METHOD_PUT,$uri,$payload);
    }

    /**
     * Must process "single" request.
     * request, that works with single value
     *
     * @return stdClass $value
     *
     * @throws  Exception $exc, if $response is Error-object
     */
    private function singleRequest(int $method, string $uri, array $data = []) : stdClass{

        switch ($method){
            case $this::METHOD_GET :

                $filters = $data ??  [];

                $response = Curl::to($uri)
                    ->withHeader("Auth-Token: {$this->key}")
                    ->withData($filters)
                    ->returnResponseObject()
                    ->asJson()->get();

                break;
            case $this::METHOD_POST :

                $payload = $data;

                $response = Curl::to($uri)
                    ->withHeader("Auth-Token: {$this->key}")
                    ->returnResponseObject()
                    ->withData($payload)
                    ->post();

                break;
            case $this::METHOD_PUT :

                $payload = $data;

                $response = Curl::to($uri)
                    ->withHeader("Auth-Token: {$this->key}")
                    ->returnResponseObject()
                    ->withData($payload)
                    ->put();
                break;
            default : $response = null;
                break;

        }

        return $this->handleResponse($response);
    }

    /**
     * Must process "mass" request.
     * request, that works with multiple values
     *
     * @return Collection of $values
     * @throws Exception $exc, if $response is Error-object
     */
    private function massRequest(int $method,string $uri,array $data = null) : Collection {
        switch ($method){
            case $this::METHOD_GET :
                $filters = $data ??  [];

                $response = Curl::to($uri)
                    ->withHeader("Auth-Token: {$this->key}")
                    ->withData($filters)
                    ->returnResponseObject()
                    ->asJson()->get();

                break;

            case $this::METHOD_POST : //API has no such resources yet
            case $this::METHOD_PUT  : //API has no such resources yet

            default : $response = null;
                break;

        }
        return collect($this->handleResponse($response));
    }

    /**
     * Must process responses from provider.
     *
     * @param mixed $response - actual response from provider
     *
     * @return stdClass $value || Collection $values
     *
     * @throws Exception $exc, if $response is Error-object
     */
    private function handleResponse($response) {
        switch ($response->status){
            case 400 : throw new Exception('BadRequest:'.PHP_EOL. print_r($response->content,true),400);
                break;
            case 401 :
            case 403 :
            case 415 :
                throw new Exception(print_r($response->content,true),400);
                break;
            case 404 : throw new NotFoundException(print_r($response->content,true),404);
                break;
            case 405 : throw new MethodNotAllowedException(print_r($response->content,true),405);
                break;
            case 422 : throw new ValidationException(print_r($response->content,true),422);
                break;
            case 429 : throw new TooManyRequestsHttpException(print_r($response->content,true),429);
                break;
            case 500 : throw new ErrorException(print_r($response->content,true),500);
                break;
        }
        return (object) $response->content;
    }

}