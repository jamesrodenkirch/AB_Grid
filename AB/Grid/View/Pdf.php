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
 * AB_Grid_View_Pdf
 *
 * PDF export class
 *
 * @property string $title
 * @property string $subtitle
 *
 * @method AB_Grid_View_Pdf setTitle(string $title)
 * @method AB_Grid_View_Pdf setSubtitle(string $subtitle)
 * @method string getTitle()
 * @method string getSubtitle()
 *
 * @package Doctrine
 * @subpackage DataGrid
 * @author James Rodenkirch <james@activebinary.com>
 */
class AB_Grid_View_Pdf extends AB_Grid
{
    /**
     * @var string $_title
     */
    protected $_title = null;

    /**
     * @var string $_subtitle
     */
    protected $_subtitle = null;

    /**
     * method to build the PDF content
     */
    protected function build()
    {
        // if the results need to be extracted from a query
        if (count($this->getResults()) == 0) {
            $results = $this->getQuery()->execute(array(), Doctrine::HYDRATE_SCALAR);

        // if the results were passed via array
        } else {
            $results = $this->getResults();
        }

        $pdf = new AB_Grid_Pdf();
        $pdf->title = $this->getTitle();
        $pdf->subtitle = $this->getSubtitle();
        $pdf->mapper = $this->getMapper();
        $pdf->results = $results;
        $pdf->generate();
        die;
    }

}
