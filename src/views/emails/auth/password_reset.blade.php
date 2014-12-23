<h1>{{ Lang::get('laravel-helpers::auth.email.password_reset.subject') }}</h1>

<p>{{ Lang::get('laravel-helpers::auth.email.password_reset.greetings', array( 'name' => $user['username'])) }},</p>

<p>{{ Lang::get('laravel-helpers::auth.email.password_reset.body') }}</p>
<a href='{{ URL::to('user/reset_password/'.$token) }}'>
    {{ URL::to('user/reset_password/'.$token)  }}
</a>

<p>{{ Lang::get('laravel-helpers::auth.email.password_reset.farewell') }}</p>
