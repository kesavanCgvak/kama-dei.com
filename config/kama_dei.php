<?php

return [
	'static'=>[
	    'No_Persona'                     =>   17,
	    'KAMARONID'                      =>  109,//KAMARON USER ID :USER
		'RELATION_GROUP_TYPE_ID'         => 1768,//RELATION GROUP TYPE "TERM
		'RELATION_ASSOCIATION_ID'        => 1768,//RELATION ASSOCIATION :TERM
		'organization_association_ID'    => 1525, //organization association :TERM
		'can_access_protected_data_from' =>   95,//can access Protected data from :RELATION TYPE 
		'PUBLIC'                         =>    0,
		'PROTECTED'                      =>    1,
		'PRIVATE'                        =>    2,
		'nicknamelength'                 =>  100,
		'chatIntro_voiceIntro_maxlength' => 1000,
		'sredChatIntroLength'            => 1000,
		'soedChatIntroLength'            => 1000,
		"KaaSRootPageID"                 =>   49,

		'is_a_member_of_ID'              =>   61,//is a member of :RELATION TYPE
		'LINKING_TERM_ID'                =>  1279,//LINKING TERM :Term
		'KR_TERM_LINKS_ID'               => 52977,//KR-TERM LINKS :Term
		'TERM_TENSE'                     =>  1984,//TENSE :Term
	    'TERM_VALUES_ID'                 =>  2080,//VALUES :Term
	],
	"portals"=>[
		["number"=>'1', "caption"=>"Webpage"],
		["number"=>'2', "caption"=>"Lex"],
		["number"=>'3', "caption"=>"Test"],
		["number"=>'4', "caption"=>"Facebook"],
		["number"=>'z', "caption"=>"Alexa"],
	],
	"BotMessage"=>[
		"Type"=>[
			["value"=>'text', "caption"=>"Text"],
			["value"=>'number', "caption"=>"Number"],
			["value"=>'multiple-choice', "caption"=>"Multiple Choice"],
			["value"=>'etc', "caption"=>"etc"],
			["value"=>'yesno', "caption"=>"Yes / No"]
		],
		"Lang"=>[
			["value"=>'en', "caption"=>"English"],
//			["value"=>'fr', "caption"=>"FR"]
		]
	]
];
