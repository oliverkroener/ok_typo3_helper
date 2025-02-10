<?php

namespace OliverKroener\Helpers\Mail\Mime;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use ZBateson\MailMimeParser\MailMimeParser;

class MessageConverter
{
    public static function convertToEmail(String $rawMessage): Email
    {
        $parser = new MailMimeParser();
        $parsedMessage = $parser->parse($rawMessage, false);

        $email = new Email();
        $email->from($parsedMessage->getHeaderValue('from'))
              ->to($parsedMessage->getHeaderValue('to'))
              ->subject($parsedMessage->getHeaderValue('subject'))
              ->text($parsedMessage->getTextContent())
              ->html($parsedMessage->getHtmlContent());

        return $email;
    }
}