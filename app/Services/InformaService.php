<?php


namespace App\Services;


use App\Traits\ConsumesExternalService;

class InformaService
{
    use ConsumesExternalService;

    public $baseUri;
    public $secret;
    protected $headers;

    public function __construct()
    {
        $this->secret = config("services.informa.key");
        $this->baseUri = config("services.informa.url");
        $this->headers = [
            'Accept' => 'application/vnd.informa.v1+json',
        ];
    }

    public function searchByPhoneAndId($phone, $id_number)
    {
        return $this->performRequest('GET',
            "/search/person/byPhone?phone=" . unencodePhone($phone) . "&associated_id=" . $id_number,
            null, $this->headers
        );
    }

    public function searchByIdAndCitizenship($id_number, $citizenship)
    {
        return $this->performRequest('GET',
            "/search/person/byID?id_number=" . $id_number . "&citizenship=" . $citizenship,
            null, $this->headers
        );
    }

}
