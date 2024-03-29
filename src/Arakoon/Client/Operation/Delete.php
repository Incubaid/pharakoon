<?php
/**
 * This file is part of Arakoon, a distributed key-value store.*
 * Copyright (C) 2010 Incubaid BVBA
 * Licensees holding a valid Incubaid license may use this file in
 * accordance with Incubaid's Arakoon commercial license agreement. For
 * more information on how to enter into this agreement, please contact
 * Incubaid (contact details can be found on http://www.arakoon.org/licensing).
 *
 * Alternatively, this file may be redistributed and/or modified under
 * the terms of the GNU Affero General Public License version 3, as
 * published by the Free Software Foundation. Under this license, this
 * file is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 *
 * See the GNU Affero General Public License for more details.
 * You should have received a copy of the
 * GNU Affero General Public License along with this program (file "COPYING").
 * If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Arakoon_Client_Operation_Delete
 * 
 * @category   	Arakoon
 * @package    	Arakoon_Client
 * @subpackage	Operation
 * @copyright 	Copyright (C) 2010 Incubaid BVBA
 * @license    	http://www.arakoon.org/licensing
 */
class Arakoon_Client_Operation_Delete extends Arakoon_Client_Operation
{
    private $_key;
    
    /**
     * @todo document
     */
    public function __construct($key)
    {
    	self::validateKey($key);
		
        $this->_key = $key;    	
    }
    
    /**
     * @todo document
     */
    public function encode($sequenced = FALSE)
    {
    	$buffer = "";
    	
    	if ($sequenced)
    	{
    		$buffer .= Arakoon_Client_Protocol::packInt(Arakoon_Client_Protocol::OP_CODE_SEQUENCED_DELETE);
    	}
    	else
    	{
    		$buffer .= Arakoon_Client_Protocol::packInt(Arakoon_Client_Protocol::OP_CODE_DELETE | Arakoon_Client_Protocol::OP_CODE_MAGIC);
    	}
    	
        $buffer .= Arakoon_Client_Protocol::packString($this->_key);
        
        return $buffer;
    }
}
?>