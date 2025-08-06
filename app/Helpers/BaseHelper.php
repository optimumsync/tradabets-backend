<?php

namespace App\Helpers;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;

class BaseHelper
{
    public static function generateToken()
    {
        return md5(rand(1, 10) . microtime());
    }

    public static function sendGridMail($emailId, $content, $from_name, $subject)
    {

        try {
            // Get Brevo API key from environment
            $apiKey = '';

            if (empty($apiKey)) {
                \Log::error('BaseHelper: Brevo API key is missing');
                return false;
            }

            // Configure API key authorization
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', $apiKey);

            $apiInstance = new TransactionalEmailsApi(
                null,
                $config
            );

            // Use MAIL_FROM_ADDRESS from .env file or fallback to default
            $fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@tradabets.com');
            $fromName = env('MAIL_FROM_NAME', 'Tradabets');
            

            $sendSmtpEmail = new SendSmtpEmail();
            $sendSmtpEmail->setTo([['email' => $emailId, 'name' => $from_name]]);
            $sendSmtpEmail->setHtmlContent($content);
            $sendSmtpEmail->setSubject($subject);

            // Create sender object as required by Brevo SDK
            $sender = new \Brevo\Client\Model\SendSmtpEmailSender([
                'name' => $fromName,
                'email' => $fromEmail
            ]);
            $sendSmtpEmail->setSender($sender);
           
            // Add headers to improve deliverability
            $headers = new \stdClass();
            $headers->{'X-Mailer'} = 'Brevo';
            $headers->{'List-Unsubscribe'} = '<mailto:unsubscribe@tradabets.com>';
            $sendSmtpEmail->setHeaders($headers);

            // --- Enhanced Logging ---
            \Log::info("BaseHelper: Sending email to: {$emailId}, from: {$fromEmail}, subject: {$subject}");
            \Log::info("BaseHelper: Email content (first 100 chars): " . substr($content, 0, 100) . "...");
            try{
                $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            }
            catch(\Exception $e){
                 dd($e->getMessage());
                 \Log::error("BaseHelper: Brevo API call Exception: " . $e->getMessage());
                 \Log::error("BaseHelper: Brevo API call Stack trace: " . $e->getTraceAsString());
                 return false;
            }


            \Log::info("BaseHelper: Brevo API response status code: " . $result->getStatusCode());
            \Log::info("BaseHelper: Brevo API response body: " . json_encode($result->getData()));
            \Log::info("BaseHelper: Brevo email sent to {$emailId}. Message ID: " . $result->getMessageId());

            return true;

        } catch (\Exception $e) {
            // dd($e->getMessage());
            \Log::error('BaseHelper: Main try-catch Brevo Exception: ' . $e->getMessage());
            \Log::error('BaseHelper: Main try-catch Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}