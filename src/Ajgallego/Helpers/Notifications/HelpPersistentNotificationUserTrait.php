<?php namespace Ajgallego\Helpers\Notifications;

trait HelpPersistentNotificationUserTrait
{
	/**
	* Relations
	*/
    public function persistentNotifications()
    {
    	// TODO Review route 'Ajgallego\Helpers\Notifications\PersistentNotification'
    	// Review 'models\PersistentNotification'
        return $this->hasMany('Ajgallego\Helpers\Notifications\PersistentNotification');
    }
}