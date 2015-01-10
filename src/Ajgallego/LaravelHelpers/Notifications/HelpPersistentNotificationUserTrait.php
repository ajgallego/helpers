<?php namespace Ajgallego\LaravelHelpers\Notifications;

trait HelpPersistentNotificationUserTrait
{
	/**
	* Relations
	*/
    public function persistentNotifications()
    {
    	// TODO Review route 'Ajgallego\LaravelHelpers\Notifications\PersistentNotification'
    	// Review 'models\PersistentNotification'
        return $this->hasMany('\PersistentNotification');
    }
}