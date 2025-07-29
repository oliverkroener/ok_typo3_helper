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
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use Symfony\Component\Mailer\SentMessage;
use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Header\HeaderConsts;

class MSGraphMailApiService
{
    public function __construct() {}

    /**
     * Converts a parsed email data into a Microsoft Graph-compatible message object.
     *
     * @param Email $rawMessage The raw message to convert.
     * @return array of (message, from) Microsoft Graph-compatible message.
     */
    public static function convertToGraphMessage(SentMessage $rawMessage): array
    {
        $parser = new MailMimeParser();
        $message = $parser->parse($rawMessage->toString(), false);

        // Process "From" address
        $from = new Recipient();
        $fromEmail = new EmailAddress();

        $fromAddress = $message->getHeaderValue(HeaderConsts::FROM);

        if (!empty($fromAddress)) {
            $fromEmail->setAddress($message->getHeaderValue(HeaderConsts::FROM)); 
            $fromEmail->setName($message->getHeader(HeaderConsts::FROM)->getPersonName());
        }

        $from->setEmailAddress($fromEmail);

        // Process "To" recipients
        $toRecipientsArray = [];
        foreach ($message->getHeader(HeaderConsts::TO)->getAddresses() ?? [] as $address) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($address->getValue());
            $emailAddress->setName($address->getName());
            $recipient->setEmailAddress($emailAddress);
            $toRecipientsArray[] = $recipient;
        }

        // Process "CC" recipients
        $ccRecipientsArray = [];
        $addresses = $message->getHeader(HeaderConsts::CC)?->getAddresses() ?? [];
        foreach ($addresses as $address) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($address->getValue());
            $emailAddress->setName($address->getName());
            $recipient->setEmailAddress($emailAddress);
            $ccRecipientsArray[] = $recipient;
        }

        // Process "BCC" recipients
        $bccRecipientsArray = [];
        $addresses = $message->getHeader(HeaderConsts::BCC)?->getAddresses() ?? [];
        foreach ($addresses as $address) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($address->getValue());
            $emailAddress->setName($address->getName());
            $recipient->setEmailAddress($emailAddress);
            $bccRecipientsArray[] = $recipient;
        }

        // Process "Reply-To" address
        $replyToArray = [];
        $addresses = $message->getHeader(HeaderConsts::REPLY_TO)?->getAddresses() ?? [];
        foreach ($addresses as $address) {
            $recipient = new Recipient();
            $emailAddress = new EmailAddress();
            $emailAddress->setAddress($address->getValue());
            $emailAddress->setName($address->getName());
            $recipient->setEmailAddress($emailAddress);
            $replyToArray[] = $recipient;
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
        foreach ($message->getAllAttachmentParts() ?? [] as $attachment) {
            $attachmentName = "";

            $currentVersion = VersionNumberUtility::getNumericTypo3Version();

            $attachmentContentType = $attachment->getHeaderValue(HeaderConsts::CONTENT_TYPE);
            $contentDispositionHeader = $attachment->getHeader(HeaderConsts::CONTENT_DISPOSITION);

            if ($contentDispositionHeader !== null) {
                $attachmentName = $contentDispositionHeader->getValueFor('filename');
            }
            
            $attachmentContent = $attachment->getContent();

            $fileAttachment = new FileAttachment();
            $fileAttachment->setName($attachmentName);
            $fileAttachment->setContentType($attachmentContentType);
            $fileAttachment->setContentBytes(Utils::streamFor(base64_encode($attachmentContent)));

            // Check if this is an inline attachment by examining Content-Disposition
            $isInline = false;
            if ($contentDispositionHeader !== null) {
                $disposition = $contentDispositionHeader->getValue();
                if (stripos($disposition, 'inline') !== false) {
                    $isInline = true;
                }
            }

            // Set inline properties for Microsoft Graph API
            if ($isInline) {
                $fileAttachment->setIsInline(true);
                // Microsoft Graph will generate a Content-ID if needed
            }

            $fileAttachments[] = $fileAttachment;
        }

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

        return [ 
            'message' => $graphMessage,
            'from' => $fromAddress
        ];
    }
}
