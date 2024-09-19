<?php

namespace OliverKroener\Helpers\MSGraphApi;

use GuzzleHttp\Psr7\Utils;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\FileAttachment;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\Recipient;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class MSGraphMailApiService
{
    public function __construct() {}

    /**
     * Converts a parsed email data into a Microsoft Graph-compatible message object.
     *
     * @param RawMessage $rawMessage The raw message to convert.
     * @param string $confFromEmail The email address to use for the "From" field.
     * @return array of (message, from) Microsoft Graph-compatible message.
     */
    public static function convertToGraphMessage(RawMessage $rawMessage): array
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
            // Get the prepared headers from the attachment
            $preparedHeaders = $attachment->getPreparedHeaders();
        
            // Retrieve the attachment's filename
            // Attempt to obtain the 'name' parameter from the 'Content-Disposition' header
            $contentDispositionHeader = $preparedHeaders->get('Content-Disposition');
            $attachmentName = $contentDispositionHeader->getParameter('name');
        
            // Determine the content type of the attachment
            // Access the 'Content-Type' header
            $contentTypeHeader = $preparedHeaders->get('Content-Type');
            $attachmentContentType = $contentTypeHeader->getValue() ?? 'text/plain';
        
            // Extract the body/content of the attachment
            $attachmentContent = $attachment->getBody();
        
            $fileAttachment = new FileAttachment();
            $fileAttachment->setODataType("#microsoft.graph.fileAttachment");
            $fileAttachment->setName($attachmentName);
            $fileAttachment->setContentType($attachmentContentType);
            $fileAttachment->setContentBytes(base64_encode($attachmentContent));
        
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
