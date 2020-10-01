<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Log;

class TenderController extends Controller
{
    public function tendersokolistings($type)
    {

        $client = new Client();

        $url = config("services.soko.url"). "/". $type. "/". "?token=".config("services.soko.token");

        try{ 
            $response = $client->get($url);

            $data = json_decode($response->getBody());
            return response()->json($data);
        } catch (ClientException $e){ 
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e){ 
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
