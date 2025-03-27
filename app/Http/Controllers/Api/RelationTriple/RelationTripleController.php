<?php   
/* RelationTriple api
 * Get triples

 * Company  : Kamazooie Development Corporation
 * Developer:  Gabriel Carrillo
 * Version  :  1.4.4
 * Updated  :  22 July 2020
 */

namespace App\Http\Controllers\Api\RelationTriple;

use Illuminate\Http\Request;
use App\Controllers;
use App\Helpers\FunctionHelper;
use App\Models\Relation;
use App\Models\RelationType;
use App\Models\Term;
use App\Models\RelationTypeFilter;

use Illuminate\Support\Facades\Config;

class RelationTripleController extends \App\Http\Controllers\Controller{


    /*------------------------------------------------------------------
     * getTermRelation
       get triples (knowledge records) from table relation
     
      Input

        int      $orgid
        int      $limit
        string   $leftTerm
        string   $relationType
        string   $rightTerm
     
      Output
     
        int      $orgid
        int      $limit
        string   $leftTerm
        string   $relationType
        string   $rightTerm
        json     $triples
      
     *-----------------------------------------------------------------*/

    public function getRelation($orgid, $limit, $leftTerm, $relationType, $rightTerm) {

      /** Data cleaning  -------------------------   */
      if ($leftTerm =="Null") { $leftTerm = ""; }
      if ($rightTerm =="Null") { $rightTerm = ""; }
      if ($relationType =="Null") { $relationType = ""; }   

      /**--- VALIDATION   -------------------------------------------------------
       - If right orgid is empty,
            send error message.
       - If right term is empty, and left term is empty, and relation type is empty,
            send error message.
       - If right term is not empty, and left term is not empty, and relation type is not empty, 
            send error message.
       - If right term is empty and relation type is empty, 
            send error message.
       - If left term is empty and relation type is empty,
            send error message.

      TESTING
      https://staging.kama-dei.com/api/v1/relationTriple/1/1/goodbye/is a member of/Null
      https://staging.kama-dei.com/api/v1/relationTriple/1/1/Null/is a member of/greetings
      https://staging.kama-dei.com/api/v1/relationTriple/1/1/hey/Null/greetings

      http://159.203.27.67/api/v1/relationTriple/1/1/goodbye/is a member of/Null
      http://159.203.27.67/api/v1/relationTriple/1/99/Null/is a member of/greetings
      http://159.203.27.67/api/v1/relationTriple/1/1/hey/Null/greetings

       ---------------------------------------------------------------------------*/

      if(is_null($orgid)  )
    	  { return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }

      if(is_null($leftTerm)  and is_null($relationType) and is_null($rightTerm) )
    	  { return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }

      if(!empty($leftTerm)  and !empty($relationType) and !empty($rightTerm) )
      	{ return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }

      if(is_null($rightTerm)  and is_null($relationType)  )
    	  { return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }

      if(is_null($leftTerm)  and is_null($relationType)  )
    	  { return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }

      if(is_null($limit)  )
    	  { $limit = 0; }


      /**----- MAIN LOGIC   ----------------------------------------------
       - if limit is Null or zero,  get all records.
       - If limit > 0, get the first n records.

       - If $rightTerm is empty, and $leftTerm is not empty, and $relationType is not empty,
            get records from table relation where
                relation.leftTerm = $leftTerm and relation.relationType = $relationType
            .
        if $leftTerm is empty, and $rightTerm is not empty, and $relationType is no empty,
            get records from table relation where
                relation.rightTerm = $rightTerm and relation.relationType = $relationType

        If $leftTerm is not empty and $rightTermterm is not empty and $relationType is empty,
            get records from table relation where
                relation.leftTerm = $leftTerm and relation.rightTerm = $rightTerm

       --------------------------------------------------------------------**/  
       // Set variables
       if ($limit == 0) { $limit = 999999999; }
       $triples = "";


       // Search triples

       // $rightTerm is empty, and $leftTerm is not empty, and $relationType is not empty
       if( isset($leftTerm)  and isset($relationType) and empty($rightTerm)  )  {
          $triples = $this->getRTerm($orgid, $limit, $leftTerm, $relationType);
       }

       // $leftTerm is empty, and $rightTerm is not empty, and $relationType is no empty,
       if( isset($rightTerm)  and isset($relationType) and empty($leftTerm)  )  {
         $triples = $this->getLTerm($orgid, $limit, $relationType, $rightTerm);
       }


       // $leftTerm is not empty and $rightTermterm is not empty and $relationType is empty
       if( isset($leftTerm)  and isset($rightTerm) and empty($relationType)  )  {
         $triples = $this->getRelationType($orgid, $limit, $leftTerm, $rightTerm);
       }


       //}        

      /**----- RESPONSE   ----------------------------------**/ 

	    return \Response::json([ 'orgid'=>$orgid, 'leftTerm'=>$leftTerm,
	  	    'relationType' => $relationType, 'rightTerm'=>$rightTerm,'triples'=>$triples ]);

      //----  end of function getTermRelation    -----------------------------//-

	}


