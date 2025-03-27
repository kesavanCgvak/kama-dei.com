
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes React and other helpers. It's a great starting point while
 * building robust, powerful web applications using React + Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh React component instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

/*
require('./components/Term');
require('./components/RelationType');
require('./components/RelationLink');
require('./components/Relation');
require('./components/User');
require('./components/Security');
require('./components/AccessLevel');
require('./components/Organization');
require('./components/Chart');
*/

if($("#term").length != 0){
  require('./components/Term');
}

if($("#relation").length != 0){
  require('./components/Relation');
}

if($("#relation_type_synonym").length != 0){
  require('./components/RelationTypeSynonym');
}

if($("#relationType").length != 0){
  require('./components/RelationType');
}

if($("#relationLink").length != 0){
  require('./components/RelationLink');
}

if($("#users").length != 0){
  require('./components/User');
}

if($("#organization").length != 0){
  require('./components/Organization');
}

if($("#accesslevel").length != 0){
  require('./components/AccessLevel');
}

if($("#security").length != 0){
  require('./components/Security');
}

if($("#personality").length != 0){
  require('./components/Personality');
}

if($("#persona").length != 0){
  require('./components/Persona');
}

if($("#personalityValues").length != 0){
  require('./components/PersonalityValue');
}

if($("#personalityRelationValue").length != 0){
  require('./components/PersonalityRelationValue');
}

require('./components/Chart');
  if($("#attributetype").length != 0){
    require('./components/extend/AttributeType');
  }

if($("#attributetype").length != 0){
	require('./components/extend/AttributeType');
}
  if($("#extendedtype").length != 0){
    require('./components/extend/ExtendedType');
  }

  if($("#extendedsubtype").length != 0){
    require('./components/extend/ExtendedSubType');
  }

  if($("#extendedattribute").length != 0){
    require('./components/extend/Extendedattribute');
  }

  if($("#extendedentity").length != 0){
    require('./components/extend/ExtendedEntity');
  }

  if($("#extendedeav").length != 0){
    require('./components/extend/ExtendedEAV');
  }

  if($("#extendedlink").length != 0){
    require('./components/extend/ExtendedLink');
  }
  if($("#chatbotlog").length != 0){
    require('./components/extend/Chatbotlog');
  }

if($("#orgRelationType").length != 0){
	require('./components/org_relation_type');
}

if($("#organizationAssociation").length != 0){
	require('./components/organization_association');
}

if($("#relationGroupType").length != 0){
	require('./components/RelationGroupType');
}

if($("#extendedeav").length != 0){
	require('./components/extend/ExtendedEAV');
}

if($("#relationTypeGroup").length != 0){
  require('./components/RelationTypeGroup');
}

if($("#relationGroupClassification").length != 0){
  require('./components/RelationGroupClassification');
}

if($("#dataClassification").length != 0){
  require('./components/DataClassification');
}


if($("#LEX_Setting").length != 0){
  require('./components/lex/Setting');
}
if($("#LEX_Mapping").length != 0){
  require('./components/lex/mapping');
}

if($("#LEX_MappingNewEdit").length != 0){
  require('./components/lex/mapping.new.edit');
}

if($("#portal").length != 0){
  require('./components/Portal');
}

if($("#botMessage").length != 0){
  require('./components/BotMessage');
}

if($("#kamalog").length != 0){
  require('./components/logs/kamalog');
}

if($("#billing").length != 0){
  require('./components/Billing');
}

if($("#KAAS_Setting").length != 0){
  require('./components/kaas/Setting');
}
if($("#KAAS_Mapping").length != 0){
  require('./components/kaas/mapping');
}
if($("#KAAS_MappingNewEdit").length != 0){
  require('./components/kaas/mapping.new.edit');
}

if($("#link_kr_to_term").length != 0){
  require('./components/LinkKrToTerm');
}

if($("#LIVEAGENT_Setting").length != 0){
  require('./components/live_agent/Setting');
}
if($("#LIVEAGENT_Mapping").length != 0){
  require('./components/live_agent/mapping');
}
if($("#LIVEAGENT_MappingNewEdit").length != 0){
  require('./components/live_agent/mapping.new.edit');
}

if($("#org_emails_config").length != 0){
  require('./components/org.emails.config');
}

if($("#rpa_types").length != 0){
  require('./components/rpa/types');
}

if($("#RPA_Mapping").length != 0){
  require('./components/rpa/mapping');
}

if($("#RPA_MappingNewEdit").length != 0){
  require('./components/rpa/mapping.new.edit');
}

if($("#feedbackDIV").length != 0){
  require('./components/Feedback');
}
