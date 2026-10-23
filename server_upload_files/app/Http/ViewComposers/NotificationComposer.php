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

      // only the latest 50 go into the page; some users have 10,000+ unread, which made every page slow
      $user = \Auth::user();
      $user_notification = $user->unreadNotifications()->take(50)->get();
      $view->with(['notifications' => $user_notification, 'notification_count' => $user->unreadNotifications()->count()]);
         
    }
}
