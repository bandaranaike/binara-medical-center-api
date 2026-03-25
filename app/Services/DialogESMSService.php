<?php

namespace App\Services;
/**
 * DialogESMSService
 *
 * This class provides functionality to interact with the Dialog eSMS API.
 * It includes methods for sending SMS messages and checking account balance.
 *
 * @package DialogESMSIntegration
 * @version 2.0
 */
class DialogESMSService
{
    private string $apiKey;
    private string $baseUrl = "https://e-sms.dialog.lk/api/v1/message-via-url";

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Sends an SMS message to the given recipient list.
     *
     * @param array|string $recipients List of recipient phone numbers.
     * @param string $message The message body.
     * @param string $sourceAddress The sender's address.
     * @param string|null $callbackUrl Optional push notification URL.
     * @return string The result of the API request.
     */
    public function sendMessage(array|string $recipients, string $message, string $sourceAddress, ?string $callbackUrl = null): string
    {
        if (is_string($recipients)) {
            $recipients = [$recipients];
        }

        if (empty($recipients)) {
            return "Error: Recipient list cannot be empty";
        }

        if (empty($message)) {
            return "Error: Message body cannot be empty";
        }

        $recipientList = implode(",", $recipients);
        $callbackUrl = $callbackUrl ?? "https://xx/xx";

        $url = sprintf(
            "%s/create/url-campaign?esmsqk=%s&list=%s&source_address=%s&message=%s&push_notification_url=%s",
            $this->baseUrl,
            urlencode($this->apiKey),
            urlencode($recipientList),
            urlencode($sourceAddress),
            urlencode($message),
            urlencode($callbackUrl)
        );

        return $this->executeRequest($url);
    }

    /**
     * Checks the account balance for the SMS service.
     *
     * @return string The balance information or error message.
     */
    public function checkBalance(): string
    {
        $url = sprintf("%s/check/balance?esmsqk=%s", $this->baseUrl, urlencode($this->apiKey));

        $response = $this->executeRequest($url);

        if (str_contains($response, "|")) {
            list($status, $balance) = explode("|", $response, 2);
            if (trim($status) === "1") {
                return "Success - Balance: " . $balance;
            }
        }

        return $this->getResponseMessage(trim($response));
    }

    /**
     * Executes a cURL GET request.
     *
     * @param string $url The request URL.
     * @return string The response or error message.
     */
    private function executeRequest(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = 'Error: ' . curl_error($ch);
            curl_close($ch);
            return $error;
        }

        curl_close($ch);
        return $this->getResponseMessage(trim($response));
    }

    /**
     * Maps API response codes to meaningful messages.
     *
     * @param string $responseCode The response code from the API.
     * @return string The corresponding message.
     */
    private function getResponseMessage(string $responseCode): string
    {
        $messages = [
            "1" => "Success",
            "2001" => "Error occurred during campaign creation",
            "2002" => "Bad request",
            "2003" => "Empty number list",
            "2004" => "Empty message body",
            "2005" => "Invalid number list format",
            "2006" => "Not eligible to send messages via GET requests (Admin access required)",
            "2007" => "Invalid API key",
            "2008" => "Insufficient funds or package limit reached",
            "2009" => "No valid numbers after filtering",
            "2010" => "Not eligible for package consumption",
            "2011" => "Transactional error"
        ];

        return $messages[$responseCode] ?? "Unknown response: $responseCode";
    }
}