   /*--------------------------------------------------------------------
	 *  getRTerm
   *  get right term
	 *  get triples from table relation 
	 *    where relation.leftTerm = $leftTerm and 
	 *          relation.relationType = $relationType
	 *
	 *-------------------------------------------------------------------*/
   public function getRTerm($orgid, $limit, $leftTermName, $relationTypeName) {

 
    $leftTermId     = 0;
    $relationTypeId = 0;
    $rightTermId    = 0;
    $rightTermName  = '';
    $count = 0;
    $rtgId = 8;  // can access protected data from

    $oTerm          = new Term();
    $oRT            = new RelationType();
    $oRelation      = new Relation();

   // $leftTermId     = $oTerm->retrieveTermIdByName($leftTermName);
   
    $tmp = $oTerm->retrieveFilteredTermId($orgid,$leftTermName,$rtgId);
    foreach($tmp as $rs0) {                   
        $leftTermId = $rs0->termId;               
    }  

    $relationTypeId = $oRT->retrieveIdByName($relationTypeName);
    //$rs = $oRelation->getByLeftTermRelationType($leftTermId, $relationTypeId, $limit);
    $rs = $oRelation->getOrgLeftRelationType ($orgid,$leftTermId,$relationTypeId,$rtgId,$limit);
 
    foreach($rs as $rs0) {
        $rightTermId = $rs0->rightTermId;
        $name = $oTerm->retrieveTermName($rightTermId);
        $name = strtolower($name);
        $count++;
        $rightTermNam[] = $name; 
    }


    if ($count == 1) {
      $rightTermName = $name;  
    } elseif ($count > 1) {
      $rightTermName = $rightTermNam;     	
    }
    
    $triples[] = ['leftTerm'=>$leftTermName, 'relationType'=>$relationTypeName, 'rightTerm'=>$rightTermName ];

    return $triples;

   }

    //--  end of function getRTerm  ------------------------


   /*--------------------------------------------------------------------
   *  getLTerm
   *  get left term
   *  get triples from table relation 
   *    where relation.rightTerm = $rightTerm and 
   *          relation.relationType = $relationType
   *
   *-------------------------------------------------------------------*/
   public function getLTerm($orgid, $limit,$relationTypeName, $rightTermName) {

 
    $leftTermId     = 0;
    $relationTypeId = 0;
    $rightTermId    = 0;
    $leftTermName   = '';
    $count          = 0;
    $rtgId = 8;  // can access protected data from

    $oTerm          = new Term();
    $oRT            = new RelationType();
    $oRelation      = new Relation();

    //$rightTermId     = $oTerm->retrieveTermIdByName($rightTermName);

    $tmp = $oTerm->retrieveFilteredTermId($orgid,$rightTermName,$rtgId);
    foreach($tmp as $rs0) {                   
        $rightTermId = $rs0->termId;               
    }  

    $relationTypeId = $oRT->retrieveIdByName($relationTypeName);
    //$rs = $oRelation->getRightTermRelationType($rightTermId, $relationTypeId, $limit);
    $rs = $oRelation->getOrgRightRelationType($orgid,$relationTypeId,$rightTermId,$rtgId, $limit);

    
    foreach($rs as $rs0) {
      $leftTermId = $rs0->leftTermId;
      $name = $oTerm->retrieveTermName($leftTermId);
      $name = strtolower($name);
      $count++;
      $leftTermNam[] = $name; 
    }

    if ($count == 1) {
      $leftTermName = $name;  
    } elseif ($count > 1) {
      $leftTermName = $leftTermNam;    	
    }

    $triples[] = ['leftTerm'=>$leftTermName, 'relationType'=>$relationTypeName, 'rightTerm'=>$rightTermName ];

    return $triples;

   }

    //--  end of function getLTerm  ------------------------

   /*--------------------------------------------------------------------
   *  getRelationType
   *  get relation type
   *  get triples from table relation 
   *    where relation.leftTerm = $leftTerm and 
   *          relation.rightTerm = $rightTerm
   *
   *-------------------------------------------------------------------*/

 
   public function getRelationType($orgid, $limit, $leftTermName, $rightTermName) { 

 
    $leftTermId     = 0;
    $relationTypeId = 0;
    $rightTermId    = 0;
    $relationTypeName  = "";
    $count          = 0;
    $name           = "";
    $triples = "";

    $oTerm          = new Term();
    $oRT            = new RelationType();
    $oRelation      = new Relation();

    $leftTermId     = $oTerm->retrieveTermIdByName($leftTermName);
    $rightTermId    = $oTerm->retrieveTermIdByName($rightTermName);
    $tmp            = $oRelation->getByLeftRight($orgid, $leftTermId, $rightTermId, $limit);
    foreach ($tmp as $rs1) {
      $relationTypeId   = $rs1->relationTypeId;
      $name   = $oRT->retrieveNameById($relationTypeId);
      $count++;
      $relationTypeNam[] = $name;
    }

    if ($count == 1) {
       $relationTypeName  = $name;
    } elseif ($count > 1) {
       $relationTypeName = $relationTypeNam;
    }


    $triples = ['leftTerm'=>$leftTermName, 'relationType'=>$relationTypeName, 'rightTerm'=>$rightTermName ];

    return $triples;

   }

    //--  end of function getRType  ------------------------
 


}
