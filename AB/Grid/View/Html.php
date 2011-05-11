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
 * AB_Grid_View_Html
 *
 * HTML export class
 *
 * @property string $xml
 * @property string $pager
 * @property int $numResults
 *
 * @method AB_Grid_View_Html setXml(string $xml)
 * @method AB_Grid_View_Html setPager(string $pager)
 * @method AB_Grid_View_Html setNumResults(int $pager)
 * @method string getXml()
 * @method string getPager()
 * @method int getNumResults()
 *
 * @package Doctrine
 * @subpackage DataGrid
 * @author James Rodenkirch <james@activebinary.com>
 */
class AB_Grid_View_Html extends AB_Grid
{
    /**
     * @var string $_xml
     */
    protected $_xml = null;

    /**
     * @var string $_pager
     */
    protected $_pager = null;

    /**
     * @var string $_pager
     */
    protected $_numResults = 0;

    /* ---------------------------------------------------------------------- */

    /**
     * method to generate HTML output from the query results
     *
     * @return string
     */
    protected function build()
    {
        // if we have not set the results with an array, find via executing
        if (count($this->getResults()) == 0) {

            // execute the statement
            $filters = $this->getFilter()->getFilters();

            $this->getQuery()->setHydrationMode(Doctrine::HYDRATE_SCALAR);

            // find number of results
            $this->_findNumResults();

            // apply paging
            $this->_applyPaging();

            // generate results from the doctrine query
            $this->setResults($this->getQuery()->execute());

        } else {
            $this->setNumResults(count($this->getResults()));
        }

        // build xml
        $this->buildXML();

        // format xml with xsl style sheet
        $xslDoc = new DOMDocument();
        $xslDoc->load($this->getTemplate());
        $xmlDoc = $this->getXml();

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xslDoc);

