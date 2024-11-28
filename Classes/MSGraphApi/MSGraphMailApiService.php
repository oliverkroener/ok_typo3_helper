<?php

namespace OliverKroener\Helpers\MSGraphApi;

use GuzzleHttp\Psr7\Utils;
use Microsoft\Graph\Generated\Models\BodyType;
use Microsoft\Graph\Generated\Models\EmailAddress;
use Microsoft\Graph\Generated\Models\FileAttachment;
use Microsoft\Graph\Generated\Models\ItemBody;
use Microsoft\Graph\Generated\Models\Message;
use Microsoft\Graph\Generated\Models\Recipient;
use Symfony\Component\Mime\Email;

class MSGraphMailApiService
{
    public function __construct() {}

    /**
     * Converts a parsed email data into a Microsoft Graph-compatible message object.
     *
     * @param Email $rawMessage The raw message to convert.
     * @return array of (message, from) Microsoft Graph-compatible message.
     */
    public static function convertToGraphMessage(Email $rawMessage): array
    {
        // Convert RawMessage to Email object
        $email = $rawMessage;

        // Process "From" address
        $fromAddresses = $email->getFrom();
        $from = new Recipient();
        $fromEmail = new EmailAddress();

        if (!empty($fromAddresses)) {
            $address = $fromAddresses[0];
            $fromEmail->setAddress($address->getAddress());
            $fromEmail->setName($address->getName());

            $fromAddress = $address->getAddress();
        }

        $from->setEmailAddress($fromEmail);

        // Process "To" recipients
        $toRecipientsArray = [];
        foreach ($email->getTo() as $address) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($address->getAddress());
            $emailAddress->setName($address->getName());
            $recipient->setEmailAddress($emailAddress);
            $toRecipientsArray[] = $recipient;
        }

        // Process "CC" recipients
        $ccRecipientsArray = [];
        foreach ($email->getCc() as $address) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($address->getAddress());
            $emailAddress->setName($address->getName());
            $recipient->setEmailAddress($emailAddress);
            $ccRecipientsArray[] = $recipient;
        }

        // Process "BCC" recipients
        $bccRecipientsArray = [];
        foreach ($email->getBcc() as $address) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($address->getAddress());
            $emailAddress->setName($address->getName());
            $recipient->setEmailAddress($emailAddress);
            $bccRecipientsArray[] = $recipient;
        }

        // Process "Reply-To" address
        $replyToArray = [];
        foreach ($email->getReplyTo() as $address) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($address->getAddress());
            $emailAddress->setName($address->getName());
            $recipient->setEmailAddress($emailAddress);
            $replyToArray[] = $recipient;
        }

        // Get message body
        $htmlBody = $email->getHtmlBody();
        $plainTextBody = $email->getTextBody();

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
        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $contentDispositionHeader = $headers->get('Content-Disposition');
            $attachmentName = $contentDispositionHeader->getParameter('filename');
            $attachmentContentType = $attachment->getMediaType() . "/" . $attachment->getMediaSubType();
            $attachmentContent = $attachment->getBody();

            $fileAttachment = new FileAttachment();
            $fileAttachment->setName($attachmentName);
            $fileAttachment->setContentType($attachmentContentType);
            $fileAttachment->setContentBytes(Utils::streamFor(base64_encode($attachmentContent)));

            $fileAttachments[] = $fileAttachment;
        }

        // Construct the message object
        $graphMessage = new Message();
        $graphMessage->setFrom($from);
        $graphMessage->setToRecipients($toRecipientsArray);
        $graphMessage->setCcRecipients($ccRecipientsArray);
        $graphMessage->setBccRecipients($bccRecipientsArray);
        $graphMessage->setReplyTo($replyToArray);
        $graphMessage->setSubject($email->getSubject() ?? 'No Subject');
        $graphMessage->setBody($body);
        $graphMessage->setAttachments($fileAttachments);

        return [ 
            'message' => $graphMessage,
            'from' => $fromAddress
        ];
    }
}
