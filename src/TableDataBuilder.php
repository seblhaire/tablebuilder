<?php

namespace Seblhaire\TableBuilder;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class that analyses parameters sent by Javascript table to the controller, and prepares data that must be returned
 */
class TableDataBuilder
{

    private $nbLinesPerPage = null;

    private $sortBy = null;

    private $searchTerm = null;

    private $start = null;

    private $aLines = null;

    private $iTotal = null;

    private $sFooter = null;

    private $query = null;

    private $withtrashed = false;

    private $searchfunction = null;

    private $methodsToDisplay = array();

    private $fieldsList = null;

    /**
     * constructor
     *
     * @param Request $request
     *            request sent to controller by table
     * @param array $othervalidationrules
     *            validation rules for additional custom parameters sent by table
     */
    public function __construct(Request $request, $othervalidationrules = [])
    {
        $rules = array_merge([
            'itemsperpage' => 'required|numeric',
            'sortBy' => 'present',
            'start' => 'required|numeric',
            'searchTerm' => 'present'
        ], $othervalidationrules);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $erreurs = '';
            foreach ($validator->errors()->all() as $message) {
                $erreurs .= $message;
            }
            throw new \Exception($erreurs);
        } else {
            $this->nbLinesPerPage = (int) $request->input('itemsperpage');
            $this->sortBy = $request->input('sortBy');
            $this->searchTerm = $request->input('searchTerm');
            $this->start = (int) $request->input('start');
            $this->iTotal = 0;
            $this->sFooter = '';
        }
    }

    /**
     * enables / disables eloquent withTrashed method
     *
     * @param boolean $bval
     *            on/office
     */
    public function setWithTrashed($bval = true)
    {
        $this->withtrashed = $bval;
    }

    /**
     * Gets line number per page
     *
     * @return int nb per page
     */
    public function nblines()
    {
        return (int) $this->nbLinesPerPage;
    }

    /**
     * gets start number
     *
     * @return int
     */
    public function start()
    {
        return (int) $this->start;
    }

    /**
     * Gets reverse order value
     *
     * @return boolean
     */
    public function reverseOrder()
    {
        return $this->reverseOrder == 'true';
    }

    /**
     * gets sort by parameter value
     *
     * @return string
     */
    public function sortBy()
    {
        return $this->sortBy;
    }

    /**
     * gets search term parameter
     *
     * @return string
     */
    public function searchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * gets line number
     *
     * @return integer
     */
    public function total()
    {
        return $this->iTotal;
    }

    /**
     * gets footer value
     *
     * @return string
     */
    public function footer()
    {
        return $this->sFooter;
    }

    /**
     * checks if has footer value
     *
     * @return boolean
     */
    public function hasFooter()
    {
        return strlen($this->sFooter) > 0;
    }

    /**
     * sets database to query
     *
     * @param
     *            \Illuminate\Database\Eloquent\Model| or \Illuminate\Database\Eloquent\Builder $obj
     * @throws \Exception
     */
    public function setQuery($obj)
    {
        if ($obj instanceof \Illuminate\Database\Eloquent\Model) {
            $query = $obj->whereRaw('1');
        } elseif ($obj instanceof \Illuminate\Database\Eloquent\Builder) {
            $query = $obj;
        } else {
            throw new Exception('wrong obj');
        }
        $this->query = $query;
    }

    /**
     * sets search function
     *
     * @param function $fn
     *            search function
     */
    public function setSearchFunction($fn)
    {
        $this->searchfunction = \Closure::bind($fn, $this, get_class($this));
    }

    /**
     * add data line if static data
     *
     * @param array $aLine
     *            table data
     */
    public function addLine($aLine)
    {
        if (is_array($aLine)) {
            $this->aLines[] = $aLine;
        } else {
            throw new Exception('not an array');
        }
    }

    /**
     * sets total value
     *
     * @param integer $iTotal
     */
    public function setTotal($iTotal)
    {
        $this->iTotal = (int) $iTotal;
    }

    /**
     * sets a footer to be added to every table page
     *
     * @param string $sFooter
     */
    public function setFooter($sFooter)
    {
        if (is_string($sFooter)) {
            $this->sFooter = $sFooter;
        } else {
            throw new Exception('not a string');
        }
    }

    /**
     * sets a list of fields to retrieve from database, comma separated
     *
     * @param string $sFieldLists
     *            comma separated list of fields
     */
    public function setFields($sFieldLists)
    {
        $this->fieldsList = $sFieldLists;
    }

    /**
     * adds an additional field to fields retrieved by database
     *
     * @param string $fieldname
     *            field name
     * @param function $func
     *            function to build line. function($line) where line is database row object
     */
    public function addMethodToDisplay($fieldname, $func)
    {
        $this->methodsToDisplay[$fieldname] = $func;
    }

    /**
     * runs database query: search, order, pagination
     */
    private function buildData()
    {
        $connection = $this->query->getConnection()->getName();
        $driver = $this->query->getConnection()->getConfig('driver');
        if ($this->withtrashed == true) {
            $this->query->withTrashed();
        }
        if (! is_null($this->searchfunction)) {
            $this->query->where($this->searchfunction);
        }
        if ($driver == 'mysql') {
            if (! is_null($this->fieldsList)) {
                $this->query->select(DB::raw('SQL_CALC_FOUND_ROWS ' . $this->fieldsList));
            } else {
                $this->query->select(DB::raw('SQL_CALC_FOUND_ROWS *'));
            }
        } else {
            $total = $this->query->getQuery()->count();
            if (! is_null($this->fieldsList)) {
                $this->query->select(DB::raw($this->fieldsList));
            }
        }
        if (! is_null($this->sortBy) && $this->sortBy != '') {
            $aSorts = explode(';', $this->sortBy);
            foreach ($aSorts as $sSort) {
                $aField = explode(':', $sSort);
                $this->query->orderBy($aField[0], $aField[1]);
            }
        }
        if ($this->nbLinesPerPage > 0) {
            $aRes = $this->query->skip($this->start)
                ->take($this->nbLinesPerPage)
                ->get();
        } else {
            $aRes = $this->query->get();
        }
        if ($driver == 'mysql') {
            $expr = DB::raw('SELECT FOUND_ROWS()');
            $this->iTotal = DB::connection($connection)->select( $expr->getValue(DB::connection($connection)->getQueryGrammar()))[0]->{'FOUND_ROWS()'};
        } else {
            $this->iTotal = $total;
        }
        $this->aLines = array();
        if (count($aRes) > 0) {
            foreach ($aRes as $obj) {
                $line = $obj->toarray();
                if (count($this->methodsToDisplay) > 0) {
                    foreach ($this->methodsToDisplay as $fieldname => $func) {
                        $line[$fieldname] = $func($obj);
                    }
                }
                $this->aLines[] = $line;
            }
        }
    }

    /**
     * prepare static data: search, order, pagination
     *
     * @return [type] [description]
     */
    private function buildStaticData()
    {
        $data = collect($this->aLines);
        if (! is_null($this->searchfunction) && ! is_null($this->searchTerm) && strlen($this->searchTerm) > 0) {
            $data = $data->filter($this->searchfunction);
        }
        $this->iTotal = $data->count();
        if (! is_null($this->sortBy) && $this->sortBy != '') {
            $aSorts = explode(';', $this->sortBy);
            foreach ($aSorts as $sSort) {
                $aField = explode(':', $sSort);
                if ($aField[1] == 'asc') {
                    $data = $data->sortBy($aField[0]);
                } else {
                    $data = $data->sortByDesc($aField[0]);
                }
            }
        }
        if ($this->nbLinesPerPage > 0) {
            $data = $data->skip($this->start)->take($this->nbLinesPerPage);
        }
        $this->aLines = array_values($data->toArray());
    }

    /**
     * Outputs json data to be returned by controller method
     *
     * @return json data
     */
    public function output()
    {
        if (is_null($this->aLines)) {
            $this->buildData();
        } else {
            $this->buildStaticData();
        }
        $aRes = array();
        $aRes['aLines'] = $this->aLines;
        $aRes['iTotalLines'] = $this->total();
        if ($this->hasFooter()) {
            $aRes['sFooter'] = $this->footer();
        }
        // var_dump($aRes);
        return response()->json($aRes);
    }
}
