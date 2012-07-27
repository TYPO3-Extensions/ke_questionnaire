<?php
$extensionPath = t3lib_extMgm::extPath('ke_questionnaire');
return array(
	'tx_kequestionnaire_pi1' => $extensionPath . 'pi1/class.tx_kequestionnaire_pi1.php',
	'tx_kequestionnaire_scheduler' => $extensionPath . 'scheduler/class.tx_kequestionnaire_scheduler.php',
	'tx_kequestionnaire_scheduler_export' => $extensionPath . 'scheduler/class.tx_kequestionnaire_scheduler_export.php',
	'tx_kequestionnaire_swiftmailer' => $extensionPath . 'res/other/class.swiftmailer.php'
);
?>