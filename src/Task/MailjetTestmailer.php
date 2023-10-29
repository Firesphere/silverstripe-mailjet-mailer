<?php

namespace Firesphere\Mailjet\Task;

use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\BuildTask;

/**
 * @package Firesphere\Mailjet\Mailer
 */
class MailjetTestmailer extends BuildTask
{
    private static $segment = 'mailjet-test';

    public function run($request)
    {
        $from = $request->getVar('from');
        $to = $request->getVar('to');
        $email = Email::create(
            $from,
            $to,
            'Test email via Mailjet',
            'This email is send from Silverstripe Framework via Mailjet.'
        );

        $email->send();
    }
}
