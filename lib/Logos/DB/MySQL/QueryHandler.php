<?php

namespace Logos\DB\MySQL;

/**
 * Class QueryHandler
 * Adds Additional Functionality to sorting, grouping, and limiting of mysql queries.
 */

class QueryHandler{

    private $_groupBy = "";
    private $_orderBy = "";
    private $_limit = "";

    //Any time a query is executed, we want to make sure to clear the query so that it doesn't show up again.
    public function getQuery($prepare){

        $query = " {$this->_groupBy} {$this->_orderBy} {$this->_limit}";

        $this->_groupBy = "";
        $this->_orderBy = "";
        $this->_limit = "";

        return $prepare.$query;

    }

    public function groupBy($grouping){

        $this->_groupBy = "GROUP BY $grouping";

        return $this;

    }

    public function orderBy($order){

        $this->_orderBy = "ORDER BY $order";

        return $this;

    }

    public function limit($limit){

        $min = null;
        $max = null;

        if(is_array($limit)){

            //if array has named keys
            if(array_key_exists('min', $limit))
                $min = $limit['min'];
            if(array_key_exists('max', $limit))
                $max = $limit['max'];

            //if array uses integers instead
            if(array_key_exists(0, $limit))
                $min = $limit[0];
            if(array_key_exists(1, $limit))
                $max = $limit[1];

        }else{
            $min = intval($limit);
        }

        if($min === null)
            return $this;

        $this->_limit = "LIMIT $min, ";

        if($max !== null)
            $this->_limit .= "$max, ";

        $this->_limit = rtrim($this->_limit, ", ");

        return $this;

    }

}