<?php

namespace OliverKroener\Helpers\MSGraphApi;

use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\FileAttachment;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\Recipient;

class MSGraphMailApiService
{
    public function __construct() {}

    /**
     * Converts a parsed email data into a Microsoft Graph-compatible message object.
     *
     * @param string $rawMessage The raw message to convert.
     * @param string $confFromEmail The email address to use for the "From" field.
     * @return Message Microsoft Graph-compatible message.
     */
    public static function convertToGraphMessage(string $rawMessage, string $confFromEmail): Message
    {
        // Create an Email object from the parsed MimeMessage
        $message = \ZBateson\MailMimeParser\Message::from($rawMessage, false);

        // Get subject
        $subject = $message->getHeader('Subject');
        if ($subject) $subject = $subject->getRawValue();

        // Process "To" recipients
        $toRecipients = $message->getHeader('To');
        $toRecipientsArray = [];
        if ($toRecipients !== null) {
            foreach ($toRecipients->getParts() as $email) {
                $recipient = new Recipient();
                $emailAddress = new EmailAddress();
                $emailAddress->setAddress($email->getValue());
                $emailAddress->setName($email->getName());
                $recipient->setEmailAddress($emailAddress);
                $toRecipientsArray[] = $recipient;
            }
        }

        // Process "CC" recipients
        $ccRecipients = $message->getHeader('Cc');
        $ccRecipientsArray = [];
        if ($ccRecipients !== null) {
            foreach ($ccRecipients->getParts() as $email) {
                $recipient = new Recipient();
                $emailAddress = new EmailAddress();
                $emailAddress->setAddress($email->getValue());
                $emailAddress->setName($email->getName());
                $recipient->setEmailAddress($emailAddress);
                $ccRecipientsArray[] = $recipient;
            }
        }

        // Process "BCC" recipients
        $bccRecipients = $message->getHeader('Bcc');
        $bccRecipientsArray = [];
        if ($bccRecipients !== null) {
            foreach ($bccRecipients->getParts() as $email) {
                $recipient = new Recipient();
                $emailAddress = new EmailAddress();
                $emailAddress->setAddress($email->getValue());
                $emailAddress->setName($email->getName());
                $recipient->setEmailAddress($emailAddress);
                $bccRecipientsArray[] = $recipient;
            }
        }

        // Process "Reply-To" addresses
        $replyToHeader = $message->getHeader('Reply-To');
        $replyToArray = [];
        if ($replyToHeader !== null) {
            foreach ($replyToHeader->getParts() as $email) {
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
            $fileAttachment->setODataType('#microsoft.graph.fileAttachment');
            $fileAttachment->setName($attachmentName);
            $fileAttachment->setContentType($attachmentContentType);

            // Assuming your content is stored in $content
            $fileAttachment->setContentBytes(base64_encode($attachmentContent));

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
        $graphMessage->setSubject($subject ?? 'No Subject');
        $graphMessage->setBody($body);
        $graphMessage->setAttachments($fileAttachments);

        return $graphMessage;
    }
}
