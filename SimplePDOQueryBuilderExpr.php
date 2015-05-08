<?php
/**
 * Created by PhpStorm.
 * User: vasiliy
 * Date: 4/29/15
 * Time: 11:28 AM
 */

namespace Megawilddaddy\SimplePDOQueryBuilder;

/**
 * Class SimplePDOQueryBuilderExpr
 * @package Megawilddaddy\SimplePdoQueryBuilder
 */
class SimplePDOQueryBuilderExpr
{
    /**
     * @param $field
     * @param $list
     * @param bool $quote
     * @return string
     */
    public function in($field, $list, $quote = true)
    {
        if (!is_array($list)) {
            $list = explode(',', $list);
        }
        if ($quote) {
            $list = array_map(function($el) {return " '$el' ";}, $list);
        }
        return "$field IN (" . implode(',', $list) . ")";
    }
} 