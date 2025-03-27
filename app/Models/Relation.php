<?php
/*--------------------------------------------------------------------------------
 *  File          : Ralation.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating relation table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation. KDC
 *  Version       : 3.0
 *  Updated       : 12 December 2023
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation';
	protected $primaryKey = "relationId";
	protected $modifiers  = ['relationId', 'leftTermId', 'relationTypeId', 'rightTermId','shortTextOLD',
	                         'relationTypeOperand','relationIsReserved', 'ownership', 'ownerId', 
                             'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('relationId', '=', $id)->get(); 
     }

	
	//--------------------------------------------------------------------
	public function retrieveByLeftTypeRightId($leftId, $typeId, $rightId)
     {
        $relationId = 0;
		$rs =   $this->where('leftTermId', '=', $leftId)
                     ->where('relationTypeId', '=', $typeId)
                     ->where('rightTermId', '=', $rightId)
                     ->get(); 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){						  
                $relationId = $rs0->relationId;
            }			
        }
        return $relationId;
	 }


    //---------------------------------------------------------------------
    public function  seekRelationName($id, $leftCustom=""){


        $relationName = "";

        $rs = Relation::select('leftTerm.termName as leftTermName',
                           'rightTerm.termName as rightTermName',
                           'relation_type.relationTypeName as relationTypeName')
                    ->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
                    ->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
                    ->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
                    ->where('relation.relationId', '=', $id )
                    ->get();

        foreach ($rs as $row) {

            $leftName   = $row->leftTermName;
            $rightName  = $row->rightTermName;
            $rtName     = $row->relationTypeName;

            if ($leftName == "person" ) {
               $relationName = $leftCustom. " ". $rtName. " ". $rightName;                 
            } else {
               $relationName = $leftName. " ". $rtName. " ". $rightName;            
            }


        }

        return $relationName;
    }  



    //--------------------------------------------------------------------
    public function retrieveShortText($relationId)
     {
        $shortText = "";
        $rs =   $this->where('relationId', '=', $relationId)
                     ->whereNotNull('shortTextOLD')
                     ->get(); 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){                        
                $shortText = $rs0->shortTextOLD;
            }           
        }
        return $shortText;
     }

    //--------------------------------------------------------------------
    public function retrieveRelationTypeId($relationId)
     {
        $relTypeId = 0;
        $rs =   $this->where('relationId', '=', $relationId)
                     ->get(); 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){                        
                $relTypeId = $rs0->relationTypeId;
            }           
        }
        return $relTypeId;
     }
 
	//--------------------------------------------------------------------
	public function getByLeftTermRelationType($termId, $typeId, $limit = 99999999)
     {

        return   $this->where('leftTermId', '=', $termId)
                      ->where('relationTypeId', '=', $typeId)
                      ->skip(0)
                      ->take($limit)
                      ->get();
	 }	

    //--------------------------------------------------------------------
    public function getRightTermRelationType($termId, $typeId, $limit = 99999999)
     {
        return   $this->where('rightTermId', '=', $termId)
                      ->where('relationTypeId', '=', $typeId)
                      ->skip(0)
                      ->take($limit)
                      ->get();
     }

    //----------------------------------------------------------------------
    public function getByLeftRight($orgid,$leftId,$rightId, $limit = 99999999) 
    {

       return $this->where('leftTermId','=', '$leftId')
                   ->where('rightTermId', '=', '$roghtId')
                   ->skip(0)
                   ->take($limit)
                   ->get();
    }

    //----------------------------------------------------------------------
    public function getRKR($orgid,$termId, $rtId, $limit = 99999999) 
    {

       return $this->where('rightTermId','=', '$termId')
                   ->skip(0)
                   ->take($limit)
                   ->get();
    }

    //---------------------------------------------------------------------
    public function retrieveRKR($orgid, $termId, $rtId, $limit = 99999999)
    {
       $krId = 0;
       $rs = $this->where('rightTermId','=','$termId')
                  ->skip(0)
                  ->take($limit)
                  ->get();

        if (!empty($rs)) {               
            foreach ($rs as $rs0){                        
                $krId = $rs0->relationId;
            }           
        }

       return $krId ;
    }


    //--------------------------------------------------------------------
    public function retrieveRTermId($relationId)
     {
        $Id = 0;
        $rs =   $this->where('relationId', '=', $relationId)
                     ->get(); 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){                        
                $Id = $rs0->rightTermId;
            }           
        }
        return $Id;
     }

    //--------------------------------------------------------------------
    public function getLeftRight($leftId,$rightId, $limit = 99999999)
     {
        return $this->where('leftTermId','=', '$leftId')
                    ->where('rightTermId', '=', '$rightId')
                    ->skip(0)
                    ->take($limit)
                    ->get();
     }        

    //--------------------------------------------------------------------
    public function getByTermRelationType($termId, $typeId)
     {

        return  $this->where( function($query) use($termId, $typeId) {
                       $query->where('relationTypeId', '=', $typeId )
                             ->where('leftTermId', '=', $termId);                           
                 })->orWhere( function($query) use($termId, $typeId)  {
                       $query->where('relationTypeId', '=', $typeId )
                             ->where('rightTermId', '=', $termId) ;
                     })
                    ->get(); 
     }  



    //--------------------------------------------------------------------
    public function getOrgLeftRelationType($orgid,$leftTermId,$relationTypeId,$rtgId, $limit = 9999999)
     {


          return $this->where( function($query) use($orgid,$leftTermId,$relationTypeId,$limit)  {
                       $query->where( 'ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgid)
                             ->where('leftTermId', '=', $leftTermId)
                             ->where('relationTypeId', '=', $relationTypeId)
                            ->skip(0)
                            ->take($limit); 
                     })->orWhere( function($query) use($orgid,$leftTermId,$relationTypeId,$limit)  {
                       $query->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgid)           // same orgid
                             ->where('leftTermId', '=', $leftTermId)
                             ->where('relationTypeId', '=', $relationTypeId) 
                            ->skip(0)
                            ->take($limit); 
                     })->orWhere( function($query) use($orgid,$leftTermId,$relationTypeId,$rtgId,$limit)  {
                       $query->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.rightOrgId','=','relation.ownerId')
                             ->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.leftOrgId','=',$orgid)
                             ->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '<>', $orgid)          // distinct orgid
                             ->where('leftTermId', '=', $leftTermId)
                             ->where('relationTypeId', '=', $relationTypeId) 
                            ->skip(0)
                            ->take($limit);  
                     })->orWhere( function($query) use($leftTermId,$relationTypeId,$limit)  {
                       $query->where( 'ownership', '=', 0  )            // public
                             ->where('leftTermId', '=', $leftTermId)
                             ->where('relationTypeId', '=', $relationTypeId) 
                            ->skip(0)
                            ->take($limit);  
                     })
                    ->get();                   

     }  

    //--------------------------------------------------------------------
    public function getOrgRightRelationType($orgid,$relationTypeId,$rightTermId,$rtgId, $limit = 9999999)
     {


          return $this->where( function($query) use($orgid,$rightTermId,$relationTypeId,$limit)  {
                       $query->where( 'ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgid)
                             ->where('rightTermId', '=', $rightTermId)
                             ->where('relationTypeId', '=', $relationTypeId)
                            ->skip(0)
                            ->take($limit); 
                     })->orWhere( function($query) use($orgid,$rightTermId,$relationTypeId,$limit)  {
                       $query->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgid)           // same orgid
                             ->where('rightTermId', '=', $rightTermId)
                             ->where('relationTypeId', '=', $relationTypeId) 
                            ->skip(0)
                            ->take($limit); 
                     })->orWhere( function($query) use($orgid,$rightTermId,$relationTypeId,$rtgId,$limit)  {
                       $query->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.rightOrgId','=','relation.ownerId')
                             ->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.leftOrgId','=',$orgid)
                             ->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '<>', $orgid)          // distinct orgid
                             ->where('rightTermId', '=', $rightTermId)
                             ->where('relationTypeId', '=', $relationTypeId) 
                            ->skip(0)
                            ->take($limit);  
                     })->orWhere( function($query) use($rightTermId,$relationTypeId,$limit)  {
                       $query->where( 'ownership', '=', 0  )            // public
                             ->where('rightTermId', '=', $rightTermId)
                             ->where('relationTypeId', '=', $relationTypeId) 
                            ->skip(0)
                            ->take($limit);  
                     })
                    ->get();                   

     }      






	//--------------------------------------------------------------------
	public function findByLeftTypeRightId($leftId, $typeId, $rightId)
     {
		return $this->where('leftTermId', '=', $leftId)
                     ->where('relationTypeId', '=', $typeId)
                     ->where('rightTermId', '=', $rightId)
                     ->get(); 
	 }	 

    //--------------------------------------------------------------------
    public function getByLeftTerm($orgid, $leftId)
     {
        return $this->where('leftTermId', '=', $leftId)
                     ->get(); 
     }  
	 

    //--------------------------------------------------------------------
     /*
                       $query->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.rightOrgId','=','extended_eav.ownerId')
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.leftOrgId','=',$orgId)
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where('ownership', '=', 1 )            // protected
                             ->where('ownerId', '<>', $orgId)          // distinct orgid
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('rightTermId', '=', $RTAid);


DB::table('users')
            ->join('contacts', 'users.id', '=', 'contacts.user_id')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.id', 'contacts.phone', 'orders.price')
            ->get();

  */

    public function joinLeftTypeRightId($termId, $typeId)
     {
        return $this->leftJoin('term','term.termId','=','relation.leftTermId')
                    ->where('relation.leftTermId', '=', $termId)
                    ->where('relation.relationTypeId', '=', $typeId)
                    ->get();
     }

	//--------------------------------------------------------------------
	public function getByRelTypeRightTerm($relationTypeId,$rightTermId)
     {
		return $this->where('relationTypeId', '=', $relationTypeId)
                    ->where('rightTermId', '=', $rightTermId)
                    ->get(); 
     }
	 
	//--------------------------------------------------------------------
	public function getByRightTerm($rightTermId)
     {
		return $this->where('rightTermId', '=', $rightTermId)
                    ->get(); 
     }

    //--------------------------------------------------------------------
    public function retrieveByRightTerm($rightTermId)
     {
        $relationId = 0;
        $rs = $this->where('rightTermId', '=', $rightTermId)
                    ->get(); 
         if (!empty($rs)) {               
            foreach ($rs as $rs0){                        
                $relationId = $rs0->relationId;
            }           
        }
        return $relationId;           
     }     

    //--------------------------------------------------------------------
    public function findByLeftTermRelationType($termId, $typeId)
     {
        return   $this->where('leftTermId', '=', $termId)
                      ->where('relationTypeId', '=', $typeId)
                      ->get();
     }  

    //--------------------------------------------------------------------
    public function findByRightTermRelationType($termId, $typeId)
     {
        return   $this->where('rightTermId', '=', $termId)
                      ->where('relationTypeId', '=', $typeId)
                      ->get();

     }
	 
	//--------------------------------------------------------------------
	public function retrieveSynonymRelationId($canbesynonymtoRTId, $sfRightTermId)
     {
        $synonymRelationId = 0;
        $rs =  $this->where('relationTypeId', '=', $canbesynonymtoRTId)
			        ->where('rightTermId', '=', $sfRightTermId )	
                    ->get(); 
					 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){						  
               $synonymRelationId = $rs0->relationId;
            }			
        }
		return $synonymRelationId;
     }

   //--------------------------------------------------------------------
    // get synonym for right term. Use bidireccional search (
    // Right hand and left hand logic )
    public function getRTSynonym($RTAid, $canbesynonymtoRTId)
     {
        $aSyn = array();
        $synCount = 0;

        // right hand logic
        $rs =  $this->where('relationTypeId', '=', $canbesynonymtoRTId)
                    ->where('rightTermId', '=', $RTAid )    
                    ->get();          
        foreach ($rs as $rs0){                        
            $aSyn[] = $rs0->leftTermId;
            $synCount++;
        } 

        // left hand logic
 
        $rs =  $this->where('relationTypeId', '=', $canbesynonymtoRTId)
                        ->where('leftTermId', '=', $RTAid )    
                        ->get();          
        foreach ($rs as $rs0){                        
            $aSyn[] = $rs0->rightTermId;
            $synCount++;
        } 

        return $aSyn;
     }

   //--------------------------------------------------------------------
    // get synonym for right term. Use bidireccional search (
    // Right hand and left hand logic )
    public function getOrgRTSynonym($RTAid, $canbesynonymtoRTId, $orgId=0,$rtgId=0)
     {
        $aSyn = array();
        $synCount = 0;

        // right hand logic 

        $rs =   $this->where( function($query) use($RTAid, $canbesynonymtoRTId) {
                       $query->where('ownership', '=', 0  )            // public
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('rightTermId', '=', $RTAid);
                     })->orWhere( function($query) use($RTAid, $canbesynonymtoRTId, $orgId)  {
                       $query->where('ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgId)
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('rightTermId', '=', $RTAid);
                      })->orWhere( function($query) use($RTAid, $canbesynonymtoRTId, $orgId)  {
                       $query->where('ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgId)           // same orgid
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('rightTermId', '=', $RTAid);
                     })->orWhere( function($query) use($RTAid, $canbesynonymtoRTId, $orgId, $rtgId)  {
                       $query->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.rightOrgId','=','extended_eav.ownerId')
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.leftOrgId','=',$orgId)
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where('ownership', '=', 1 )            // protected
                             ->where('ownerId', '<>', $orgId)          // distinct orgid
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('rightTermId', '=', $RTAid);
                     })
                    ->get(); 

        foreach ($rs as $rs0){                        
            $aSyn[] = $rs0->leftTermId;
            $synCount++;
        } 

        // left hand logic

        $rs =  $this->where( function($query) use($RTAid, $canbesynonymtoRTId) {
                       $query->where('ownership', '=', 0  )            // public
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('leftTermId', '=', $RTAid);
                     })->orWhere( function($query) use($RTAid, $canbesynonymtoRTId, $orgId)  {
                       $query->where('ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgId)
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('leftTermId', '=', $RTAid);
                      })->orWhere( function($query) use($RTAid, $canbesynonymtoRTId, $orgId)  {
                       $query->where('ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgId)           // same orgid
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('leftTermId', '=', $RTAid);
                     })->orWhere( function($query) use($RTAid, $canbesynonymtoRTId, $orgId, $rtgId)  {
                       $query->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.rightOrgId','=','extended_eav.ownerId')
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.leftOrgId','=',$orgId)
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where('ownership', '=', 1 )            // protected
                             ->where('ownerId', '<>', $orgId)          // distinct orgid
                             ->where('relationTypeId', '=', $canbesynonymtoRTId)
                             ->where('leftTermId', '=', $RTAid);
                     })
                    ->get();



        foreach ($rs as $rs0){                        
            $aSyn[] = $rs0->rightTermId;
            $synCount++;
        } 

        return $aSyn;
     }


    //--------------------------------------------------------------------
    // get synonym for left term. Use bidireccional search (
    // Right hand and left hand logic )
    public function getLTSynonym($LTAid, $canbesynonymtoRTId)
     {
        $aSyn = array();
        $synCount = 0;

        // right hand logic  ///////////
        $rs =  $this->where('relationTypeId', '=', $canbesynonymtoRTId)
                    ->where('rightTermId', '=', $LTAid )    
                    ->get();          

        foreach ($rs as $rs0){                        
            $aSyn[] = $rs0->leftTermId;
            $synCount++;
        } 


        // left hand logic   //////////

        $rs =  $this->where('relationTypeId', '=', $canbesynonymtoRTId)
                    ->where('leftTermId', '=', $LTAid )    
                    ->get();          
        foreach ($rs as $rs0){                        
            $aSyn[] = $rs0->rightTermId;
            $synCount++;
        } 

        return $aSyn;
     }


	//--------------------------------------------------------------------
	public function getSynonymRelationId($canbesynonymtoRTId, $sfRightTermId)
     {
        return ($this->where('relationTypeId', '=', $canbesynonymtoRTId)
			        ->where('rightTermId', '=', $sfRightTermId )	
                    ->get())->toArray(); 
					 
     }	 
	 
	//--------------------------------------------------------------------
	public function retrieveRightTermSynonymId($synonymRelationId)
     {
        $rightTermSynonymId = 0;
        $rs =  $this->where('relationId', '=', $synonymRelationId)
        		->get(); 
					 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){						  
               $rightTermSynonymId = $rs0->rightTermId;
            }			
        }
		return $rightTermSynonymId;
     }

	//--------------------------------------------------------------------
	public function retrieveLeftTermSynonymId($synonymRelationId)
     {
        $leftTermSynonymId = 0;
        $rs =  $this->where('relationId', '=', $synonymRelationId)
        		->get(); 
					 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){						  
               $leftTermSynonymId = $rs0->leftTermId;
            }			
        }
		return $leftTermSynonymId;
     }	   

    /////////////////////////////////////////////////////////////////////
    //--------------------  NEW ------------------------------------------------
    public function retrieveRTermSynonym($termId, $rtFilterId)
     {
        $newTermId = 0;
        $rs =  $this->where('relationTypeId', '=', $rtFilterId)
                    ->where('rightTermId', '=', $termId )    
                    ->get();          
        foreach ($rs as $rs0){                        
            $newTermId = $rs0->leftTermId;
        } 
        return $newTermId;                   
     }
      
    //---------------------  NEW -----------------------------------------------
    public function retrieveLTermSynonym($termId, $rtFilterId)
     {
        $newTermId = 0;
        $rs =  $this->where('relationTypeId', '=', $rtFilterId)
                    ->where('leftTermId', '=', $termId )    
                    ->get();          
        foreach ($rs as $rs0){                        
            $newTermId = $rs0->rightTermId;
        } 
        return $newTermId;                   
     }

	 
	//////////////////////////////////////////////////////////////////// 
	 
}
