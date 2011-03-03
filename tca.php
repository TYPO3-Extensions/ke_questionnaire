<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_kequestionnaire_questions"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_questions"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,type,title,show_title,text,helptext,image,mandatory,mandatory_correct,time,dependant_show,open_type,open_pre_text,open_in_text,open_post_text,open_validation,closed_type,closed_selectsize,closed_inputfield,matrix_type,matrix_validation,demographic_type,privacy_post,privacy_link,privacy_file,answers,columns,dependancy,subquestions"
	),
	"feInterface" => $TCA["tx_kequestionnaire_questions"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_kequestionnaire_questions',
				'foreign_table_where' => 'AND tx_kequestionnaire_questions.pid=###CURRENT_PID### AND tx_kequestionnaire_questions.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					/*Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type.I.open", "open"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type.I.closed", "closed"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type.I.matrix", "matrix"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type.I.semantic", "semantic"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type.I.demographic", "demographic"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type.I.privacy", "privacy"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type.I.blind", "blind"),*/
				),
                                'default' => 'blind',
				"itemsProcFunc" => "tx_kequestionnaire_tx_kequestionnaire_questions_type->main",
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required",
			)
		),
		"show_title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.show_title",
			"config" => Array (
				"type" => "check",
			)
		),
		"text" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.text",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"helptext" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.helptext",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"image" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",
				"max_size" => 500,
				"uploadfolder" => "uploads/tx_kequestionnaire",
				"show_thumbs" => 1,
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
                "image_position" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.0", "left"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.1", "right"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.2", "top"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.3", "bottom"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"mandatory" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.mandatory",
			"config" => Array (
				"type" => "check",
			)
		),
		"mandatory_correct" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.mandatory_correct",
			"config" => Array (
				"type" => "check",
			)
		),
		"time" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.time",
			"config" => Array (
				"type"     => "input",
				"size"     => "6",
				"max"      => "6",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100000",
					"lower" => "1"
				),
				"default" => 0
			)
		),
		"dependant_show" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.dependant_show",
			"config" => Array (
				"type" => "check",
                                "default" => "1"
			)
		),
		"open_type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_type",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_type.I.0", "0"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_type.I.1", "1"),
				),
				//"itemsProcFunc" => "res/tx_kequestionnaire_tx_kequestionnaire_questions_open_type->main",
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"open_pre_text" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_pre_text",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"open_in_text" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_in_text",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"open_post_text" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_post_text",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"open_validation" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_validation",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_validation.I.0", "0"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_validation.I.1", "numeric"),
                                        Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_validation.I.1a", "integer"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_validation.I.2", "date"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_validation.I.3", "email"),
                                        Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_validation.I.4", "text"),
				),
				//"itemsProcFunc" => "res/tx_kequestionnaire_tx_kequestionnaire_questions_open_validation->main",
				"size" => 1,
				"maxitems" => 1,
			)
		),
                "open_validation_text" => Array (
                    'displayCond' => 'FIELD:open_validation:=:text',
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_validation_text",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"open_compare_text" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.open_compare_text",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"closed_type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_type",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_type.I.0", "radio_single"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_type.I.1", "check_multi"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_type.I.2", "select_single"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_type.I.3", "select_multi"),
                                        Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_type.I.4", "sbm_button"),
				),
				//"itemsProcFunc" => "res/tx_kequestionnaire_tx_kequestionnaire_questions_closed_type->main",
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"closed_selectsize" => Array (
			"displayCond" => "FIELD:closed_type:=:select_multi",
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_selectsize",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "3",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100",
					"lower" => "0"
				),
				"default" => 0
			)
		),
                "closed_maxanswers" => Array (
                        "displayCond" => "FIELD:closed_type:IN:check_multi,select_multi",
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_maxanswers",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "3",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100",
					"lower" => "0"
				),
				"default" => 0
			)
		),
                "closed_randomanswers" => Array (
                        "displayCond" => "EXT:ke_questionnaire_premium:LOADED:true",
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_randomanswers",
			"config" => Array (
				"type" => "check",
                        )
		),
		"closed_inputfield" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.closed_inputfield",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "3",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100",
					"lower" => "0"
				),
				"default" => 0
			)
		),
		"matrix_type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_type",
			"config" => Array (
				"type" => "select",
				"items" => Array (
                                        Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_type.I.0", "radio"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_type.I.1", "check"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_type.I.2", "input"),
				),
				//"itemsProcFunc" => "res/tx_kequestionnaire_tx_kequestionnaire_questions_matrix_type->main",
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"matrix_validation" => Array (
                    "displayCond" => "FIELD:matrix_type:=:input",
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_validation",
			"config" => Array (
				"type" => "select",
				"items" => Array (
                                        Array("--", ""),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_validation.I.0", "numeric"),
                                        Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_validation.I.0a", "integer"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_validation.I.1", "date"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_validation.I.2", "percent"),
				),
				//"itemsProcFunc" => "res/tx_kequestionnaire_tx_kequestionnaire_questions_matrix_validation->main",
				"size" => 1,
				"maxitems" => 1,
			)
		),
                "matrix_inputfield" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_inputfield",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "3",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100",
					"lower" => "0"
				),
				"default" => 0
			)
		),
                "matrix_maxanswers" => Array (
                        "displayCond" => "FIELD:matrix_type:=:check",
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.matrix_maxanswers",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "3",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100",
					"lower" => "0"
				),
				"default" => 0
			)
		),
		"demographic_type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.demographic_type",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.demographic_type.I.0", "demo"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.demographic_type.I.1", "address"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.demographic_type.I.2", "both"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
                "demographic_fields" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.demographic_fields",
			"config" => Array (
				"type" => "flex",
                                "ds" => Array(
                                    "default" => "<T3DataStructure>
                                                    <sheets>
                                                        <sDEF>
                                                            <ROOT>
                                                                <TCEforms>
                                                                    <sheetTitle>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.FeUser_Fields_Sheet</sheetTitle>
                                                                </TCEforms>
                                                                <type>array</type>
                                                                <el>
                                                                    <FeUser_Fields>
                                                                        <TCEforms>
                                                                            <label>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.FeUser_Fields</label>
                                                                            <config>
                                                                                <type>select</type>
                                                                                <renderMode>checkbox</renderMode>
                                                                                <maxitems>100</maxitems>
                                                                                <itemsProcFunc>tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields->get_feuser_fields</itemsProcFunc>
                                                                            </config>
                                                                        </TCEforms>
                                                                    </FeUser_Fields>
                                                                </el>
                                                            </ROOT>
                                                        </sDEF>
                                                        <mDEF>
                                                            <ROOT>
                                                                <TCEforms>
                                                                    <sheetTitle>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.FeUser_Mandatory_Sheet</sheetTitle>
                                                                </TCEforms>
                                                                <type>array</type>
                                                                <el>
                                                                    <FeUser_Fields>
                                                                        <TCEforms>
                                                                            <label>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.FeUser_Mandatory</label>
                                                                            <config>
                                                                                <type>select</type>
                                                                                <renderMode>checkbox</renderMode>
                                                                                <maxitems>100</maxitems>
                                                                                <itemsProcFunc>tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields->get_feuser_mandatory</itemsProcFunc>
                                                                            </config>
                                                                        </TCEforms>
                                                                    </FeUser_Fields>
                                                                </el>
                                                            </ROOT>
                                                        </mDEF>
                                                    </sheets>
                                                </T3DataStructure>
                                    "
                                )
			)
		),
                "demographic_addressfields" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.demographic_addressfields",
			"config" => Array (
				"type" => "flex",
                                "ds" => Array(
                                    "default" => "<T3DataStructure>
                                                    <sheets>
                                                        <sDEF>
                                                            <ROOT>
                                                                <TCEforms>
                                                                    <sheetTitle>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.FeUser_Fields_Sheet</sheetTitle>
                                                                </TCEforms>
                                                                <type>array</type>
                                                                <el>
                                                                    <FeUser_Fields>
                                                                        <TCEforms>
                                                                            <label>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.FeUser_Fields</label>
                                                                            <config>
                                                                                <type>select</type>
                                                                                <renderMode>checkbox</renderMode>
                                                                                <maxitems>100</maxitems>
                                                                                <itemsProcFunc>tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields->get_ttaddress_fields</itemsProcFunc>
                                                                            </config>
                                                                        </TCEforms>
                                                                    </FeUser_Fields>
                                                                </el>
                                                            </ROOT>
                                                        </sDEF>
                                                        <mDEF>
                                                            <ROOT>
                                                                <TCEforms>
                                                                    <sheetTitle>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.FeUser_Mandatory_Sheet</sheetTitle>
                                                                </TCEforms>
                                                                <type>array</type>
                                                                <el>
                                                                    <FeUser_Fields>
                                                                        <TCEforms>
                                                                            <label>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.FeUser_Mandatory</label>
                                                                            <config>
                                                                                <type>select</type>
                                                                                <renderMode>checkbox</renderMode>
                                                                                <maxitems>100</maxitems>
                                                                                <itemsProcFunc>tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields->get_ttaddress_mandatory</itemsProcFunc>
                                                                            </config>
                                                                        </TCEforms>
                                                                    </FeUser_Fields>
                                                                </el>
                                                            </ROOT>
                                                        </mDEF>
                                                    </sheets>
                                                </T3DataStructure>
                                    "
                                )
			)
		),
		"privacy_post" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.privacy_post",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"privacy_link" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.privacy_link",
			"config" => Array (
				"type"     => "input",
				"size"     => "15",
				"max"      => "255",
				"checkbox" => "",
				"eval"     => "trim",
				"wizards"  => array(
					"_PADDING" => 2,
					"link"     => array(
						"type"         => "popup",
						"title"        => "Link",
						"icon"         => "link_popup.gif",
						"script"       => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					)
				)
			)
		),
		"privacy_file" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.privacy_file",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",
				"disallowed" => "php,php3",
				"max_size" => 500,
				"uploadfolder" => "uploads/tx_kequestionnaire",
				"show_thumbs" => 1,
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"answers" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.answers",
			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_kequestionnaire_answers",
                                "foreign_field" => "question_uid",
				"foreign_sortby" => "sorting",
				"maxitems" => 1000,
				"behaviour" => Array(
                                    "localizationMode" => "select",
                                ),
                                "appearance" => Array(
                                    'collapseAll' => 1,
                                    'expandSingle' => 1,
                                    "showSynchronizationLink" => 1,
                                    "showAllLocalizationLink" => 1,
                                    "showPossibleLocalizationRecords" => 1,
                                    "showRemovedLocalizationRecords" => 1,
                                ),
			)
		),
		"columns" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.columns",
			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_kequestionnaire_columns",
                                "foreign_field" => "question_uid",
				"foreign_sortby" => "sorting",
				"maxitems" => 100,
				"behaviour" => Array(
                                    "localizationMode" => "select",
                                ),
                                "appearance" => Array(
                                    'collapseAll' => 1,
                                    'expandSingle' => 1,
                                    "showSynchronizationLink" => 1,
                                    "showAllLocalizationLink" => 1,
                                    "showPossibleLocalizationRecords" => 1,
                                    "showRemovedLocalizationRecords" => 1,
                                ),
			)
		),
		"dependancy" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.dependancy",
			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_kequestionnaire_dependancies",
                                "foreign_field" => "dependant_question",
				"maxitems" => 20,
				"behaviour" => Array(
                                    "localizationMode" => "select",
                                ),
                                "appearance" => Array(
                                    'collapseAll' => 1,
                                    'expandSingle' => 1,
                                    "showSynchronizationLink" => 1,
                                    "showAllLocalizationLink" => 1,
                                    "showPossibleLocalizationRecords" => 1,
                                    "showRemovedLocalizationRecords" => 1,
                                ),
			)
		),
        'dependancy_simple' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.dependancy_simple',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"subquestions" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.subquestions",
			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_kequestionnaire_subquestions",
                                "foreign_field" => "question_uid",
				"maxitems" => 1000,
				"behaviour" => Array(
                                    "localizationMode" => "select",
                                ),
                                "appearance" => Array(
                                    'collapseAll' => 1,
                                    'expandSingle' => 1,
                                    "showSynchronizationLink" => 1,
                                    "showAllLocalizationLink" => 1,
                                    "showPossibleLocalizationRecords" => 1,
                                    "showRemovedLocalizationRecords" => 1,
                                ),
			)

		),
                "sublines" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.sublines",
			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_kequestionnaire_sublines",
                                "foreign_field" => "question_uid",
				"maxitems" => 1000,
				"behaviour" => Array(
                                    "localizationMode" => "select",
                                ),
                                "appearance" => Array(
                                    'collapseAll' => 1,
                                    'expandSingle' => 1,
                                    "showSynchronizationLink" => 1,
                                    "showAllLocalizationLink" => 1,
                                    "showPossibleLocalizationRecords" => 1,
                                    "showRemovedLocalizationRecords" => 1,
                                ),
			)

		)
	),
	"types" => array (
                "0" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                dependant_show,
                                dependancy_simple,
                                dependancy"),
				"open" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                mandatory,".
                                //"time,".
                                "dependant_show,
                                dependancy_simple,
                                dependancy,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.type_based,
                                open_type,
                                open_pre_text,
                                open_in_text,
                                open_post_text,
                                open_validation,
                                open_validation_text,
								open_compare_text"),
                "closed" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                mandatory,
                                mandatory_correct,".
                                //"time,".
                                "dependant_show,
                                dependancy_simple,
                                dependancy,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.type_based,
                                closed_type,
                                closed_selectsize,
                                closed_maxanswers,
                                closed_randomanswers,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.answers,"
                                //closed_inputfield,
                                ."answers"),
                "matrix" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                mandatory,".
                                //"time,".
                                "dependant_show,
                                dependancy_simple,
                                dependancy,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.type_based,
                                matrix_type,
                                matrix_validation,
                                matrix_maxanswers,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.sub_types,
                                matrix_inputfield,
                                columns,
                                subquestions"),
                /*"ematrix" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                mandatory,".
                                //"time,".
                                "dependant_show,
                                dependancy_simple,
                                dependancy,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.sub_types,
                                submatrix,
                                matrix_inputfield,
                                subquestions"),*/
                "semantic" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                mandatory,".
                                //"time,".
                                "dependant_show,
                                dependancy_simple,
                                dependancy,
                                --div--;SubTypes,
                                columns,
                                sublines"),
                "demographic" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                mandatory,".
                                //"time,".
                                "dependant_show,
                                dependancy_simple,
                                dependancy,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.type_based,
                                demographic_type,
                                demographic_fields,
                                demographic_addressfields"),
                "privacy" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                mandatory,".
                                //"time,".
                                "dependant_show,
                                dependancy_simple,
                                dependancy,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.type_based.ds,
                                privacy_post,
                                privacy_link,
                                privacy_file"),
                "blind" => array(
                            "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,
                                show_title;;;;3-3-3,
                                text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                helptext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/],
                                image,
                                image_position,
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.flow,
                                dependant_show,
                                dependancy_simple,
                                dependancy"),
		  "refusal" => array(
                             "showitem" => "
                                --div--;LLL:EXT:ke_questionnaire/locallang.xml:tx_kequestionnaire.base,
                                sys_language_uid;;;;1-1-1,
                                l18n_parent,
                                l18n_diffsource,
                                hidden;;1,
                                type,
                                title;;;;2-2-2,,"
                                ),
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_kequestionnaire_answers"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_answers"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,title,value,correct_answer,text,helptext,image"
	),
	"feInterface" => $TCA["tx_kequestionnaire_answers"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_kequestionnaire_answers',
				'foreign_table_where' => 'AND tx_kequestionnaire_answers.pid=###CURRENT_PID### AND tx_kequestionnaire_answers.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
                "question_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.question_uid",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_questions",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required",
			)
		),
                "show_input" => Array (
                        "exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.show_input",
			"config" => Array (
				"type" => "check",
                        )
		),
                "validate_input" => Array (
                        "exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.validate_input",
			"config" => Array (
				"type" => "check",
                        )
		),
		"value" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.value",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "-1000"
				),
				"default" => 0
			)
		),
		"correct_answer" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.correct_answer",
			"config" => Array (
				"type" => "check",
			)
		),
		"text" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.text",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"helptext" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.helptext",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"image" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.image",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",
				"max_size" => 500,
				"uploadfolder" => "uploads/tx_kequestionnaire",
				"show_thumbs" => 1,
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
                "image_position" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.0", "left"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.1", "right"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.2", "top"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.3", "bottom"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
                "finish_page_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.finish_page_uid",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "pages",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent,question_uid, title;;;;2-2-2, show_input, validate_input, value;;;;3-3-3, correct_answer, text;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];4-4-4, helptext;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], image;;;;5-5-5, image_position,finish_page_uid")
                //"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, question_uid, title;;;;2-2-2, value;;;;3-3-3, correct_answer, text;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], helptext;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], image")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_kequestionnaire_columns"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_columns"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,fe_group,title,image"
	),
	"feInterface" => $TCA["tx_kequestionnaire_columns"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_kequestionnaire_columns',
				'foreign_table_where' => 'AND tx_kequestionnaire_columns.pid=###CURRENT_PID### AND tx_kequestionnaire_columns.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
                "question_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.question_uid",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_questions",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
                "different_type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_columns.different_type",
			"config" => Array (
				"type" => "select",
				"items" => Array (
                                        Array("---", ""),
                                        Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_columns.different_type.I.0", "radio"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_columns.different_type.I.1", "check"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_columns.different_type.I.2", "input"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_columns.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"image" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_columns.image",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",
				"max_size" => 500,
				"uploadfolder" => "uploads/tx_kequestionnaire",
				"show_thumbs" => 1,
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
                "image_position" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.0", "left"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.1", "right"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.2", "top"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.3", "bottom"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
                "maxanswers" => Array (
                        "exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_columns.maxanswers",
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "3",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100",
					"lower" => "0"
				),
				"default" => 0
			)
		),
		
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent,question_uid, title;;;;2-2-2, different_type, maxanswers, image;;;;3-3-3, image_position")
                //"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, question_uid, title;;;;2-2-2, image;;;;3-3-3")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);



$TCA["tx_kequestionnaire_subquestions"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_subquestions"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,title,text,image"
	),
	"feInterface" => $TCA["tx_kequestionnaire_subquestions"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_kequestionnaire_subquestions',
				'foreign_table_where' => 'AND tx_kequestionnaire_subquestions.pid=###CURRENT_PID### AND tx_kequestionnaire_subquestions.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
                "question_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.question_uid",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_questions",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
                'title_line' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_subquestions.title_line',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
                'render_as_slider' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_subquestions.render_as_slider',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
                "title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_subquestions.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"text" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_subquestions.text",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"image" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_subquestions.image",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",
				"max_size" => 500,
				"uploadfolder" => "uploads/tx_kequestionnaire",
				"show_thumbs" => 1,
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
                "image_position" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.0", "left"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.1", "right"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.2", "top"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.3", "bottom"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent,question_uid, title;;;;2-2-2, title_line, render_as_slider, text;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3, image, image_position")
                //"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, question_uid, title;;;;2-2-2, text;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3, image")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_kequestionnaire_dependancies"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_dependancies"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,fe_group,dependant_question,activating_question,activating_value"
	),
	"feInterface" => $TCA["tx_kequestionnaire_dependancies"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_kequestionnaire_dependancies',
				'foreign_table_where' => 'AND tx_kequestionnaire_dependancies.pid=###CURRENT_PID### AND tx_kequestionnaire_dependancies.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"dependant_question" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies.dependant_question",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_questions",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
                "dependant_outcome" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies.dependant_outcome",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_questions",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		/*"activating_question" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies.activating_question",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_questions",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),*/
                "activating_question" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies.activating_question",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					//Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies.activating_value.I.0", "0"),
				),
				"itemsProcFunc" => "tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_question->main",
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"activating_value" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies.activating_value",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies.activating_value.I.0", "0"),
				),
				"itemsProcFunc" => "tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_value->main",
				"size" => 1,
				"maxitems" => 1,
			)
		),
                "activating_formula" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies.activating_formula",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "dependant_question, activating_question, activating_value, activating_formula")
                //"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, activating_question, activating_value")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);



