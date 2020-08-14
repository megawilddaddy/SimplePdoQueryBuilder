<?php
/**
 * Created by PhpStorm.
 * User: vasiliy
 * Date: 4/29/15
 * Time: 11:28 AM
 */

namespace Megawilddaddy\SimplePDOQueryBuilder;

/**
 * Class SimplePDOQueryBuilder
 * @package Megawilddaddy\SimplePDOQueryBuilder
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
     * @var int
     */
    protected $joinOrd = 0;
    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var array
     */
    protected $leftJoins = [];

    /**
     * @var
     */
    protected $sortBy;

    /**
     * @var
     */
    protected $sortOrder;

    /**
     * @var array
     */
    protected $extra = [];

    /**
     * @var array
     */
    protected $having = [];
    /**
     * @var
     */
    protected $offset;
    /**
     * @var
     */
    protected $limit;

    /**
     * @var array
     */
    protected $union = [];

    /**
     * @var
     */
    protected $groupBy;

    /**
     * @var array
     */
    private $parameters = array();

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
        $sq->setAlias($alias);
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
     * @return string
     */
    public function getSql()
    {
        $query = "\n SELECT " . implode(',', $this->fields);

        if (!empty($this->from)) {
            $query .= "\n FROM " . $this->from;
        }
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
            $query .= "\n HAVING " . implode(" AND ", $this->having);
        }
        if (!empty($this->sortBy)) {
            $query .= "\n ORDER BY $this->sortBy $this->sortOrder ";
        }
        if ($this->limit) {
            $query .= "\n LIMIT $this->limit";
            if ($this->offset) {
                $query .= " OFFSET " . $this->offset;
            }
        }

        if (!empty($this->union)) {
            $query .= implode(' ', $this->union);
        }

        $query .= "\n";

        return $query;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
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
            $join = "\n LEFT JOIN ( {$leftJoin->getSql()} ) as {$leftJoin->getAlias()} ON $condition";
        } else {
            $join = "\n LEFT JOIN " . $leftJoin . ($condition ? ' ON ' . $condition : '');
        }

        $this->joinOrd++;
        $this->joins[$this->joinOrd] = $join;
        $this->leftJoins[] = $this->joinOrd;

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
            $join = "\n JOIN ( {$join->getSql()} ) as {$join->getAlias()} ON $condition";
        } else {
            $join = "\n JOIN " . $join . ($condition ? ' ON ' . $condition : '');
        }

        $this->joinOrd++;
        $this->joins[$this->joinOrd] = $join;

        return $this;
    }


    /**
     * @param $join
     * @return $this
     * @internal param string $condition
     */
    public function union($join)
    {
        if ($join instanceof SimplePDOQueryBuilder) {
            $this->union[] = " UNION {$join->getSql()}";
        } else {
            $this->union[] = ' UNION ' . $join;
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
     * @param $having
     * @return $this
     */
    public function having($having)
    {
        $this->having [] = $having;
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
     * @return $this
     */
    public function orderBy($sortBy, $sortOrder = 'DESC')
    {
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     *
     */
    public function resetHaving()
    {
        $this->having = [];
    }

    /**
     *
     */
    public function resetLeftJoins()
    {
        if (!empty($this->leftJoins)) {
            foreach ($this->leftJoins as $ord) {
                unset($this->joins[$ord]);
            }
        }
    }

    /**
     * @param $limit
     * @param $offset
     */
    public function limit($limit, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     *
     */
    public function resetLimit()
    {
        $this->limit = null;
    }

    /**
     *
     */
    public function resetSorting()
    {
        $this->sortBy = null;
    }

    /**
     *
     */
    public function resetGroupBy()
    {
        $this->groupBy = null;
    }

    public function addParameter($k, $v)
    {
        $this->parameters[$k] = $v;
    }

    public function dump()
    {
        $sql = $this->getSql();
        foreach ($this->getParameters() as $k => $v) {
            $sql = str_replace(":$k", "'$v'", $sql);
        }
        die("<pre>" . $sql . "</pre>");
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return array
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @return array
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return array
     */
    public function getUnion()
    {
        return $this->union;
    }

    /**
     * @return mixed
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }
}
