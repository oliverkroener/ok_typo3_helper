<?php

namespace OliverKroener\Helpers\MSGraphApi;

use Exception;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\FileAttachment;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\Recipient;
use Swift_Mime_Message;
use Swift_Attachment;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\LogManager;

/**
 * Class MSGraphMailApiService
 *
 * Service to convert Swift_Mime_Message to Microsoft Graph-compatible Message object.
 */
class MSGraphMailApiService
{
    /**
     * Converts a Swift_Mime_Message into a Microsoft Graph-compatible message object.
     *
     * @param \Swift_Mime_Message $email The Swift_Mime_Message to convert.
     * @return array An array containing the Graph message object and the sender's email address.
     */
    public static function convertToGraphMessage(Swift_Mime_Message $email): array
    {
        try {
            /** @var LogManager $logManager */
            $logManager = GeneralUtility::makeInstance(LogManager::class);
            $logger = $logManager->getLogger(__CLASS__);

            // Initialize the 'From' recipient
            $fromAddresses = $email->getFrom();
            $from = new Recipient();
            $fromEmail = new EmailAddress();

            if (!empty($fromAddresses)) {
                // Get the first 'From' address
                $emailAddress = array_key_first($fromAddresses);
                $name = $fromAddresses[$emailAddress] ?? '';
                $fromEmail->setAddress($emailAddress);
                $fromEmail->setName($name);
                $from->setEmailAddress($fromEmail);
                $fromAddress = $emailAddress;
            } else {
                // Default 'From' address if none is provided
                $fromEmail->setAddress('no-reply@example.com');
                $fromEmail->setName('No Reply');
                $from->setEmailAddress($fromEmail);
                $fromAddress = 'no-reply@example.com';
                $logger->warning('No "From" address found. Using default "no-reply@example.com".');
            }

            // Process "To" recipients
            $toRecipientsArray = MSGraphMailApiService::processRecipients($email->getTo() ?? []);

            // Process "CC" recipients
            $ccRecipientsArray = MSGraphMailApiService::processRecipients($email->getCc() ?? []);

            // Process "BCC" recipients
            $bccRecipientsArray = MSGraphMailApiService::processRecipients($email->getBcc() ?? []);

            // Process "Reply-To" addresses
            $replyToArray = MSGraphMailApiService::processRecipients($email->getReplyTo() ?? []);

            // Determine the email body content and type
            $body = new ItemBody();
            $contentType = $email->getContentType();

            if ($contentType === 'text/html') {
                $body->setContentType(BodyType::HTML);
                $body->setContent($email->getBody());
            } else {
                $body->setContentType(BodyType::TEXT);
                $body->setContent($email->getBody());
            }

            // Initialize an array to hold file attachments
            $fileAttachments = [];

            // Iterate through each attachment in the email
            foreach ($email->getChildren() as $attachment) {
                if ($attachment instanceof Swift_Attachment) {
                    $attachmentName = $attachment->getFilename() ?: 'attachment';
                    $attachmentContentType = $attachment->getContentType() ?: 'application/octet-stream';
                    $attachmentContent = $attachment->getBody();

                    $fileAttachment = new FileAttachment();
                    $fileAttachment->setODataType("#microsoft.graph.fileAttachment");
                    $fileAttachment->setName($attachmentName);
                    $fileAttachment->setContentType($attachmentContentType);
                    $fileAttachment->setContentBytes(base64_encode($attachmentContent));

                    $fileAttachments[] = $fileAttachment;
                }
            }

            // Construct the Microsoft Graph message object
            $graphMessage = new Message();
            $graphMessage->setFrom($from);
            $graphMessage->setToRecipients($toRecipientsArray);
            $graphMessage->setCcRecipients($ccRecipientsArray);
            $graphMessage->setBccRecipients($bccRecipientsArray);
            $graphMessage->setReplyTo($replyToArray);
            $graphMessage->setSubject($email->getSubject() ?: 'No Subject');
            $graphMessage->setBody($body);

            // Attach files if any are present
            if (!empty($fileAttachments)) {
                $graphMessage->setAttachments($fileAttachments);
            }

            return [
                'message' => $graphMessage,
                'from' => $fromAddress
            ];
        } catch (Exception $e) {
            $logger->alert('Convert to MS Graph Message failed. ' . $e->getMessage());
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * Converts an associative array of recipients to an array of Microsoft Graph Recipient objects.
     *
     * @param array $addresses Associative array where keys are email addresses and values are names.
     * @return Recipient[] Array of Recipient objects.
     */
    private static function processRecipients(array $addresses): array
    {
        $recipients = [];
        foreach ($addresses as $email => $name) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($email);
            $emailAddress->setName($name ?? '');
            $recipient->setEmailAddress($emailAddress);
            $recipients[] = $recipient;
        }
        return $recipients;
    }
}
