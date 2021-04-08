<?php


namespace Jlab\Epas\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

abstract class BaseResource extends JsonResource
{

    /**
     * An array of keys (kebab-case) that should be omitted from the resource.
     *
     * For example to exclude softwareTestplan, add software-testplan to the array.
     *
     * @var array
     */
    protected $requestWithout = [];


    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
        self::withoutWrapping();
    }


    protected function camelArray($attributeArray){
        $camelArray = [];
        foreach($attributeArray as $key => $value){
            $camelArray[Str::camel($key)] = $value;
        }
        return $camelArray;
    }


    /**
     * Returns string representation of a date.
     *
     * Null/Missing dates are returned as an empty string.
     *
     * @param Carbon|null $date
     * @return string
     */
    protected function dateAsString(Carbon $date = null){
        return $date ? $date->format('Y-m-d') : '';
    }

    protected function dateAndTimeAsString(Carbon $date = null){
        return $date ? $date->format('Y-m-d H:i') : '';
    }

    /**
     * The authorized abilities available to all users (aka the public).
     *
     * @return bool[]
     */
    protected function publicCan(){
        return [
            'view'  => true,
            'create' => false,
            'update' => false,
            'delete' => false,
        ];
    }

    /**
     * The authorized abilities of the current user.
     *
     * @param mixed $user
     * @return array|bool[]
     */
    protected function can($user = null){

        if (! $user){
            return $this->publicCan();
        }
        return array_merge($this->publicCan(), [
            'create' => $user->can('create', get_class($this->resource)),
            'update' => $user->can('update', $this->resource),
            'delete' => $user->can('delete', $this->resource),
        ]);
    }


    /**
     * Requests that given keys be omitted from resource.
     * The request can be overridden if the HTTP request
     * issues a corresponding "with-".
     *
     * @param mixed $keys string or array of strings
     */
    public function requestWithout($keys){
        foreach (Arr::wrap($keys) as $key){
            $this->requestWithout[] = $key;
        }
    }

    /**
     * Removes the keys from the list of keys to be excluded.
     * The keys may still be excluded from generation however if
     * the HTTP request object includes a corresponding "without-"
     * directive.
     *
     * @param mixed $keys
     */
    public function requestWith($keys){
        foreach (Arr::wrap($keys) as $key) {
            $this->requestWithout[] = array_filter($this->requestWithout, function ($item) use ($key) {
                return $item !== $key;
            });
        }
    }

    /**
     * Determine if a given key is wanted in the resource output.
     *
     * The logic is that if neither the instance nor the HTTP Request has
     * requested the key to be excluded, then it is wanted.
     *
     * @param string $key
     * @param Request $request
     * @return bool
     */
    public function wants(string $key, Request $request){
        return $this->instanceWants($key) && $this->requestWants($key, $request);
    }

    /**
     * Returns false
     * @param $key
     * @return bool
     */
    protected function instanceWants($key){
        return ! in_array($key, $this->requestWithout);
    }

    /**
     * Converts the presence of a parameter named either with-{$key}
     * or without-{$key} in the request into a boolean value.
     *
     * If both parameters are present, then the with-{$key} will take
     * precedence.  If neither parameter is present, then a default value
     * of true is returned.
     *
     * @param string $key
     */
    protected function requestWants($key, $request){
        if ($request->has('with-'.$key)){
            return true;
        }
        if ($request->has('without-'.$key)){
            return false;
        }
        return true;
    }
}
