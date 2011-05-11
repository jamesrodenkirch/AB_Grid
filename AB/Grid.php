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
 * AB_Grid
 *
 * Base data grid class
 *
 * @property AB_Grid_Mapper $mapper
 * @property AB_Grid_Filter $filter
 * @property Doctrine_Query $query
 * @property Doctrine_Collection $results
 * @property array $fields
 * @property string $template
 * @property string $key
 * @property int $pagelimit
 * @property array $exporttypes
 *
 * @method AB_Grid setMapper(AB_Grid_Mapper $mapper)
 * @method AB_Grid setFilter(AB_Grid_Filter $filter)
 * @method AB_Grid setQuery(Doctrine_Query $query)
 * @method AB_Grid setResults(Doctrine_Collection $results)
 * @method AB_Grid setFields(array $fields)
 * @method AB_Grid setTemplate(string $template)
 * @method AB_Grid setKey(string $key)
 * @method AB_Grid setPagelimit(int $pagelimit)
 * @method AB_Grid setExporttypes(array $exporttypes)
 * @method AB_Grid_Mapper getMapper()
 * @method AB_Grid_Filter getFilter()
 * @method Doctrine_Query getQuery()
 * @method Doctrine_Collection getResults()
 * @method array getFields()
 * @method string getTemplate()
 * @method string getKey()
 * @method int getPagelimit()
 * @method array getExporttypes()
 *
 * @package Doctrine
 * @subpackage DataGrid
 * @author James Rodenkirch <james@activebinary.com>
 */
class AB_Grid extends AB_Class
{

    /**
     * @var AB_Grid_Mapper $_mapper
     */
    protected $_mapper = null;

    /**
     * @var AB_Grid_Filter $_filter
     */
    protected $_filter = null;

    /**
     * @var Doctrine_Query $_query
     */
    protected $_query = null;

    /**
     * @var Doctrine_Collection $_results
     */
    protected $_results = null;

    /**
     * @var array $_fields
     */
    protected $_fields = array();

    /**
     * @var string $_template
     */
    protected $_template = null;

    /**
     * @var string $_key
     */
    protected $_key = null;

    /**
     * @var int $_pagelimit
     */
    protected $_pagelimit = 5;

    /**
     * @var array $_exporttypes
     */
    protected $_exporttypes = array();

    /* ---------------------------------------------------------------------- */

    /**
     * factory method for creating the appropriate type
     *
     * @param string $type
     * @return AB_Grid AB_Grid_View_*
     */
    public static function create($type = 'html')
    {
        $type = "AB_Grid_View_" . ucfirst((strtolower($type)));
        return new $type;
    }

    /**
     * class constructor
     *
     * @return AB_Grid AB_Grid_View_*
     */
    public function  __construct()
    {
        // if a filter is in use
        if (isset($_GET['AB_Grid'])){
            $this->setFilter(AB_Grid_Filter::loadEncoded($_GET['AB_Grid']));

        // if no filters are set
        } else {
            $this->setFilter(new AB_Grid_Filter());
        }

        // process any passed in actions
        if (isset($_GET['AB_Grid_Action'])) {

            switch ($_GET['AB_Grid_Action']) {
                case 'sort';
                    $this->getFilter()->sort($_GET['AB_Grid_Value']);
                    break;

                case 'filter';
                    $this->getFilter()->filter($_GET['AB_Grid_Value']);
                    break;

                case 'page';
                    $this->getFilter()->page($_GET['AB_Grid_Value']);
                    break;

                case 'clear';
                    $this->getFilter()->clear();
                    break;

                case 'export';
                    $this->getFilter()->export($_GET['AB_Grid_Value']);
                    break;
            }
        }

        // if we processed an action, go back to the referring page
        if (isset($_GET['AB_Grid_Action'])) {
            $_GET['AB_Grid'] = $this->getFilter()->getEncoded();

            unset($_GET['type']);

            if ($_GET['AB_Grid_Action'] == 'export') {
                $_GET['type'] = $_GET['AB_Grid_Value'];
            }
            
            unset($_GET['AB_Grid_Action']);
            unset($_GET['AB_Grid_Value']);

            $url = "{$_SERVER['PATH_INFO']}?" . http_build_query($_GET);
            die(header("Location: {$url}"));
        }

        return $this;
    }

    /* ---------------------------------------------------------------------- */

    /**
     * method to generate a data grid in the requested type
     *
     * @return mixed
     */
    public function generate()
    {
        // apply filters
        $this->applyFilters();

        // set fields/header row
        $headers = $this->getMapper()->getHeaders();
        $this->setFields($headers);

        return $this->build();
    }

    /**
     * method to apply any filters from the associated filter object
     */
    protected function applyFilters()
    {
        $filters = $this->getFilter()->getFilters();

        // apply sort
        if (!empty($filters['sort']['field'])) {
            $field = $filters['sort']['field'];
            $direction = $filters['sort']['direction'];

            $this->getQuery()->addOrderBy("{$field} {$direction}");
        }

        // apply field filters
        $patterns = $this->getMapper()->getPatterns();
        if(is_array($filters['filters'])) {
            foreach ($filters['filters'] as $field=>$value) if (!empty($value)){
                list($operator, $value) = $this->getMapper()
                    ->getSearchPattern($field, trim($value));

                if ($operator !== false) {
                    $this->getQuery()
                        ->addWhere("{$field} {$operator} ?", $value);
                }
            }
        }
    }

    /**
     * method to register and export type for the grid data
     *
     * @param string $type
     */
    public function registerExport($type)
    {
        $this->_exportTypes[] = $type;
    }

    /* ---------------------------------------------------------------------- */

    /**
     * method for building the actual output.
     *
     * child classes override this method to perform type specific output
     * generation
     */
    protected function build() {}
}
