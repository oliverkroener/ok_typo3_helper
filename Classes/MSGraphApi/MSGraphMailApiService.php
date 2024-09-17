<?php

namespace OliverKroener\Helpers\MSGraphApi;

use GuzzleHttp\Psr7\Utils;
use Microsoft\Graph\Generated\Models\BodyType;
use Microsoft\Graph\Generated\Models\EmailAddress;
use Microsoft\Graph\Generated\Models\FileAttachment;
use Microsoft\Graph\Generated\Models\ItemBody;
use Microsoft\Graph\Generated\Models\Message;
use Microsoft\Graph\Generated\Models\Recipient;
use Symfony\Component\Mime\RawMessage;

class MSGraphMailApiService
{
    public function __construct() {}

    /**
     * Converts a parsed email data into a Microsoft Graph-compatible message object.
     *
     * @param RawMessage $rawMessage The raw message to convert.
     * @param string $confFromEmail The email address to use for the "From" field.
     * @return Message Microsoft Graph-compatible message.
     */
    public static function convertToGraphMessage(RawMessage $rawMessage, string $confFromEmail): Message
    {
        // Create an Email object from the parsed MimeMessage
        $message = \ZBateson\MailMimeParser\Message::from($rawMessage->toString(), false);

        // Process "To" recipients
        $toRecipients = $message->getHeader('To');
        $toRecipientsArray = [];
        foreach ($toRecipients->getAllParts() as $email) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($email->getValue());
            $emailAddress->setName($email->getName());
            $recipient->setEmailAddress($emailAddress);
            $toRecipientsArray[] = $recipient;
        }

        // Process "CC" recipients
        $ccRecipients = $message->getHeader('Cc');
        $ccRecipientsArray = [];
        if ($ccRecipients !== null) {
            foreach ($ccRecipients->getAllParts() as $email) {
                $recipient = new Recipient();
                $emailAddress = new EmailAddress();
                $emailAddress->setAddress($email->getValue());
                $emailAddress->setName($email->getName());
                $recipient->setEmailAddress($emailAddress);
                $ccRecipientsArray[] = $recipient;
            }
        }

        // Process "BCC" recipients
        $bccRecipients = $rawMessage->getBcc();
        $bccRecipientsArray = [];
        if ($bccRecipients !== null) {
            foreach ($bccRecipients as $email) {
                $recipient = new Recipient();
                $emailAddress = new EmailAddress();
                $emailAddress->setAddress($email->getAddress());
                $emailAddress->setName($email->getName());
                $recipient->setEmailAddress($emailAddress);
                $bccRecipientsArray[] = $recipient;
            }
        }

        // Process "Reply-To" address
        $replyToHeader = $message->getHeader('Reply-To');
        $replyToArray = [];
        if ($replyToHeader !== null) {
            foreach ($replyToHeader->getAllParts() as $email) {
                $recipient = new Recipient();
                $emailAddress = new EmailAddress();
                $emailAddress->setAddress($email->getValue());
                $emailAddress->setName($email->getName());
                $recipient->setEmailAddress($emailAddress);
                $replyToArray[] = $recipient;
            }
        }

        // Get message body
        $htmlBody = $message->getHtmlContent();
        $plainTextBody = $message->getTextContent();

        // Create the body content
        $body = new ItemBody();
        if (!empty($htmlBody)) {
            $body->setContentType(new BodyType(BodyType::HTML));
            $body->setContent($htmlBody);
        } elseif (!empty($plainTextBody)) {
            $body->setContentType(new BodyType(BodyType::TEXT));
            $body->setContent($plainTextBody);
        } else {
            $body->setContentType(new BodyType(BodyType::TEXT));
            $body->setContent(''); // Default empty content if none provided
        }

        // Process attachments
        $fileAttachments = [];
        $attachments = $message->getAllAttachmentParts();
        foreach ($attachments as $attachment) {
            $attachmentName = $attachment->getFilename();
            $attachmentContentType = $attachment->getContentType();
            $attachmentContent = $attachment->getContent();

            $fileAttachment = new FileAttachment();
            $fileAttachment->setName($attachmentName);
            $fileAttachment->setContentType($attachmentContentType);

            // Assuming your content is stored in $content
            $stream = Utils::streamFor(base64_encode($attachmentContent));
            $fileAttachment->setContentBytes($stream);

            $fileAttachments[] = $fileAttachment;
        }

        // Set the "From" address
        $from = new Recipient();
        $fromEmail = new EmailAddress();
        $fromEmail->setAddress($confFromEmail); // Use the parsed 'From' header or a default value
        $from->setEmailAddress($fromEmail);

        // Construct the message object
        $graphMessage = new Message();
        $graphMessage->setFrom($from);
        $graphMessage->setToRecipients($toRecipientsArray);
        $graphMessage->setCcRecipients($ccRecipientsArray);
        $graphMessage->setBccRecipients($bccRecipientsArray);
        $graphMessage->setReplyTo($replyToArray);
        $graphMessage->setSubject($message->getSubject() ?? 'No Subject');
        $graphMessage->setBody($body);
        $graphMessage->setAttachments($fileAttachments);

        return $graphMessage;
    }
}
