<?php
/**
 * @author James Rodenkirch <james@activebinary.com>
 * @copyright Copyright (c) 2010 Active Binary
 * @license MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * AB_Grid_Mapper
 *
 * Mapping class for AB_Grid to assign headers and flter options for each data
 * field in the grid.
 *
 * @property AB_Grid_Mapper $headers
 * @property AB_Grid_Filter $patterns
 * @property Doctrine_Query $maps
 *
 * @method AB_Grid_Mapper setHeaders(array $headers)
 * @method AB_Grid_Mapper setPatterns(array $patterns)
 * @method AB_Grid_Mapper setMaps(array $maps)
 * @method array getHeaders()
 * @method array getPatterns()
 * @method array getMaps()
 *
 * @package Doctrine
 * @subpackage DataGrid
 * @author James Rodenkirch <james@activebinary.com>
 */
class AB_Grid_Mapper extends AB_Class
{
    /**
     * @var array $_headers
     */
    protected $_headers = array();

    /**
     * @var array $_patterns
     */
    protected $_patterns = array();

    /**
     * @var array $_maps
     */
    protected $_maps = array();

    /**
     * @var array $_cache internal storage for method calls to reduce extra calls
     */
    protected $_cache = array();

    /* ---------------------------------------------------------------------- */

    /**
     * factory method for creating a new object for chaining
     *
     * @return AB_Grid_Mapper
     */
    public static function create()
    {
        return new AB_Grid_Mapper();
    }

    /**
     * method to add mapping information to a data field
     * 
     * @param string $header
     * @param string $field
     * @param string $filter
     * @return AB_Grid_Mapper
     */
    public function addMapping($header, $field, $filter)
    {
        $this->_maps[$field]     = $field;
        $this->_headers[$field]  = $header;
        $this->_patterns[$field] = $filter;

        return $this;
    }

    /**
     * method to add mapping information to a method call
     *
     * @param string $header
     * @param array $method
     * @param array $params
     * @return AB_Grid_Mapper
     */
    public function addMappingMethod($header, $method, $params)
    {
        $hash = md5(var_export(array($method, $params), true));

        $this->_maps[$hash]     = array($method, $params);
        $this->_headers[$hash]  = $header;
        $this->_patterns[$hash] = false;

        return $this;
    }

    /**
     * method to format the mysql operator and values for adding filter data to
     * a dql query object
     * 
     * @param string $field
     * @param string $value
     * @return array($operator, $value)
     */
    public function getSearchPattern($field, $value)
    {
        $pattern = $this->_patterns[$field];

        switch ($pattern) {
            case 'equal' :
            case 'select' :
                $operator = "=";
                break;
            case 'contains':
            case 'like' :
                $operator = 'like';
                $value = "%{$value}%";
                break;
            case 'startsWith':
            case 'rlike' :
                $operator = 'like';
                $value = "{$value}%";
                break;
            case 'endsWith':
            case 'llike' :
                $operator = 'like';
                $value = "%{$value}";
                break;
            case '=' :
            case '>=' :
            case '>' :
            case '<>' :
            case '!=' :
            case '<=' :
            case '<' :
                $operator = $pattern;
                break;

            default :
                $operator = false;
                break;
        }

        return array($operator, $value);
    }

    /**
     * 
     *
     * @param array $record
     * @param string $field
     * @return mixed
     */
    public function getValue($record, $field)
    {

        // if this is just a field, return the record value
        if (!is_array($this->_maps[$field])){
            $field = substr_replace($field, '_', stripos($field, '.'), 1);

            return $record[$field];

        // if this is a method call
        } else {
            list($method, $params) = $this->_maps[$field];

            // substitute params with record values as needed
            $keys = array_keys($record);
            foreach ($params as $k=>$v) {
                if(!is_array($v)) {
                    $field = substr_replace($v, '_', stripos($v, '.'), 1);
                }

                if (in_array($field, $keys)){
                    $params[$k] = $record[$field];
                }
            }

            // make sure the value is cached
            $hash = md5(var_export(array($method, $params), true));
            if (!isset($this->_cache[$hash])) {
                $this->_cache[$hash] = call_user_func_array($method, $params);
            }

            return $this->_cache[$hash];
        }
    }

}