        return $proc->transformToXML($xmlDoc);
    }

    /**
     * method to build the xml structure for the request
     */
    private function buildXML()
    {
        // create main xml structure
        $doc = new DOMDocument('1.0');
        $data = $doc->createElement('data');
        $doc->appendChild($data);

        $meta = $doc->createElement('meta');
        $data->appendChild($meta);

        $headers = $doc->createElement('headers');
        $data->appendChild($headers);

        $records = $doc->createElement('records');
        $data->appendChild($records);

        // add meta structure
        $key = $doc->createElement('key', $this->getKey());
        $meta->appendChild($key);

        $url = $doc->createElement('url');
        $meta->appendChild($url);

        $pager = $doc->createElement('pager');
        $text = $doc->createCDATASection($this->_buildPager());
        $pager->appendChild($text);
        $meta->appendChild($pager);

        $exports = $doc->createElement('exports');
        $meta->appendChild($exports);

        $filters = $doc->createElement('filters');
        $meta->appendChild($filters);


        $numresults = $doc->createElement('numresults');
        $meta->appendChild($numresults);

        $text = $doc->createTextNode($this->getNumResults());
        $numresults->appendChild($text);


        // add base url
        $this->_buildUrl($doc, $url);

        // add exports
        $this->_buildExports($doc, $exports);

        // add filters
        $this->_buildFilters($doc, $filters);

        // add headers
        $this->_buildHeaders($doc, $headers);

        // add records
        $this->_buildRecords($doc, $records);

        // save xml to instance
        $this->setXml($doc);
    }

    /**
     * method to build the pagination control
     *
     * @return string
     */
    private function _buildPager()
    {
        $nav = array();
        $filters = $this->getFilter()->getFilters();

        // find the pages that should show up in the list
        list($start, $pages, $last) = $this->_buildPageRange();

        if (!is_null($start)) {
            $nav[] = "<a href=\"javascript:AB_Grid('page', {$start});\" class='first'>{$start}</a>";
        }

        foreach ($pages as $p) {
            $class = $p == $filters['page'] ? "class='current'" : null;

            $nav[] = "<a href=\"javascript:AB_Grid('page', {$p});\" {$class}>{$p}</a>";
        }

        if (!is_null($last)) {
            $nav[] = "<a href=\"javascript:AB_Grid('page', {$last});\" class='last'>{$last}</a>";
        }

        // build html
        $html = "<div class='pagination'>";
        if (count($nav) > 1 ) {
            $html .= "Page: ". implode(' ', $nav);
        }

        $html .= " &nbsp; ({$this->getNumResults()} results)</div>";

        return $html;
    }


    /**
     * method to generate nodes for the base URL
     *
     * @param DOMDocument $doc
     * @param DOMElement $node
     */
    private function _buildUrl(DOMDocument &$doc, DOMElement &$node)
    {
        foreach ($this->getExporttypes() as $type){
            $attr = $doc->createAttribute('path_info');
            $text = $doc->createTextNode($_SERVER['PATH_INFO']);
            $attr->appendChild($text);
            $node->appendChild($attr);

            $attr = $doc->createAttribute('query_string');
            $text = $doc->createTextNode($_SERVER['QUERY_STRING']);
            $attr->appendChild($text);
            $node->appendChild($attr);
        }
    }


    /**
     * method to generate nodes for each registered export type
     *
     * @param DOMDocument $doc
     * @param DOMElement $node
     */
    private function _buildExports(DOMDocument &$doc, DOMElement &$node)
    {
        foreach ($this->getExporttypes() as $type){
            $el = $doc->createElement('export');
            $export = $node->appendChild($el);

            $attr = $doc->createAttribute('type');
            $text = $doc->createTextNode($type);
            $attr->appendChild($text);
            $export->appendChild($attr);
        }
    }

    /**
     * method to generate nodes for each filter
     *
     * @param DOMDocument $doc
     * @param DOMElement $node
     */
    private function _buildFilters(DOMDocument &$doc, DOMElement &$node)
    {
        $filters = $this->getFilter()->getFilters();
        $patterns = $this->getMapper()->getPatterns();

        foreach ($this->getMapper()->getHeaders() as $f=>$h) {
            $value = isset($filters['filters'][$f]) ? $filters['filters'][$f] : null;

            $el = $doc->createElement('filter');
            $filter = $node->appendChild($el);

            $attr = $doc->createAttribute('display');
            $text = $doc->createTextNode($value);
            $attr->appendChild($text);
            $filter->appendChild($attr);

            $attr = $doc->createAttribute('field');
            $text = $doc->createTextNode($f);
            $attr->appendChild($text);
            $filter->appendChild($attr);

            list($pattern) = $this->getMapper()->getSearchPattern($f, null);

            if ($pattern === false) {
                $type = 'NA';
            } elseif ($patterns[$f] == 'select') {
                $type = 'Select';
            } else {
                $type = 'Input';
            }

            $attr = $doc->createAttribute('type');
            $text = $doc->createTextNode($type);
            $attr->appendChild($text);
            $filter->appendChild($attr);

            // add filter options if needed
            if ($type === 'Select') {
                $optionel = $doc->createElement('options');
                $filter->appendChild($optionel);

                $options = $this->_findOptionsFromQuery($f);

                foreach ($options as $o) {
                    $el = $doc->createElement('option');
                    $optionel->appendChild($el);

                    $attr = $doc->createAttribute('display');
                    $text = $doc->createTextNode($o);
                    $attr->appendChild($text);
                    $el->appendChild($attr);
                }
            }
        }
    }

    /**
     * method to generate nodes for each header
     *
     * @param DOMDocument $doc
     * @param DOMElement $node
     */
    private function _buildHeaders(DOMDocument &$doc, DOMElement &$node)
    {
        foreach ($this->getMapper()->getHeaders() as $f=>$h) {
            $el = $doc->createElement('header');
            $header = $node->appendChild($el);

            $attr = $doc->createAttribute('display');
            $text = $doc->createTextNode($h);
            $attr->appendChild($text);
            $header->appendChild($attr);

            $attr = $doc->createAttribute('field');
            $text = $doc->createTextNode($f);
            $attr->appendChild($text);
            $header->appendChild($attr);

            list($pattern) = $this->getMapper()->getSearchPattern($f, null);

            $attr = $doc->createAttribute('allow_filter');
            $text = $doc->createTextNode($pattern !== false ? 'Yes' : 'No');
            $attr->appendChild($text);
            $header->appendChild($attr);
        }
    }

    /**
     * method to generate nodes for each result in the record set
     *
     * @param DOMDocument $doc
     * @param DOMElement $node
     */
    private function _buildRecords(DOMDocument &$doc, DOMElement &$node)
    {
        $mappers = array_keys($this->getMapper()->getHeaders());

        foreach ($this->getResults() as $r) {
            $el = $doc->createElement('record');
            $record = $node->appendChild($el);

            foreach ($mappers as $m){
                $el = $doc->createElement('field');
                $field = $record->appendChild($el);

                $attr = $doc->createAttribute('display');
                $text = $doc->createTextNode($this->getMapper()->getValue($r, $m));
                $attr->appendChild($text);
                $field->appendChild($attr);
            }
        }
    }

    /**
     * method to find the number of results that will be returned from the query
     */
    private function _findNumResults()
    {

        $this->setNumResults($this->getQuery()->count());

    }

    /**
     * method to find available filter options from the data represented in the
     * paseed query
     *
     * @param string $field
     * @return array
     */
    private function _findOptionsFromQuery($field)
    {
        $query = clone $this->getQuery();
        $select = $query->getDqlPart('select');

        $query->removeDqlQueryPart('select')
            ->removeDqlQueryPart('limit')
            ->groupBy($field)
            ->addSelect($field)
            ->orderBy($field);

        foreach ($select as $k=>$v) if (preg_match('/.+\.id/', $v)) {
            $query->addSelect($select[$k]);
        }

        $results = $query->execute(array(), DOCTRINE::HYDRATE_SCALAR);

        $options = array();
        $field = str_replace('.', '_', $field);

        foreach ($results as $r) {
            $options[] = $r[$field];
        }

        return $options;
    }

    /**
     * method to apply pagination limits
     */
    private function _applyPaging()
    {
        $filters = $this->getFilter()->getFilters();

        $this->getQuery()
            ->limit($this->getPagelimit())
            ->offset(($filters['page'] - 1) * $this->getPagelimit());
    }

    /**
     * method to build the pages to display
     *
     * @return array [mixed $first, array $pages, mixed $last]
     */
    private function _buildPageRange()
    {
        $pages = array();
        $first = null;
        $last = null;

        $filters = $this->getFilter()->getFilters();

        $total = ceil($this->getNumResults() / $this->getPagelimit());

        // set starting
        $start = $filters['page'] - 2 <= 0 ? 1 : $filters['page'] - 2;

        for ($i=0; $i<5; $i++){
            $page = $start + $i;
            if ($page <= $total){
                $pages[$i] = $page;
            }
        }

        // set first
        if (isset($pages[0]) && $pages[0] != 1){
            $first = 1;
        }

        // set last
        if (isset($pages[4]) && $pages[4] != $total){
            $last = $total;
        }

        return array($first, $pages, $last);
    }
}
