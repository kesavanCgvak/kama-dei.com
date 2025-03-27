<?php
/*--------------------------------------------------------------------------------
 *  File          : SolutionLink.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating solution_link table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 1.5
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionLink extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'solution_link';
	protected $primaryKey = "solutionLinkId";
	protected $modifiers  = ['sourceRelationId', 'targetRelationId', 
	                         'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('solutionLinkId', '=', $id)->get(); 
     }


	//--------------------------------------------------------------------
	public function getBySourceRelationId($id)
     {
         return $this->where('sourceRelationId', '=', $id)->get(); 
     }	 
	 	 
	 
}
