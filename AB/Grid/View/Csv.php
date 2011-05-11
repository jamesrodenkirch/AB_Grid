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
 * AB_Grid_View_Csv
 *
 * CSV export class
 *
 * @package Doctrine
 * @subpackage DataGrid
 * @author James Rodenkirch <james@activebinary.com>
 */
class AB_Grid_View_Csv extends AB_Grid
{

    /**
     * method to generate a CSV file from the query results
     */
    protected function build()
    {
        // execute and store the results if the results were not passed via array
        if (count($this->getResults()) == 0) {
            
            $results = $this->getQuery()->execute(array(), Doctrine::HYDRATE_SCALAR);
            $this->setResults($results);
        }

        // build csv
        $csv = $this->buildCSV();

        // output csv
		header('Pragma: private');
		header('Expires: 0');
		header('Cache-Control: private, must-revalidate');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: application/octet-stream");
		header('Content-Disposition: attachment; filename="export.csv"');

        echo $csv;
        die;
    }

    /**
     * method to build the CSV content
     *
     * @return string
     */
    protected function buildCSV()
    {
        $rows = array();
        $headers = $this->getMapper()->getHeaders();

        // add header row
        $rows[] = implode(",", $headers);

        // add data rows
        $mappers = array_keys($headers);

        foreach ($this->getResults() as $r) {
            $fields = array();

            foreach ($mappers as $m){
                $value = $this->getMapper()->getValue($r, $m);
                $fields[] = "\"{$value}\"";
            }

            $rows[] = implode(",", $fields);
        }

        return implode("\r\n", $rows);
    }

}
