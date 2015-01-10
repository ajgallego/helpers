<?php echo "<?php\n"; ?>{{ $namespace ? ' namespace '.$namespace.';' : '' }}

<?php $repositoryClass = strstr($model, '\\') ? substr($model, 0, -strlen(strrchr($model, '\\'))).'\UserRepository' : 'UserRepository' ?>
@if ($namespace)

use App;
use View;
use Input;
use Config;
use Redirect;
use Lang;
use Mail;
use Confide;
use Controller;
@endif

/**
 * UsersController Class
 *
 * Implements actions regarding user management
 */
class {{ $class }} extends Controller
{


    /**
     * Stores new account
     *
     * @return Illuminate\Http\Response
     */
    public function {{ (! $restful) ? 'store' : 'postIndex' }}()
    {
        $repo = App::make('{{ $repositoryClass }}');
        $user = $repo->signup(Input::all());

        if ($user->id) {
            if (Config::get('confide::signup_email')) {
                Mail::queueOn(
                    Config::get('confide::email_queue'),
                    Config::get('confide::email_account_confirmation'),
                    compact('user'),
                    function ($message) use ($user) {
                        $message
                            ->to($user->email, $user->username)
                            ->subject(Lang::get('confide::confide.email.account_confirmation.subject'));
                    }
                );
            }

            return Redirect::action('{{ $namespace ? $namespace.'\\' : '' }}{{ $class }}{{ (! $restful) ? '@login' : '@postLogin' }}')
                ->with('notice', Lang::get('confide::confide.alerts.account_created'));
        } else {
            $error = $user->errors()->all(':message');

            return Redirect::action('{{ $namespace ? $namespace.'\\' : '' }}{{ $class }}{{ (! $restful) ? '@create' : '@getCreate' }}')
                ->withInput(Input::except('password'))
                ->with('error', $error);
        }
    }

    /**
     * Attempt to confirm account with code
     *
     * @param string $code
     *
     * @return Illuminate\Http\Response
     */
    public function {{ (! $restful) ? 'confirm' : 'getConfirm' }}($code)
    {
        if (Confide::confirm($code)) {
            $notice_msg = Lang::get('confide::confide.alerts.confirmation');
            return Redirect::action('{{ $namespace ? $namespace.'\\' : '' }}{{ $class }}{{ (! $restful) ? '@login' : '@postLogin' }}')
                ->with('notice', $notice_msg);
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_confirmation');
            return Redirect::action('{{ $namespace ? $namespace.'\\' : '' }}{{ $class }}{{ (! $restful) ? '@login' : '@postLogin' }}')
                ->with('error', $error_msg);
        }
    }


    /**
     * Attempt to send change password link to the given email
     *
     * @return Illuminate\Http\Response
     */
    public function {{ (! $restful) ? 'doForgotPassword' : 'postForgot' }}()
    {
        if (Confide::forgotPassword(Input::get('email'))) {
            $notice_msg = Lang::get('confide::confide.alerts.password_forgot');
            return Redirect::action('{{ $namespace ? $namespace.'\\' : '' }}{{ $class }}{{ (! $restful) ? '@login' : '@postLogin' }}')
                ->with('notice', $notice_msg);
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_password_forgot');
            return Redirect::action('{{ $namespace ? $namespace.'\\' : '' }}{{ $class }}{{ (! $restful) ? '@doForgotPassword' : '@postForgot' }}')
                ->withInput()
                ->with('error', $error_msg);
        }
    }

}
