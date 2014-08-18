<h1>{{ Lang::get('helpers::auth.email.account_confirmation.subject') }}</h1>

<p>{{ Lang::get('helpers::auth.email.account_confirmation.greetings', array('name' => $user['username'])) }},</p>

<p>{{ Lang::get('helpers::auth.email.account_confirmation.body') }}</p>
<a href='{{{ URL::to("user/confirm/{$user['confirmation_code']}") }}}'>
    {{{ URL::to("users/confirm/{$user['confirmation_code']}") }}}
</a>

<p>{{ Lang::get('helpers::auth.email.account_confirmation.farewell') }}</p>
