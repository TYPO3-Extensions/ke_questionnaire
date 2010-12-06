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


class OutputGenerator {
		// $that ist the '$this' object of tx_questionnaire_pi1
	var $that;

	function __construct($caller=False) {
		$this->that = $caller;
	}

	function getPDF() {
		$content = '';
		$type = 'empty';
		$type = $this->that->piVars['type'];
		if (t3lib_extMgm::isLoaded('ke_dompdf')){
			// require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/class.dompdf_export.php');
			require_once(t3lib_extMgm::extPath('ke_questionnaire').'pi1/class.pdfresult_dompdf.php');
			$pdfdata = '';
			$pdf_conf = $this->that->conf;
			$storage_pid = $this->that->ffdata['storage_pid'];
				// $conf, $pid, $title, $ffdata
			$pdf = new pdfresult_dompdf($pdf_conf, $storage_pid, 'test', $this->that->cObj->data['pi_flexform']['data']);
			switch ($type){
				case 'empty':
					$pdfdata = $pdf->getPDFBlank();
					exit;
					break;
				case 'filled':
					$this->that->getResults($this->that->piVars['p_id'],false);
					$pdfdata = $pdf->getPDFFilled($this->that->saveArray);
					exit;
					break;
				case 'compare':
					$this->that->getResults($this->that->piVars['p_id'],false);
					$pdfdata = $pdf->getPDFCompare($this->that->saveArray);
					exit;
					break;
				case 'outcomes':
					$this->that->getResults($this->that->piVars['p_id'],false);
					$pdfdata = $pdf->getPDFOutcomes($this->that->saveArray);
					exit;
					break;
				case 'outcomesHtml':
					$this->that->getResults($this->that->piVars['p_id'],false);
					$content = $pdf->getPDFOutcomesHtml($this->that->saveArray);
					break;
				default:
					$content = 'unknown value \'' . $type . '\' of $type in getPDF()';
					break;
			}
		}
		return $content;
	}
}

final class pdfresult {

		// Example constants
	const DUNNO_0 = 0;
	const DUNNO_1 = 1;

	public static function main($caller) {
		$generator = new OutputGenerator($caller);
		if ($caller->piVars['pdf'] == 1){
			$content = $generator->getPDF();
		} else {
			$content = 'ho ho ho from pdfresult:main()';
		}
		return $content;
	}

}

?>