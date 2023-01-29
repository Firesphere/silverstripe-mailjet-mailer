# SilverStripe SendGrid Mailer 

Simple mailer module that uses Mailjet API to send emails.

## Requirements

* silverstripe/cms ^4.3.x
* silverstripe/framework ^4.3.x
* mailjet/mailjet-apiv3-php "^1.5.8

## Installation

```bash
composer require firesphere/mailjet-mailer
```

## Configuration

Add the following to your `.env`:

```dotenv
SS_MAILJET_KEY="YOURMAILJETKEY"
SS_MAILJET_SECRET="YOURMAILJETSECRET"
```

## Usage

Simply use the Email class provided by SilverStripe framework:

```php
$email = new SilverStripe\Control\Email\Email();
$email->setFrom('from@example.com', 'John Doe');
$email->setTo('to@example.com', 'Jane Doe');
$email->setSubject('This is a test email');
$email->setBody('Hello there, this was sent using Mailjet');
$email->addAttachment('path/to/file.pdf', 'document.pdf');
$email->send();
```

That should be all there is to it.

### DEV usage

If, in dev mode, you still want to use the Mailjet Mailer, but
not want to send actual emails, set the `send` flag to `false`

Via PHP:
```php
new \Firesphere\Mailjet\Service\MailjetMailer(false);
```
or
```php
$mailer = (new \Firesphere\Mailjet\Service\MailjetMailer())->setSend(false);
```

Via YML:
```yaml
SilverStripe\Core\Injector\Injector:
  Firesphere\Mailjet\Service\MailjetMailer:
    constructor:
      - false
```

## Advanced usage

Mailjet allows to send multiple emails in a single request.
To do this, use the MailjetMailer directly and give it an array of Email objects.

E.g.

```php
$mails = [
    $mail1 = Email::create('to@example.com', 'from@example.com', 'Subject', 'Body'); // 
    $mail2 = Email::create('to@example.com', 'from@example.com', 'Subject', 'Body'); // 
];

(new \Firesphere\Mailjet\Service\MailjetMailer())->send($mails);
```

