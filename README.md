## ATK4 Outbox

Most web applications will need to communicate with the user. Following the task-centric approach, this add-on for ATK implements various components to help communicate with the user.

The basic idea is to avoid errors and provide only one way to use the component.


### How to install
`composer require atk4/outbox`

### How to use with Atk4/Ui

Add the lines below after calling `$app->initLayout()`
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
```
Due to missing App, init is not called so you have to do it you have to run it
```php
$outbox->invokeInit(); 
```

### Mailer (PHMailer)

 - Sendmail
 - Gmail
   - can be customized using Atk4 direct injection :
     - username
     - password
 - Smtp
   - can be customized using Atk4 direct injection :
     - debug = PHPMailer::DEBUG_OFF;
     - auth = false
     - host = 'localhost';
     - port = 587;
     - secure = PHPMailer::ENCRYPTION_*
     - username
     - password 
    
### Extend Mailer

Whatever your needs are you must respect only the contract provided by the `MailerInterface` interface.
So, any Traditional SMTP or API SMTP can be added only by create a new class and define 
the method : `send(Mail $mail): MailResponse`, add to Outbox during initialization and use it.

If you create a new Mailer, feel free to open a PR.

### How to use it
``` php

// request an new email from outbox  
$mail = $outbox->new();

// use an already saved mail template
$mail->withTemplateIdentifier('template_test');

// replace token with data
$mail->replaceContent('token', 'Agile Toolkit');

// Add a custom address as "to"
$mail->ref('to')->createEntity()->save([
    'email' => 'destination@email.it',
    'name' => 'destination',
]);

// send the mail and check the response 
$response = $outbox->send($mail);
```

#### TODO
 - Store the token present in a template, inside a field (at the moment, there is only a draft)
 - Add more helpers to the Mail template or create a controller to manipulate it and leave it simpler as a descriptor
 - Add a wysiwyg editor to edit individual templates (UI)
 - Add resend system for failed messages
 - Send SMS
 - If you have more ideas, feel free to open an issue
