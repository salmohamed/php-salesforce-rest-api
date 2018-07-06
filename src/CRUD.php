<?php

namespace bjsmasth\Salesforce;

use GuzzleHttp\Client;

class CRUD
{
    protected $instance_url;
    protected $access_token;

    public function __construct()
    {
        if ((!isset($_SESSION) && !isset($_SESSION['salesforce'])) && !$this->hasValidCookie()) {
            throw new \Exception('Access Denied', 403);
        }
        if (!$this->hasValidCookie()) {
            $this->instance_url = $_SESSION['salesforce']['instance_url'];
            $this->access_token = $_SESSION['salesforce']['access_token'];
        }
    }

    /**
     * @return bool
     */
    protected function hasValidCookie()
    {
        if (isset($_COOKIE['access_token']) && isset($_COOKIE['instance_url'])) {
            $this->instance_url = $_COOKIE['instance_url'];
            $this->access_token = $_COOKIE['access_token'];
            return true;
        }
        return false;
    }

    /**
     * @param $query
     * @return mixed
     */

    public function query($query)
    {
        $url = "$this->instance_url/services/data/v39.0/query";

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token"
            ],
            'query' => [
                'q' => $query
            ]
        ]);

        return json_decode($request->getBody(), true);
    }

    public function create($object, array $data)
    {
        $url = "$this->instance_url/services/data/v39.0/sobjects/$object/";

        $client = new Client();

        $request = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 201) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        $response = json_decode($request->getBody(), true);
        $id = $response["id"];

        return $id;

    }

    /**
     * @param $object
     * @param array $data
     * @return mixed
     */
    public function createTree($object, array $data)
    {
        $url = "$this->instance_url/services/data/v39.0/composite/tree/$object/";

        $client = new Client();

        $request = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 201) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        $response = json_decode($request->getBody(), true);

        return $response["results"];
    }

    /**
     * @param $object
     * @param $id
     * @param array $data
     * @return int
     */
    public function update($object, $id, array $data)
    {
        $url = "$this->instance_url/services/data/v39.0/sobjects/$object/$id";

        $client = new Client();

        $request = $client->request('PATCH', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 204) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        return $status;
    }

    public function delete($object, $id)
    {
        $url = "$this->instance_url/services/data/v39.0/sobjects/$object/$id";

        $client = new Client();
        $request = $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
            ]
        ]);

        $status = $request->getStatusCode();

        if ($status != 204) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        return true;
    }

    /**
     * @param $data
     * @param $boundary
     * @return mixed
     */
    public function upload($data, $boundary)
    {
        $url = "$this->instance_url/services/data/v42.0/sobjects/ContentVersion/";

        $client = new Client();

        $request = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
                'Content-type' => 'multipart/form-data; boundary=' . $boundary . ''
            ],
            'body' => $data
        ]);
        $status = $request->getStatusCode();

        if ($status != 201) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        $response = json_decode($request->getBody(), true);

        return $response;
    }

    /**
     * @param $objectId
     * @return string
     */
    public function download($objectId)
    {
        $url = "$this->instance_url/services/data/v42.0/sobjects/ContentVersion/$objectId/VersionData";

        $client = new Client();

        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token"
            ]
        ]);
        $status = $request->getStatusCode();
        if ($status != 200) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }
        return $request->getBody()->getContents();
    }
}