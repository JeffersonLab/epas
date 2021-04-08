<?php


namespace Jlab\Epas\Service;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Abstract Class ModelSearch
 *
 * Model query and filter service inspired by:
 *
 * @see https://m.dotdev.co/writing-advanced-eloquent-search-query-filters-de8b6c2598db
 *
 */
abstract class ModelSearch
{
    /**
     * Max models to return
     *
     *  Note that attempting to get more that 1,000 may cause an ORA-1795 error
     *  "maximum number of expressions in a list is 1000".
     *  This arises because of how Laravel elqoquent builds its eager-loading sql
     *  using great big "where in (?,?,?,...1000+)" clause.
     *
     * @todo implement a warning mechanism that can tell the user that results have been limited
     */
    public $limit = 1000;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;


    /**
     * Generic ModelSearch constructor.
     *
     */
    public function __construct()
    {
        $this->query = $this->getModelInstance()->newQuery();
    }

    /**
     * Return an instance of the model class that is being made searchable.
     *
     * @return Model
     */
    abstract protected function getModelInstance();

    /**
     * Apply all filters that match parameters in the Request object.
     *
     * The method looks for StudlyCase filters that correspond to snake_case
     * parameters.  (e.g  DomainId filter class matching the domain_id request parameter)
     *
     * This is well suited for simple filters based on a single request parameter.
     *
     * It is chainable for fluent specification of additional filters.
     *
     * @param Request $request
     * @return $this
     */
    public function applyRequest(Request $request)
    {
        foreach ($request->all() as $filterName => $value) {
            $this->applyNamedFilter($filterName, $value);

        }
        return $this;
    }

    /**
     * Apply the specified filter using the provided value.
     *
     * This is well suited for complex filters, such as those that require
     * multiple input parameters where $value can be passed an array.
     *
     * @param string $filterName
     * @param mixed $value
     * @return $this
     */
    public function applyFilter($filterName, $value)
    {
        $this->applyNamedFilter($filterName, $value);
        return $this;
    }

    /**
     * Looks for a Filter class based on provided filterName and if
     * available applies it to the query.
     *
     * @param $filterName
     * @param $value
     */
    private function applyNamedFilter($filterName, $value){
        $decorator = $this->createFilterDecorator($filterName);

        if ($this->isValidDecorator($decorator)) {
            $this->query = $decorator::apply($this->query, $value);
        }
    }

    /**
     * Returns the fully qualified class of $name in the Filters Namespace.
     *
     * @param string $name
     * @return string
     */
    private function createFilterDecorator($name)
    {
        return __NAMESPACE__ . '\\Filters\\' .Str::studly($name);
    }

    /**
     * Answers whether the decorator name is a valid class name.
     *
     * @param string $decorator
     * @return bool
     */
    private function isValidDecorator($decorator)
    {
        return class_exists($decorator);
    }

    /**
     * Returns query results.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getResults()
    {
        return $this->query->take($this->limit)->get();
    }

}
