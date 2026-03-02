<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class DiscussionForums extends Model
{
    protected $guarded = [];

    protected $table = 'discussion_forums';



    /*
    *
    * Category 
    */
    public function category(){

      return $this->belongsTo('Modules\BackOffice\Entities\DiscussionCategory','discussion_category_id');
    }
    /*
    *
    * User 
    */
    public function user(){

      return $this->belongsTo('App\User','commented_by');
    }




}
