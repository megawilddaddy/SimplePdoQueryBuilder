<?php
/**
 * Created by PhpStorm.
 * User: vasiliy
 * Date: 4/29/15
 * Time: 11:28 AM
 */

namespace Megawilddaddy\SimplePdoQueryBuilder;

/**
 * Class SimplePDOQueryBuilder
 * @package Megawilddaddy\SimplePdoQueryBuilder
 */
class SimplePDOQueryBuilder
{
    /**
     * @var
     */
    protected $fields;

    /**
     * @var
     */
    protected $from;

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var
     */
    protected $alias;

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var
     */
    protected $sortBy;

    /**
     * @var
     */
    protected $sortOrder;

    /**
     * @return SimplePDOQueryBuilder
     */
    public static function create()
    {
        return new self;
    }

    /**
     * @param $fields
     * @return $this
     */
    public function select($fields)
    {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return SimplePDOQueryBuilderExpr
     */
    public function expr()
    {
        return new SimplePDOQueryBuilderExpr();
    }

    /**
     * @param $alias
     * @return SimplePDOQueryBuilder
     */
    public function subQuery($alias)
    {
        $sq = new SimplePDOQueryBuilder();
        $sq->alias = $alias;
        return $sq;
    }


    /**
     * @param $from
     * @return $this
     */
    public function from($from)
    {
        if ($from instanceof SimplePDOQueryBuilder) {
            $this->from = "(" . $from->getSql() . ")";
            if ($from->getAlias()) {
                $this->from .= " as " . $from->getAlias();
            }
        } else {
            $this->from = $from;
        }
        return $this;
    }

    /**
     * @param $leftJoin
     * @param string $condition
     * @return $this
     */
    public function leftJoin($leftJoin, $condition = '')
    {
        if ($leftJoin instanceof SimplePDOQueryBuilder) {
            $this->joins[] = " LEFT JOIN ( {$leftJoin->getSql()} ) as {$leftJoin->getAlias()} ON $condition";
        } else {
            $this->joins[] = ' LEFT JOIN ' . $leftJoin . ($condition ? ' ON ' . $condition : '');
        }
        return $this;
    }

    /**
     * @param $join
     * @param string $condition
     * @return $this
     */
    public function join($join, $condition = '')
    {
        if ($join instanceof SimplePDOQueryBuilder) {
            $this->joins[] = " JOIN ( {$join->getSql()} ) as {$join->getAlias()} ON $condition";
        } else {
            $this->joins[] = ' JOIN ' . $join . ($condition ? ' ON ' . $condition : '');
        }
        return $this;
    }

    /**
     * @param $condition
     * @return $this
     */
    public function where($condition)
    {
        $this->where[] = $condition;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param $having
     * @return $this
     */
    public function having($having)
    {
        $this->having = $having;
        return $this;
    }

    /**
     * @param $groupBy
     * @return $this
     */
    public function group($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @param $sortBy
     * @param string $sortOrder
     */
    public function orderBy($sortBy, $sortOrder = 'DESC')
    {
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        $query = "\n SELECT " . implode(',', $this->fields) ."\n FROM " . $this->from;

        if (!empty($this->joins)) {
            $query .= implode(' ', $this->joins);
        }
        if (!empty($this->where)) {
            $query .= "\n WHERE " . implode(" AND ", $this->where);
        }
        if (!empty($this->groupBy)) {
            $query .= "\n GROUP BY $this->groupBy ";
        }
        if (!empty($this->having)) {
            $query .= "\n HAVING $this->having ";
        }
        if (!empty($this->sortBy)) {
            $query .= "\n ORDER BY $this->sortBy $this->sortOrder ";
        }
        $query .= "\n";

        return $query;
    }
} 