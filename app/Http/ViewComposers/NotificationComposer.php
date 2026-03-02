<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

use Modules\Masters\Entities\UnitType;
 

class NotificationComposer
{     

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct( )
    {
       
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {

      $user_notification = \Auth::user()->unreadNotifications;
      $view->with(['notifications' => $user_notification]); 
         
    }
}
