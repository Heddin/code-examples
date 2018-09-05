<?php

namespace App\Listeners;

use App\Events\Interfaces\ISocialActionEvent;
use App\Events\UserAction\Social\SocialLoginEvent;
use App\Events\UserAction\Social\SocialRegisterEvent;
use App\Events\UserAction\Social\SocialShareEvent;
use App\Model\UserActions\UserRegistered;
use Carbon\Carbon;


class SocialActionPerformed
{
    /**
     * Handle the event.
     *
     * @param  ISocialActionEvent  $event
     * @return void
     */
    public function handle(ISocialActionEvent $event)
    {
        if($event instanceof SocialLoginEvent){
           $this->loginHandle($event);
        }

        if($event instanceof SocialRegisterEvent){
           $this->registerHandle($event);
        }

        if($event instanceof SocialShareEvent){
           $this->shareHandle($event);
        }
    }

    private function loginHandle(ISocialActionEvent $event){
        info("{$event->user->name}({$event->user->id}) logged in with social service({$event->provider}) at {$event->time}");
    }

    private function registerHandle(ISocialActionEvent $event){
        info("{$event->user->name}({$event->user->id}) registered  with  social service ({$event->provider}) at {$event->time}");

        $date = Carbon::now()->format('Y-m-d');

        $registered = UserRegistered::where('date', $date)->first();
        if (!$registered) {
            $registered = new UserRegistered(['date' => $date]);
        }

        $registered->increment($event->provider.'_count');
        $registered->save();


    }

    private function shareHandle(ISocialActionEvent $event){
        info("{$event->user->name}({$event->user->id}) shared  with  social social service ({$event->provider}) at {$event->time}");

        debug($event->user->social->shares);
    }
}
