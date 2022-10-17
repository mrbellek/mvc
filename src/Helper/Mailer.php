<?php
declare(strict_types=1);

namespace MVC\Helper;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class Mailer
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->From = 'admin@' . filter_input(INPUT_SERVER, 'SERVER_NAME');
    }

    public function test(string $email): bool
    {
        return $this->send(
            $email,
            'Test email',
            sprintf('This is the body of the test email to %s.', $email)
        );
    }

    public function send(string $recipient, string $subject, string $body, ?string $sender = null)
    {
        $this->mailer->addAddress($recipient);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $body;

        if ($sender) {
            $this->mailer->setFrom($sender);
        }

        return $this->mailer->send();
    }
}