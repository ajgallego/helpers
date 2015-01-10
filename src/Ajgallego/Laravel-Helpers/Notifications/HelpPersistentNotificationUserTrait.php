<?php namespace Ajgallego\Laravel-Helpers\Notifications;

trait HelpPersistentNotificationUserTrait
{
	/**
	* Relations
	*/
    public function persistentNotifications()
    {
    	// TODO Review route 'Ajgallego\Laravel-Helpers\Notifications\PersistentNotification'
    	// Review 'models\PersistentNotification'
        return $this->hasMany('\PersistentNotification');
    }
}