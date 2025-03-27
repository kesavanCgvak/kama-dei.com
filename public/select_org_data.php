<?php
try{
	//-------------------------------------------------------------------------------
	if(!isset($_GET['org_id'])){ throw new \Exception("Organization ID not found"); }
	$org_id = $_GET['org_id'];
	//-------------------------------------------------------------------------------
	$username = "root";
	$password = "a39878!9sAss3h";
	//-------------------------------------------------------------------------------
	$connEP = mysqli_connect( 'localhost', $username, $password, "kamadeiep" );
	if($connEP->connect_error){ throw new \Exception($connEP->connect_error); }
	mysqli_query($connEP, "SET NAMES 'utf8'");
	//-------------------------------------------------------------------------------
	$connKB = mysqli_connect( 'localhost', $username, $password, "kamadeikb" );
	if($connKB->connect_error){ throw new \Exception($connKB->connect_error); }
	mysqli_query($connKB, "SET NAMES 'utf8'");
	//-------------------------------------------------------------------------------
	$result = mysqli_query( $connEP, "select * from organization_ep where organizationId='{$org_id}';" );
	if($result===false){ throw new \Exception(mysqli_error( $connEP )); }
	if( mysqli_affected_rows( $connEP )==0 ){ throw new \Exception("Organization not found: organizationId [{$org_id}]"); }
	$orgData = mysqli_fetch_assoc( $result );
	//-------------------------------------------------------------------------------
	$conn__ = mysqli_connect( 'localhost', $username, $password );
	if($conn__->connect_error){ throw new \Exception($conn__->connect_error); }
	mysqli_query($conn__, "SET NAMES 'utf8'");

	$dbEP = "kamadeiep_{$org_id}";
	$result = mysqli_query( $conn__, "drop DATABASE IF EXISTS {$dbEP};" );
	if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
	$result = mysqli_query( $conn__, "CREATE DATABASE {$dbEP};" );
	if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }

	
	$dbKB = "kamadeikb_{$org_id}";
	$result = mysqli_query( $conn__, "drop DATABASE IF EXISTS {$dbKB};" );
	if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
	$result = mysqli_query( $conn__, "CREATE DATABASE {$dbKB};" );
	if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }

	$conn_KB = mysqli_connect( 'localhost', $username, $password, $dbKB );
	if($conn_KB->connect_error){ throw new \Exception($conn_KB->connect_error); }
	mysqli_query($conn_KB, "SET NAMES 'utf8'");

	$result = mysqli_query( $conn__, "RESET MASTER;" );
	if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
	//-------------------------------------------------------------------------------
	//-------------------------------------------------------------------------------
	//-------------------------------------------------------------------------------
	$kamadeiep = [
		["api_key_manager"      , 0, 0, ''],
		["autocomplete"         , 1, 0, ''],
		["botMessage"           , 1, 0, 'OrgId'],
		["client"               , 1, 0, ''],
		["data_classification"  , 1, 0, 'organizationId'],
		["language"             , 1, 0, ''],
		["level"                , 1, 0, ''],
		["organization_ep"      , 1, 0, 'organizationId'],
		["organization_language", 1, 0, 'org_id'],
		["page_client_level"    , 1, 0, ''],
		["page_level"           , 1, 0, ''],
		["portal"               , 1, 0, 'organization_id'],
		["portalType"           , 1, 0, ''],
		["site_page"            , 1, 0, ''],
		["tier"                 , 1, 0, 'orgID'],
		["user"                 , 1, 0, 'orgID'],
		["relation_link_with_linkTypeName", 1, 0, 'ownerId'],
		["logEmailsConfig"      , 1, 0, '']
	];
	foreach($kamadeiep as $indx=>$tbl){
		$newTBL = "{$dbEP}.{$tbl[0]}";
		$oldTBL = "kamadeiep.{$tbl[0]}";
		//---------------------------------------------------------------------------
		try{
			//-----------------------------------------------------------------------
			$result = mysqli_query( $conn__, "CREATE TABLE {$newTBL} LIKE {$oldTBL};" );
			if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
			//-----------------------------------------------------------------------
			if($tbl[1]==1){
				if($tbl[3]!=""){
					$result = mysqli_query( $conn__, "insert into {$newTBL} select * from {$oldTBL} where ({$oldTBL}.{$tbl[3]} is null ) or ({$oldTBL}.{$tbl[3]} in (0, {$org_id}));" );
					if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
				}else{
					$result = mysqli_query( $conn__, "insert into {$newTBL} select * from {$oldTBL};" );
					if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
				}
			}

			if($tbl[0]=="logEmailsConfig"){
				$result = mysqli_query( $conn__, "delete FROM {$newTBL} WHERE portal_id not in(select id from {$dbEP}.portal);" );
				if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
			}
			
			if($tbl[0]=="user"){
				$result = mysqli_query( $conn__, "delete FROM {$newTBL} WHERE levelID=4;" );
				if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
			}
			//-----------------------------------------------------------------------
			$kamadeiep[$indx][2] = "OK";
			//-----------------------------------------------------------------------
		}catch(\Throwable $e){ $kamadeiep[$indx][2] = $e->getMessage(); }
	}
	//-------------------------------------------------------------------------------
	//-------------------------------------------------------------------------------
	$kamadeikb = [
		["consumer_user"                , 0, 0, ''],
		["consumer_user_personality"    , 0, 0, ''],
		["consumer_user_personality_log", 0, 0, ''],
		["extended_subtype"             , 1, 0, 'ownerId'],
		["extended_attribute"           , 1, 0, 'ownerId'],
		["extended_attribute_type"      , 1, 0, 'ownerId'],
		["extended_eav"                 , 1, 0, 'ownerId'],
		["extended_entity"              , 1, 0, 'ownerId'],
		["draft"                        , 1, 0, ''],
		["extended_link"                , 1, 0, 'ownerId'],
		["extended_link_translation"    , 1, 0, ''],
		["extended_type"                , 1, 0, 'ownerId'],
		["message"                      , 1, 0, 'orgId'],
		["organization"                 , 0, 0, ''],
		["organization_association"     , 1, 0, ''],
		["organization_personality"     , 1, 0, 'organizationId'],
		["person"                       , 0, 0, ''],
		["personality"                  , 1, 0, 'ownerId'],
		["personality_relation"         , 1, 0, 'ownerId'],
		["personality_relation_value"   , 1, 0, 'ownerId'],
		["personality_trait"            , 1, 0, 'ownerId'],
		["personality_value"            , 1, 0, 'ownerId'],
		["relation"                     , 1, 0, 'ownerId'],
		["relation_group_classification", 1, 0, ''],
		["relation_language"            , 1, 0, 'orgId'],
		["relation_link"                , 1, 0, 'ownerId'],
		["relation_term_link"           , 1, 0, 'ownerId'],
		["relation_translation"         , 1, 0, 'orgId'],
		["relation_type"                , 1, 0, 'ownerId'],
		["relation_type_classification" , 1, 0, 'ownerId'],
		["relation_type_filter"         , 1, 0, ''],
		["relation_type_group"          , 1, 0, 'ownerId'],
		["relation_type_synonym"        , 1, 0, 'ownerId'],
		["responsiblity"                , 1, 0, ''],
		["solution_fact"                , 0, 0, ''],//*
		["solution_factexdata"          , 0, 0, ''],//*
		["solution_option"              , 0, 0, ''],//*
		["solution_optionexdata"        , 0, 0, ''],//*
		["solution_relation"            , 0, 0, ''],//*
		["solution_relationexdata"      , 0, 0, ''],//*
		["term"                         , 1, 0, 'ownerId'],
		["term_type"                    , 1, 0, ''],
		["extended_data_new"            , 2, 0, '']
	];
	
	foreach($kamadeikb as $indx=>$tbl){
		$newTBL = "{$dbKB}.{$tbl[0]}";
		$oldTBL = "kamadeikb.{$tbl[0]}";
		//---------------------------------------------------------------------------
		try{
			//-----------------------------------------------------------------------
			if($tbl[1]!=2){
				$result = mysqli_query( $conn__, "CREATE TABLE {$newTBL} LIKE {$oldTBL};" );
				if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
			}
			//-----------------------------------------------------------------------
			if($tbl[1]==1){
				if($tbl[3]!=""){
					$result = mysqli_query( $conn__, "insert into {$newTBL} select * from {$oldTBL} where ({$oldTBL}.{$tbl[3]} is null ) or ({$oldTBL}.{$tbl[3]} in (0, {$org_id}));" );
					if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
				}else{
					$result = mysqli_query( $conn__, "insert into {$newTBL} select * from {$oldTBL};" );
					if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
				}
			}
/*
			if($tbl[0]=="draft"){
				$result = mysqli_query( $conn__, "delete FROM {$newTBL} WHERE extendedEntityId not in(select extendedEntityId from {$dbKB}.extended_entity);" );
				if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
			}
*/		
			if($tbl[0]=='extended_data_new'){
				$result = mysqli_query( $conn_KB, "CREATE VIEW {$newTBL} AS select `b`.`lang` AS `lang`,`a`.`extendedSubTypeId` AS `extendedSubTypeId`,`e`.`extendedSubTypeName` AS `extendedSubTypeName`,`a`.`attributeId` AS `attributeId`,`a`.`attributeName` AS `attributeName`,`a`.`displayName` AS `displayName`,`a`.`defaultValue` AS `defaultValue`,`a`.`notNullFlag` AS `notNullFlag`,`a`.`memo` AS `attributeMemo`,`b`.`extendedEAVID` AS `extendedEAVID`,`b`.`valueString` AS `valueString`,`b`.`memo` AS `memo`,`d`.`extendedEntityId` AS `extendedEntityId`,`d`.`extendedEntityName` AS `extendedEntityName`,`b`.`ownerId` AS `ownerId`,`b`.`ownership` AS `ownership`,`a`.`attributeTypeId` AS `attributeTypeId`,`c`.`attributeTypeName` AS `attributeTypeName`,`c`.`storageType` AS `storageType` from ((((`extended_entity` `d` left join `extended_subtype` `e` on((`d`.`extendedSubTypeId` = `e`.`extendedSubTypeId`))) left join `extended_attribute` `a` on((`a`.`extendedSubTypeId` = `e`.`extendedSubTypeId`))) left join `extended_attribute_type` `c` on((`a`.`attributeTypeId` = `c`.`attributeTypeId`))) left join `extended_eav` `b` on(((`a`.`attributeId` = `b`.`extendedAttributeId`) and (`d`.`extendedEntityId` = `b`.`extendedEntityId`))));" );
				if($result===false){ throw new \Exception(mysqli_error( $conn__ )); }
			}
			//-----------------------------------------------------------------------
			$kamadeikb[$indx][2] = "OK";
			//-----------------------------------------------------------------------
		}catch(\Throwable $e){ $kamadeikb[$indx][2] = $e->getMessage(); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "relation");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from relation where relationTypeId not in (select relationTypeId from relation_type)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from relation where rightTermId not in (select termId from term)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			else{
				$result = mysqli_query( $conn_KB, "delete from relation where leftTermId not in (select termId from term)");
				if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			}
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "relation_language");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from relation_language where relationId not in (select relationId from relation)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "relation_link");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from relation_link where rightRelationId not in (select relationId from relation)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from relation_link where leftRelationId not in (select relationId from relation)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			else{
				$result = mysqli_query( $conn_KB, "delete from relation_link where linkTermId not in (select termId from term)");
				if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			}
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "relation_term_link");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from relation_term_link where relationId not in (select relationId from relation)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from relation_term_link where termId not in (select termId from term)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "relation_translation");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from relation_translation where relationId not in (select relationId from relation)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "relation_type_filter");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from relation_type_filter where relationTypeId not in (select relationTypeId from relation_type)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "relation_type_group");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from relation_type_group where relationAssociationTermId not in (select termId from term)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from relation_type_group where relationTypeId not in (select relationTypeId from relation_type)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "relation_type_synonym");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from relation_type_synonym where rtSynonymRelationTypeId not in (select relationTypeId from relation_type)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from relation_type_synonym where rtSynonymTenseId not in (select termId from term)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			else{
				$result = mysqli_query( $conn_KB, "delete from relation_type_synonym where rtSynonymTermId not in (select termId from term)");
				if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			}
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "extended_type");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from extended_type where termId not in (select termId from term)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "extended_subtype");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from extended_subtype where extendedTypeId not in (select extendedTypeId from extended_type)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "extended_entity");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from extended_entity where extendedSubTypeId not in (select extendedSubTypeId from extended_subtype)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "responsiblity");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from responsiblity where userid not in (select id from {$dbEP}.user)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from responsiblity where extendedEntityId not in (select extendedEntityId from extended_entity)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "extended_attribute");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from extended_attribute where extendedSubTypeId not in (select extendedSubTypeId from extended_subtype)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from extended_attribute where attributeTypeId not in (select attributeTypeId from extended_attribute_type)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "draft");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from draft where extendedEntityId not in (select extendedEntityId from extended_entity)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "extended_eav");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from extended_eav where extendedEntityId not in (select extendedEntityId from extended_entity)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from extended_eav where extendedAttributeId not in (select attributeId from extended_attribute)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "extended_link");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from extended_link where entityId not in (select extendedEntityId from extended_entity)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from extended_link where parentId not in (select relationId from relation) and parentTable=2");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			else{
				$result = mysqli_query( $conn_KB, "delete from extended_link where parentId not in (select relationLinkId from relation_link) and parentTable=1");
				if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
				else{
					$result = mysqli_query( $conn_KB, "delete from extended_link where parentId not in (select termId from term) and parentTable=0");
					if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
				}
			}
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "extended_link_translation");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from extended_link_translation where extendedLinkId not in (select extendedLinkId from extended_link)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "organization_association");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from organization_association where relationTypeGroupId not in (select relationTypeGroupId from relation_type_group)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from organization_association where (leftOrgId is not null) and (leftOrgId not in (select organizationId from {$dbEP}.organization_ep))");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			else{
				$result = mysqli_query( $conn_KB, "delete from organization_association where rightOrgId not in (select organizationId from {$dbEP}.organization_ep)");
				if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
			}
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "personality");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from personality where parentPersonaId not in (select personalityId from (select * from personality) a where a.parentPersonaId=0) and parentPersonaId>0");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "personality_relation");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from personality_relation where personalityId not in (select personalityId from personality)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from personality_relation where relationId not in (select relationId from relation)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "personality_relation_value");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from personality_relation_value where personalityRelationId not in (select relationId from relation)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from personality_relation_value where personRelationTermId not in (select termId from term)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "personality_trait");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from personality_trait where personalityId not in (select personalityId from personality)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from personality_trait where termTraitId not in (select termId from term)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "personality_value");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from personality_value where personalityId not in (select personalityId from personality)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from personality_value where personTermId not in (select termId from term)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	$indx = findTable($kamadeikb, "organization_personality");
	if($indx!=-1 && $kamadeikb[$indx][2]=='OK'){
		$result = mysqli_query( $conn_KB, "delete from organization_personality where organizationId not in (select organizationId from {$dbEP}.organization_ep)");
		if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		else{
			$result = mysqli_query( $conn_KB, "delete from organization_personality where personalityId not in (select personalityId from personality)");
			if($result===false){ $kamadeikb[$indx][2] = mysqli_error( $conn_KB ); }
		}
	}
	//-------------------------------------------------------------------------------
	//TRUNCATE TABLE `api_key_manager`;
	//-------------------------------------------------------------------------------
	?>
	<html>
		<head>
			<title>Organization: <?=$orgData['organizationShortName'];?></title>
			<style>
				ul{ height: 200px; overflow: auto; margin-top: -10px; }
				h1,h2,h3,h4,h5,h6{ border-bottom: 1px solid #aaa; width: fit-content; }
			</style>
		</head>
		<body style="background: #c0bbbb94; color: black">
			<h2><?=$orgData['organizationShortName'];?></h2>
			<h4><?=$dbEP;?> created</h4>
			<ul>
			<?php
			foreach($kamadeiep as $indx=>$tbl){
				?><li><label style="display:inline-block; width:250px"><?=$tbl[0];?></label>: <b><?=$tbl[2];?></b></li><?php
			}
			?>
			</ul>
			<br/>
			<h4><?=$dbKB;?> created</h4>
			<ul>
			<?php
			foreach($kamadeikb as $indx=>$tbl){
				?><li><label style="display:inline-block; width:250px"><?=$tbl[0];?></label>: <b><?=$tbl[2];?></b></li><?php
			}
			?>
			</ul>
		</body>
	</html>
	<?php
}catch(\Throwable $ex){
?>
<html>
	<head>
		<title>Error: </title>
	</head>
	<body style="background: #3a2626">
		<h3 style="color: #F8060A"><?=$ex->getMessage();?></h3>
	</body>
</html>
<?php
}

function findTable($tables, $table){
	foreach($tables as $indx=>$tbl){ if($tbl[0]==$table){ return $indx; }}
	return -1;
}
?>
