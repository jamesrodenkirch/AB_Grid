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
 * AB_Class
 *
 * Base class for all Active Binary library files
 *
 * @package ActiveBinary
 * @subpackage Class
 * @author James Rodenkirch <james@activebinary.com>
 */
class AB_Class
{

    /**
     * magic method to handle getters and setters for attributes
     *
     * @param <type> $name
     * @param <type> $arguments
     * @return AB_Grid
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        try {
            $action = substr($name, 0, 3);
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);
            $attribute = '_'. $name;

            switch ($action){
                case 'set':
                    // save the value
                    $this->$attribute = $arguments[0];

                    // return to allow chaining
                    return $this;
                    break;

                case 'get':
                    // return the saved value
                    return $this->$attribute;
                    break;
            }

        } catch (Exception $e) { throw $e; }
    }

}
