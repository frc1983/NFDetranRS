<?php
declare(strict_types=1);

namespace app\services\integration;

final class GidSoapRequestCapture extends \SoapClient
{
    public string $capturedRequest = '';
    public string $capturedLocation = '';
    public string $capturedAction = '';

    public function __doRequest(string $request, string $location, string $action, int $version, bool $oneWay = false): ?string
    {
        $this->capturedRequest = $request;
        $this->capturedLocation = $location;
        $this->capturedAction = $action;
        return '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body/></soap:Envelope>';
    }
}
