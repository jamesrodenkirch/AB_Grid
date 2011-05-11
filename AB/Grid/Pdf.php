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
 * AB_Grid_Pdf
 *
 * PDF generation class
 *
 * @package Doctrine
 * @subpackage DataGrid
 * @author James Rodenkirch <james@activebinary.com>
 */
class AB_Grid_Pdf extends FPDF
{
    /**
     * @var string $title
     */
    public $title = null;

    /**
     * @var string $subtitle
     */
    public $subtitle = null;

    /**
     * @var AB_Grid_Mapper $mapper
     */
    public $mapper = null;

    /**
     * @var array $results
     */
    public $results = array();

    /**
     * @var array $_widths
     */
    private $_widths = array();

    /**
     * @var array $_records
     */
    private $_records = array();

    /**
     * @var array $_headers
     */
    private $_headers = array();

    /**
     * @var array $_headers
     */
    private $_pages = array();

    /**
     * @var int $_maxwidth
     */
    private $_maxwidth = 270;

    /**
     * @var int $_maxrows
     */
    private $_maxrows= 24;

    /**
     * method to generte the PDF
     */
    public function generate()
    {      
        // build results and column widths
        $this->_buildResults();

        // split pages
        $pages = array_chunk ($this->_records, $this->_maxrows);

        // process each page
        foreach ($pages as $page) {
            foreach ($this->_headers as $header) {
                $this->AddPage('L', 'Letter');
                $this->addHeaderRow($header);

                foreach ($page as $row){
                    $data = array();

                    foreach ($header as $field=>$h) {
                        $data[$field] = $row[$field];
                    }

                    $this->addRow($data);
                }
            }
        }

        // output
        parent::Output();
    }

    /**
     * method to build the array of results.
     */
    private function _buildResults()
    {
        // set headers
        $headers = $this->mapper->getHeaders();
        $this->_setWidths($headers);

        // add data rows
        $mappers = array_keys($headers);
        foreach ($this->results as $r) {
            $fields = array();
            foreach ($mappers as $m){
                $fields[$m] = $this->mapper->getValue($r, $m);
            }
            $this->_records[] = $fields;
            $this->_setWidths($fields);
        }

        // split headers into pages
        $width = 0;
        $index = 0;
        foreach ($headers as $k=>$h) {
            if ($width + $this->_widths[$k] > $this->_maxwidth){
                $index++;
                $width = 0;
            }

            $this->_headers[$index][$k] = $h;
            $width += $this->_widths[$k];
        }
    }

    /**
     * method to set the max column width based on the string length of the
     * record
     *
     * @param array $data
     */
    private function _setWidths($data)
    {
        $this->SetFont('Arial', 'B', 7);

        foreach ($data as $k=>$v) {
            $width = ceil($this->GetStringWidth($v) * 1.1);

            if (!isset($this->_widths[$k]) || $this->_widths[$k] < $width) {
                $this->_widths[$k] = $width;
            }
        }
    }

    /**
     * method to generate a data row
     */
    private function addRow($data)
    {
        $this->SetFont('Arial', '', 7);
        $this->SetFillColor(255,255,255);
        $this->SetDrawColor(175,175,175);
        $this->SetLineWidth(.1);

        foreach ($data as $k=>$v) {
            $this->Cell($this->_widths[$k], 6, $v, 1, 0, 'L', true);
        }

        $this->Ln();
    }

    /**
     * method to generate a header row
     */
    private function addHeaderRow($data)
    {
        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(225,225,225);
        $this->SetDrawColor(175,175,175);
        $this->SetLineWidth(.1);

        foreach ($data as $k=>$v) {
            $this->Cell($this->_widths[$k], 6, $v, 1, 0, 'C', true);
        }

        $this->Ln();
    }

    /**
     * method to add a header to the pdf
     *
     * (non-PHPdoc)
     * @see lib/fpdf16/FPDF#Header()
     */
    public function Header()
    {
        $this->SetFont('Times','B',18);
        $this->Cell($this->_maxwidth,10,$this->title, null, null, 'C');
        $this->Ln();

        $this->SetDrawColor(30, 30, 30);
        $this->SetLineWidth(.1);
        $top = $this->GetY()+5;
        $this->Line(10,$top,$this->_maxwidth,$top);

        $this->Ln(10);

        $this->SetFont('Times','B',14);
        $this->Cell($this->_maxwidth,10,$this->subtitle, null, null, 'C');

        $this->Ln(15);
    }

    /**
     * method to add a footer to the pdf
     *
     * (non-PHPdoc)
     * @see lib/fpdf16/FPDF#Footer()
     */
    public function Footer()
    {
        //Position at 1.5 cm from bottom
        $this->SetY(-15);
        //Arial italic 8
        $this->SetFont('Arial','I',8);
        //Page number
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}