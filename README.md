Most of the Web Apps will need to communicate with user. Following the task-centric approach, this add-on for ATK implements various components for helping communicate with the user.

``` php
// Send password reminder to a user with a corresponding email.
$user->tryLoadBy('email', $email);
if (!$user->loaded()) {
    return 'no user with this email';
}

$user->set('reminder_token', uniqid())->save();
$user->ref('Messages')
    ->new('password reminder', 'reminder', ['link'=>$user['reminder_token']])
    ->send();
```

Here are few key points that makes this implementation different:

-   We recognize that Mail Gateway Templates / API is the best way to send mail and focus on that.
-   We understand that in most Apps it should be possible to configure which messages user receives.
-   We want to encourage use of alternative transports, e.g. SMS messages or push notifications.
-   Our messages are persisted (stored in database), so you can re-send.
-   We recognize that "from" address will remain same, that we may add global "bcc" and may also need to disable "send by default" for development environment. 

aoeu



