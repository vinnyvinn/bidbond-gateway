<?php

namespace App\Traits;

use App\Services\InformaService;
use App\UserCache;
use App\UserSearch;

trait Searches
{


    /**
     * @param $data
     * @return mixed|void|null
     */
    function searchById($data)
    {
        $citizenship = $this->citizenFormat($data['citizenship']);

        $usercache = UserCache::where('id_number', $data['id_number'])->where('citizenship', $citizenship)->first();

        if ($usercache) {

            $usercache->valid = true;

            return $usercache;
        }

        $informaService  = new InformaService();
        $response = json_decode($informaService->searchByIdAndCitizenship($data['id_number'],$citizenship),true);
        if(!$response){
            return null;
        }

        if ($response['status'] !== "success") {

            $response['valid'] = 0;

            return $response;
        }

        $this->saveUserCache($response['data']);

        $this->saveUserSearch($response['data']);

        $response = $response['data'];

        $response['valid'] = 1;

        return $response;
    }

    function searchByPhoneNId($phone, $id_number): bool
    {
        $usercache = UserCache::where('id_number', $id_number)->first();

        if ($usercache && $usercache->phone_numbers != null) {
            if (in_array($phone, $usercache->phone_numbers) || in_array(unencodePhone($phone), $usercache->phone_numbers)) {
                return true;
            }
        }

        if(config('services.informa.phone_search_active') && $this->isSafaricomNumber($phone)){
            $informaService  = new InformaService();

            $response = json_decode($informaService->searchByPhoneAndId($phone,$id_number),true);

            if(!$response){
                return false;
            }

            if ($response["status"] !== "success") {
                if((int) $response["error"]["code"] === 11){
                    return true;
                }
                return false;
            }
        }

        $this->updateUserCachePhone(["phone_number" => $phone, "id_number" => $id_number]);

        return true;
    }

    private function isSafaricomNumber($number){
        return preg_match('/^(?:254|\+254|0)?((7(?:(?:[129][0-9])|(?:0[0-8])|(?:[9][0-9])|(?:[6][8-9])|(?:[5][7-9])|(4([0-6]|[8-9]))))|(11[0-1]))[0-9]{6}$/', $number);
    }

    function saveUserCache($data)
    {
        $citizenship = $this->citizenFormat($data['citizenship']);

        $user = UserCache::where('id_number', $data['id_number'])->where('citizenship', $citizenship)->first();

        if (!$user) {
            UserCache::create([
                "last_name" => $data['last_name'],
                "middle_name" => $data['middle_name'],
                "id_number" => $data['id_number'],
                "gender" => $data['gender'],
                "first_name" => $data['first_name'],
                "dob" => $data['date_of_birth'],
                "citizenship" => $citizenship,
            ]);
        } else {
            $user->update([
                "last_name" => $data['last_name'],
                "middle_name" => $data['middle_name'],
                "id_number" => $data['id_number'],
                "gender" => $data['gender'],
                "first_name" => $data['first_name'],
                "dob" => $data['date_of_birth'],
                "citizenship" => $citizenship,
            ]);
        }
    }

    function updateUserCachePhone($data)
    {
        $user = UserCache::where('id_number', $data['id_number'])->first();

        if (!$user) {
            return;
        }

        if ($user->phone_numbers) {

            $phoneArr = $user->phone_numbers;

            if (!in_array($data['phone_number'], $phoneArr)) {
                array_push($phoneArr, $data['phone_number']);
            }

        } else {
            $phoneArr = array($data['phone_number']);
        }

        $user->update(["phone_numbers" => $phoneArr]);

    }

    function saveUserSearch($data)
    {
        UserSearch::create([
            'id_number' => $data['id_number'],
            'citizenship' => $data['citizenship']
        ]);
    }

    /**
     * @param $data
     * @return string
     */
    function citizenFormat($data): string
    {
        return strtolower($data) == "kenyan" ? "Kenyan" : "Alien";
    }
}
