<?php
namespace App\Helpers;
//--------------------------------------------------------------------------
//--------------------------------------------------------------------------
class Extended{
	public $extendedValue=[
		'entity'=>null,
		'total' =>0,
		'data'=>[]
	];		
	//----------------------------------------------------------------------
	public function load($entity,$maxLen=100){
		//------------------------------------------------------------------
		$entity = trim($entity);
		if($entity==''){ return $this->extendedValue; }
		//------------------------------------------------------------------
		$this->extendedValue['entity']=$entity;
		//------------------------------------------------------------------
		$extendedEntity    = new \App\Models\ExtendedEntity();
		$extendedSubType   = new \App\Models\ExtendedSubType();
		$extendedAttribute = new \App\Models\ExtendedAttribute();
		$extendedEAV       = new \App\Models\ExtendedEAV();
		//------------------------------------------------------------------
		$extendedEntityValues = $extendedEntity
									->where('extendedEntityName', 'like', "%{$entity}%")
									->orderBy('extendedEntityId', 'asc')
									->forPage(1,$maxLen)
									->get();
		//------------------------------------------------------------------
		if($extendedEntityValues!=null){
			foreach($extendedEntityValues as $tmpEntity){
				//----------------------------------------------------------
				$Entity = [];
				$Entity['EntityId'        ] = $tmpEntity->extendedEntityId;
				$Entity['EntityName'      ] = $tmpEntity->extendedEntityName;
				$Entity['EntityMemo'      ] = $tmpEntity->memo;
				$Entity['SubTypeId'       ] = null;
				$Entity['SubTypeName'     ] = null;
				$Entity['SubTypeChatIntro'] = null;
				$Entity['SubTypeMemo'     ] = null;
				$Entity['Attributes'      ] = [];
				$Entity['EAV'             ] = [];
				//----------------------------------------------------------
				$extendedSubTypeValue = $extendedSubType
									->where('extendedSubTypeId', $tmpEntity->extendedSubTypeId)
									->first();
				if($extendedSubTypeValue!=null){
					//------------------------------------------------------
					$Entity['SubTypeId'       ] = $extendedSubTypeValue->extendedSubTypeId;
					$Entity['SubTypeName'     ] = $extendedSubTypeValue->extendedSubTypeName;
					$Entity['SubTypeChatIntro'] = $extendedSubTypeValue->chatIntro;
					$Entity['SubTypeMemo'     ] = $extendedSubTypeValue->memo;
					//------------------------------------------------------
				}
				//----------------------------------------------------------
				$extendedAttributeValues = $extendedAttribute
									->leftJoin('extended_attribute_type as e_a_t', 'extended_attribute.attributeTypeId', '=', 'e_a_t.attributeTypeId')
									->where('extendedSubTypeId', $tmpEntity->extendedSubTypeId)
									->orderBy('attributeId', 'asc')
									->select(
										"extended_attribute.*",
										"attributeTypeName",
										"storageType",
										"e_a_t.memo as attributeTypeMemo"
									)
									->get();
				if($extendedAttributeValues!=null){
					foreach($extendedAttributeValues as $tmpAttribute){
						//--------------------------------------------------
						$Attribute = [];
						$Attribute['AttributeId'      ] = $tmpAttribute->attributeId;
						$Attribute['AttributeName'    ] = $tmpAttribute->attributeName;
						$Attribute['DisplayName'      ] = $tmpAttribute->displayName;
						$Attribute['DefaultValue'     ] = $tmpAttribute->defaultValue;
						$Attribute['NotNullFlag'      ] = $tmpAttribute->notNullFlag;
						$Attribute['AttributeMemo'    ] = $tmpAttribute->memo;
						$Attribute['AttributeTypeName'] = $tmpAttribute->attributeTypeName;
						$Attribute['StorageType'      ] = $tmpAttribute->storageType;
						$Attribute['AttributeTypeMemo'] = $tmpAttribute->attributeTypeMemo;
						//--------------------------------------------------
						$Entity['Attributes'][] = $Attribute;
						//--------------------------------------------------
					}
				}
				//----------------------------------------------------------
				$extendedEAVValues = $extendedEAV
									->leftJoin('extended_attribute as e_a', 'extended_eav.extendedAttributeId', '=', 'e_a.attributeId')
									->leftJoin('extended_attribute_type as e_a_t', 'e_a.attributeTypeId', '=', 'e_a_t.attributeTypeId')
									->where('extendedEntityId', $tmpEntity->extendedEntityId)
									->orderBy('extendedEAVID', 'asc')
									->select(
										"extended_eav.*",
										"attributeTypeName",
										"storageType",
										"e_a_t.memo as attributeTypeMemo"
									)
									->get();
				if($extendedEAVValues!=null){
					foreach($extendedEAVValues as $tmpEAV){
						//--------------------------------------------------
						$EAV = [];
						$EAV['EAVId'            ] = $tmpEAV->extendedEAVID;
						$EAV['ValueString'      ] = $tmpEAV->valueString;
						$EAV['ValueBlob'        ] = $tmpEAV->valueBlob;
						$EAV['ValueFloat'       ] = $tmpEAV->valueFloat;
						$EAV['ValueDate'        ] = $tmpEAV->valueDate;
						$EAV['EAVMemo'          ] = $tmpEAV->memo;
						$EAV['AttributeTypeName'] = $tmpEAV->attributeTypeName;
						$EAV['StorageType'      ] = $tmpEAV->storageType;
						$EAV['AttributeTypeMemo'] = $tmpEAV->attributeTypeMemo;
						//--------------------------------------------------
						$Entity['EAV'][] = $EAV;
						//--------------------------------------------------
					}
				}
				//----------------------------------------------------------
				$this->extendedValue['data'][] = $Entity;
				$this->extendedValue['total']++;
				//----------------------------------------------------------
			}
		}
		//------------------------------------------------------------------
		return $this->extendedValue;
	}
	//----------------------------------------------------------------------
}
//--------------------------------------------------------------------------
//--------------------------------------------------------------------------
