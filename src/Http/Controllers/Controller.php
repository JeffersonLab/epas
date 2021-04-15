<?php

namespace Jlab\Epas\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Jlab\Epas\Http\Resources\UserResource;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * The current Request
     *
     * @var Request
     */
    protected $request;


    public function __construct( Request $request)
    {
        $this->request = $request;
        $this->shareCurrentUser();
    }

    /**
     * Add a without-{$key} parameter to the request.
     * @param $key
     */
    protected function requestWithout(string $key){
        $this->request->merge(['without-'.$key => true]);
    }

    /**
     * Shares the server's request data to the client
     */
    protected function shareRequest(){
        Inertia::share(['request' => $this->request->all()]);
    }


    /**
     * Return the API resource of the currently authenticated user.
     *
     * Relies on middleware to have filtered out non-authenticated requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareCurrentUser()
    {
        if (Auth::user()){
            $userResource = new UserResource(Auth::user());
        }else{
            $userResource = null;
        }
        Inertia::share(['currentUser' => $userResource]);
    }


}
