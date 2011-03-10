<?php
$extensionPath = t3lib_extMgm::extPath('ke_questionnaire');
return array(
	'tx_kequestionnaire_scheduler' => $extensionPath . 'scheduler/class.tx_kequestionnaire_scheduler.php',
        'tx_kequestionnaire_scheduler_export' => $extensionPath . 'scheduler/class.tx_kequestionnaire_scheduler_export.php'
);
?>