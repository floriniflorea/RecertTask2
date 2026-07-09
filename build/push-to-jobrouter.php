<?php

new JobLinkForGit($argv[1], $argv[2], $argv[3], $argv[4], $argv[5]);

class JobLinkForGit
{
    private string $token;
    private string $baseUrl;

    public function __construct(
        private readonly string $processName,
        private readonly int $version,
        string $jrBaseRestURL,
        private readonly string $jrUserName,
        private readonly string $jrPassword,
    ) {
        if (!$this->processName || !$this->version || !$jrBaseRestURL || !$this->jrUserName || !$this->jrPassword) {
            error_log("Insufficient parameters.", 0);
            if (!$jrBaseRestURL) error_log("Please define a variable/secret 'JR_URL'", 0);
            if (!$this->jrUserName) error_log("Please define a variable/secret 'JR_USERNAME'", 0);
            if (!$this->jrPassword) error_log("Please define a variable/secret 'JR_PASSWORD'", 0);
            exit(1);
        }

        $this->baseUrl = rtrim($jrBaseRestURL, '/');
        $this->generateTokenForUser($this->jrUserName, $this->jrPassword);
        $this->initSynchronization();
    }

    private function sendRequest(string $url, array $headers, ?string $body = null): array
    {
        $ch = curl_init();
        if ($ch === false) {
            error_log("Failed to initialize cURL.", 0);
            exit(1);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            error_log("cURL error: " . curl_error($ch), 0);
            curl_close($ch);
            exit(1);
        }

        curl_close($ch);

        return [$code, $response];
    }

    private function generateTokenForUser(string $username, string $password): void
    {
        $headers = ['Accept: application/json', 'Content-Type: application/json'];
        $body = json_encode(['username' => $username, 'password' => $password]);

        [$code, $response] = $this->sendRequest(
            $this->baseUrl . "/api/rest/v2/application/tokens",
            $headers,
            $body
        );

        if ($code !== 201) {
            error_log("Authentication failed. HTTP $code: $response", 0);
            exit(1);
        }

        $this->token = json_decode($response, true)['tokens'][0];
    }

    private function initSynchronization(): void
    {
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Jobrouter-Authorization: Bearer ' . $this->token,
        ];

        [$code, $response] = $this->sendRequest(
            $this->baseUrl . "/api/rest/v2/designer/process/{$this->processName}/{$this->version}/git/pull",
            $headers
        );

        if ($code !== 200) {
            error_log("Synchronization failed. HTTP $code: $response", 0);
            exit(1);
        }
    }
}