<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-2010 Martin Bless (martin@mbless.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Currently this class serves as a development platform 
 * for producing alternative pdf output
 *
 * $Id$
 *
 * @author	Martin Bless <martin@mbless.de>
 */

	// a tabulator
define('TAB', chr(9));
	// a linefeed
define('LF', chr(10));
	// a carriage return
define('CR', chr(13));
	// a CR-LF combination
define('CRLF', CR . LF);


final class pdfresult {

		// Example constants
	const DUNNO_0 = 0;
	const DUNNO_1 = 1;

	public static function main($caller) {
		$content = 'hi from pdfresult:main()';
		return $content;
	}
}

?>