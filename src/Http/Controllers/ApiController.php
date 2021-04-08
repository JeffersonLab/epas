<?php
namespace Jlab\Epas\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\MessageBag;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Request
     */
    protected $request;


    public function __construct( Request $request)
    {
        $this->request = $request;
    }

    public function resourceResponse(JsonResource $resource){
        $response = $resource->toResponse($this->request);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }

    /**
     * Returns a json success response.
     *
     * @param $data
     * @return mixed
     */
    public function response($data){
        $struct['status'] = 'ok';
        $struct['data'] = $data;

        $options = 0;
        if ($this->request->get('pretty', 1) !== 0){
            $options = JSON_PRETTY_PRINT;
        }

        $response = response()->json($struct, 200, [], $options);

        if ($this->request->has('jsonp')){
            $response->setCallback($this->request->get('jsonp'));
        }

        return $response;
    }


    /**
     * Returns a json error response.
     *
     * @param string $msg
     * @param int $code
     * @param MessageBag $errors
     * @return mixed
     */
    public function error($msg, $code=404, MessageBag $errors = null){
        $struct['status'] = 'fail';
        $struct['message'] = $msg;
        if ($errors) {
            $struct['errors'] = $errors->toArray();
        }

        $options = 0;
        if ($this->request->get('pretty')){
            $options = JSON_PRETTY_PRINT;
        }

        $response = response()->json($struct, $code, [], $options);

        if ($this->request->has('jsonp')){
            $response->setCallback($this->request->get('jsonp'));
        }
        return $response;
    }

}
