<?php

/**
 * Class QueryHandler
 * Adds Additional Functionality to sorting, grouping, and limiting of mysql queries.
 */

class QueryHandler{

    private $_groupby = "";
    private $_orderby = "";
    private $_limit = "";

    //Any time a query is executed, we want to make sure to clear the query so that it doesn't show up again.
    public function getQuery(){

        $query = " {$this->_groupby} {$this->_orderby} {$this->_limit} ";

        $this->_groupby = "";
        $this->_orderby = "";
        $this->_limit = "";

        return $query;

    }

    public function groupby($grouping){

        $this->_groupby = "GROUP BY $grouping";

        return $this;

    }

    public function orderby($order){

        $this->_orderby = "ORDER BY $order";

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