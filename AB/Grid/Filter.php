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
 * AB_Grid_Filter
 *
 * Filter class for AB_Grid to handle storing filter data sent from the
 * interface
 *
 * @property Doctrine_Query $filters
 *
 * @method AB_Grid_Filter setFilters(array $filters)
 * @method array getFilters()
 */
class AB_Grid_Filter extends AB_Class
{
    /**
     * @var array $_filters
     */
    protected $_filters = array();

    /* ---------------------------------------------------------------------- */

    /**
     * factory method for creating a new object for chaining
     *
     * @return AB_Grid_Filter
     */
    public static function create()
    {
        return new AB_Grid_Filter;
    }

    /**
     * class constructor
     *
     * the constructor sets default filter data
     */
    public function  __construct()
    {
        $this->clear();
    }

    /**
     * method to generate a json encoded version of the filter settings.
     *
     * this is ued to generate a version of the filter object that is stored in
     * the session so that the javascript end can read/set values
     *
     * @return string
     */
    public function getEncoded()
    {
        $json = json_encode($this->getFilters());
        $json = str_replace('[]', '{}', $json);
        return $json;
    }

    /**
     * method to load the json filter object from the session and convert it
     * into a php object
     *
     * @param string $json
     * @return AB_Grid_Filter
     */
    public static function loadEncoded($json)
    {
        $obj = new AB_Grid_Filter();
        $obj->setFilters(json_decode($json, true));

        return $obj;
    }

    /**
     * method to add a filter setting
     *
     * @param string $field
     * @param string $value
     */
    public function addFilter($field, $value)
    {
        $this->_filters['filters'][$field] = $value;
    }

    /**
     * method to generate a session hash key to help segregate grid settings
     * across multiple pages
     *
     * @return string
     */
    public static function generateKey()
    {
        return md5($_SERVER['PATH_INFO']);
    }

    /* ---------------------------------------------------------------------- */

    /**
     * method to set the sorting field and direction
     *
     * @param string $value
     */
    public function sort($value)
    {
        $filters = $this->getFilters();

        if ($filters['sort']['field'] == $value) {
            if ($filters['sort']['direction'] == 'asc') {
                $filters['sort']['direction'] = 'desc';

            } else {
                $filters['sort']['direction'] = 'asc';
            }

        } else {
            $filters['sort']['field'] = $value;
            $filters['sort']['direction'] = 'asc';
        }

        $filters['page'] = 1;

        $this->setFilters($filters);
    }

    /**
     * method to set field filters
     *
     * values come in as "field,value|field,value|field,value|"
     *
     * @param string $value
     */
    public function filter($value)
    {
        $filters = $this->getFilters();
        $filters['filters'] = array();
        
        $sections = explode('|', $value);
        foreach ($sections as $sections) {
            list($field, $value) = explode(',,', $sections);

            if (!empty($value)) {
                $filters['filters'][$field] = $value;
            }
        }

        $this->setFilters($filters);
    }

    /**
     * method to set the requested page number
     * 
     * @param int $value
     */
    public function page($value)
    {
        $filters = $this->getFilters();
        $filters['page'] = $value;
        $this->setFilters($filters);
    }

    /**
     * method to reset settigns back to the base
     */
    public function clear()
    {
        $this->setFilters(array(
            'sort' => array(
                'field' => null,
                'direction' => 'asc',
            ),
            'page' => 1,
            'filters' => array(),
        ));
    }

}