$TCA["tx_kequestionnaire_sublines"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_sublines"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,start,end"
	),
	"feInterface" => $TCA["tx_kequestionnaire_sublines"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_kequestionnaire_sublines',
				'foreign_table_where' => 'AND tx_kequestionnaire_sublines.pid=###CURRENT_PID### AND tx_kequestionnaire_sublines.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
                "question_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers.question_uid",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_questions",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"start" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_sublines.start",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"end" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_sublines.end",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent,question_uid, start, end")
                //"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, question_uid, start, end")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);

$TCA["tx_kequestionnaire_outcomes"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_outcomes"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,title,value_start,value_end,text,image"
	),
	"feInterface" => $TCA["tx_kequestionnaire_outcomes"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_kequestionnaire_outcomes',
				'foreign_table_where' => 'AND tx_kequestionnaire_outcomes.pid=###CURRENT_PID### AND tx_kequestionnaire_outcomes.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
        "type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes.type",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes.type.value", "value"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes.type.dependancy", "dependancy"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"value_start" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes.value_start",
			"config" => Array (
				"type"     => "input",
				"size"     => "5",
				"max"      => "5",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "-1000"
				),
				"default" => 0
			)
		),
		"value_end" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes.value_end",
			"config" => Array (
				"type"     => "input",
				"size"     => "5",
				"max"      => "5",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "-1000"
				),
				"default" => 0
			)
		),
		"text" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes.text",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"image" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes.image",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",
				"max_size" => 500,
				"uploadfolder" => "uploads/tx_kequestionnaire",
				"show_thumbs" => 1,
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
                "image_position" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.0", "left"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.1", "right"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.2", "top"),
					Array("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.image_position.I.3", "bottom"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
        "dependancy" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.dependancy",
			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_kequestionnaire_dependancies",
                                "foreign_field" => "dependant_outcome",
				"maxitems" => 20,
				"behaviour" => Array(
                                    "localizationMode" => "select",
                                ),
                                "appearance" => Array(
                                    'collapseAll' => 1,
                                    'expandSingle' => 1,
                                    "showSynchronizationLink" => 1,
                                    "showAllLocalizationLink" => 1,
                                    "showPossibleLocalizationRecords" => 1,
                                    "showRemovedLocalizationRecords" => 1,
                                ),
			)
		),
		'dependancy_simple' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.dependancy_simple',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
			
		),
	),
	"types" => array (
		"0" => array("showitem" => "title;;;;2-2-2,type, value_start;;;;3-3-3, value_end, text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/], image, image_position, dependancy_simple, dependancy")
                //"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, value_start;;;;3-3-3, value_end, text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kequestionnaire/rte/], image")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_kequestionnaire_authcodes"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_authcodes"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,qpid,authcode,feuser"
	),
	"feInterface" => $TCA["tx_kequestionnaire_authcodes"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_kequestionnaire_authcodes',
				'foreign_table_where' => 'AND tx_kequestionnaire_authcodes.pid=###CURRENT_PID### AND tx_kequestionnaire_authcodes.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"qpid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_authcodes.qpid",
			"config" => Array (
				"type"     => "input",
				"size"     => "15",
				"max"      => "255",
				"checkbox" => "",
				"eval"     => "trim",
				"wizards"  => array(
					"_PADDING" => 2,
					"link"     => array(
						"type"         => "popup",
						"title"        => "Link",
						"icon"         => "link_popup.gif",
						"script"       => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					)
				)
			)
		),
		"authcode" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_authcodes.authcode",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
                "email" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_authcodes.email",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"feuser" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_authcodes.feuser",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "fe_users",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, qpid, authcode, email, feuser")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_kequestionnaire_results"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_results"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,auth,start,last,finished,data_2a5c43f84b,ip"
	),
	"feInterface" => $TCA["tx_kequestionnaire_results"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
                'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"auth" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_results.auth",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_authcodes",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"start_tstamp" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_results.start",
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"last_tstamp" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_results.last",
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"finished_tstamp" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_results.finished",
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"xmldata" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_results.data_2a5c43f84b",
			"config" => Array (
				"type" => "none",
			)
		),
		"ip" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_results.ip",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid, hidden;;1;;1-1-1, auth, start, last, finished, data_2a5c43f84b, ip")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);



$TCA["tx_kequestionnaire_history"] = array (
	"ctrl" => $TCA["tx_kequestionnaire_history"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,data_2e86c50d23,history_time,result_id"
	),
	"feInterface" => $TCA["tx_kequestionnaire_history"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
                'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"xmldata" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_history.data_2e86c50d23",
			"config" => Array (
				"type" => "none",
			)
		),
		"history_time" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_history.history_time",
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"result_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_history.result_id",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_kequestionnaire_results",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid, hidden;;1;;1-1-1, data_2e86c50d23, history_time, result_id")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);

if (t3lib_extMgm::isLoaded('ke_questionnaire_premium')){
    include_once(t3lib_extMgm::extPath('ke_questionnaire_premium').'tca_includes.php');
}
?>
