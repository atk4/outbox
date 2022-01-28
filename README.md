## ATK4 Outbox

Most of the Web Apps will need to communicate with user. Following the task-centric approach, this add-on for ATK implements various components for helping communicate with the user.

The idea behind is to avoid errors, 

### How to install
`composer require atk4/outbox`

### How to use with Atk4/Ui

Add the lines below after calling `$app->initLayout()` of Application add it as a normal View
```php
$app->add([Outbox::class, [
    'mailer' => new Sendmail(),
    'model' => new Mail($this->db),
]]);
```
After that you can call from any view `$this->getApp()->getOutbox()`

### How to use without Atk4/Ui

Add the code below :
```php
$outbox = new Outbox([
    'mailer' => new Sendmail(),
    'model' => new Mail($this->db),
]);
$outbox->invokeInit();
```
Use it

### Mailer Configuration (PHMailer)

 - Sendmail
 - Gmail
   - can be customized via Atk4 direct injection :
     - username
     - password
 - Smtp
   - can be customized via Atk4 direct injection :
     - debug = PHPMailer::DEBUG_OFF;
     - auth = false
     - host = 'localhost';
     - port = 587;
     - secure = PHPMailer::ENCRYPTION_*
     - username
     - password 
    


### How to use it
``` php
// request an email from outbox  
$mail = $outbox->new();
// use an already saved mail template
$mail->withTemplateIdentifier('template_test');
// replace token with data
$mail->replaceContent('token', 'Agile Toolkit');

// Add a custom to address
$mail->ref('to')->createEntity()->save([
    'email' => 'destination@email.it',
    'name' => 'destination',
]);

// send the mail and check the response 
$response = $outbox->send($mail);
```

#### TODO
 - Store token present in a template, inside a field (there is only a draft)
 - Add more helper to Mail model or create a controller to manipulate it
 - Add Ui wysiwyg editor to modify templates
 - Add Resend of failed messages
 - if you have more, feel free to open an issue
