<?php

namespace App\Repositories\Api\Quotation;

use Illuminate\Support\Facades\DB;
use App\Models\Autocomplete;
use App\Models\AutocompleteTranslate;

class AutocompleteRepository
{
    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN POR DESTINO
    |--------------------------------------------------------------------------
    |
    | Aquí se centraliza:
    | - país
    | - coordenadas centrales
    | - radius
    |
    | Para agregar nuevos destinos en el futuro:
    | solo agrega otro índice.
    |
    */

    private $destinations = [

        // Cancún
        1 => [
            "country" => "mx",
            "location" => "21.0419282,-86.8769593",
            "radius" => 400000,
            "strictbounds" => "true"
        ],

        // Los Cabos
        2 => [
            "country" => "mx",
            "location" => "22.890533,-109.916740",
            "radius" => 400000,
            "strictbounds" => "true"
        ],

        // Punta Cana / República Dominicana
        3 => [
            "country" => "do",
            "location" => "18.5601,-68.3725",
            "radius" => 400000,
            "strictbounds" => "true"
        ]
    ];

    /*
    |--------------------------------------------------------------------------
    | SEARCH DATABASE
    |--------------------------------------------------------------------------
    */

    public function searcDB($request)
    {
        $term = iconv('UTF-8', 'ASCII//TRANSLIT', $request->keyword);

        $term = preg_replace('/[^a-zA-Z0-9_ ]/', '', $term);

        $data = DB::select("
            SELECT
                aut.id,
                aut.latitude,
                aut.longitude,

                CASE
                    WHEN aut.name LIKE '%{$term}%'
                    THEN aut.name
                    ELSE aut_trans.name
                END AS name,

                aut.address

            FROM autocomplete as aut

            LEFT JOIN autocomplete_translate as aut_trans
                ON aut_trans.autocomplete_id = aut.id

            WHERE
                aut.name LIKE '%{$term}%'
                OR aut_trans.name LIKE '%{$term}%'

            LIMIT 25
        ");

        if (sizeof($data) <= 0) {
            return false;
        }

        $items = [];

        foreach ($data as $value) {

            $items[] = [
                "name" => $value->name,
                "address" => $value->address,
                "type" => "DEFAULT",

                "geo" => [
                    "lat" => $value->latitude,
                    "lng" => $value->longitude
                ]
            ];
        }

        return $items;
    }

    /*
    |--------------------------------------------------------------------------
    | MAIN SEARCH
    |--------------------------------------------------------------------------
    */

    public function search($request)
    {
        $searchDB = $this->searcDB($request);

        if ($searchDB != false) {
            return $searchDB;
        }

        $destination_id = $request->destination_id ?? 1;

        $data = $this->sendGoogle(
            $request->keyword,
            $destination_id
        );

        if ($data == false) {
            return false;
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | GOOGLE AUTOCOMPLETE
    |--------------------------------------------------------------------------
    */

    function sendGoogle($query, $destination_id = 1)
    {
        $config = $this->destinations[$destination_id]
            ?? $this->destinations[1];

        /*
        |--------------------------------------------------------------------------
        | GOOGLE AUTOCOMPLETE API
        |--------------------------------------------------------------------------
        */

        $urlAutocomplete =
            'https://maps.googleapis.com/maps/api/place/autocomplete/json?' .
            'input=' . urlencode($query) .
            '&components=country:' . $config['country'] .
            '&location=' . $config['location'] .
            '&radius=' . $config['radius'] .
            '&strictbounds=' . $config['strictbounds'] .
            '&key=' . config('services.maps.key');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $urlAutocomplete);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $responseAutocomplete = curl_exec($ch);

        curl_close($ch);

        $responseDataAutocomplete = json_decode(
            $responseAutocomplete,
            true
        );

        $items = [];

        if (
            isset($responseDataAutocomplete['status']) &&
            $responseDataAutocomplete['status'] == "OK"
        ) {

            if (
                isset($responseDataAutocomplete['predictions']) &&
                sizeof($responseDataAutocomplete['predictions']) > 0
            ) {

                foreach (
                    $responseDataAutocomplete['predictions']
                    as $valueP
                ) {

                    $id = $valueP['place_id'];

                    $name =
                        $valueP['structured_formatting']['main_text'];

                    $address =
                        $valueP['structured_formatting']['secondary_text'];

                    /*
                    |--------------------------------------------------------------------------
                    | GOOGLE PLACE DETAILS
                    |--------------------------------------------------------------------------
                    */

                    $urlDetails =
                        'https://maps.googleapis.com/maps/api/place/details/json?' .
                        'place_id=' . $id .
                        '&fields=geometry' .
                        '&key=' . config('services.maps.key');

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $urlDetails);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    $responseDetails = curl_exec($ch);

                    curl_close($ch);

                    $responseDataDetails = json_decode(
                        $responseDetails,
                        true
                    );

                    if (
                        isset($responseDataDetails['status']) &&
                        $responseDataDetails['status'] == "OK"
                    ) {

                        if (
                            isset(
                                $responseDataDetails['result']['geometry']['location']
                            )
                        ) {

                            $items[] = [
                                "name" => $name,
                                "address" => $address,
                                "type" => "GCP",

                                "geo" => [
                                    "lat" =>
                                        $responseDataDetails['result']['geometry']['location']['lat'],

                                    "lng" =>
                                        $responseDataDetails['result']['geometry']['location']['lng'],
                                ]
                            ];
                        }
                    }
                }

                return $items;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | LEGACY GOOGLE SEARCH
    |--------------------------------------------------------------------------
    */

    public function legacySend($keyword = '')
    {
        $api_url =
            "https://maps.googleapis.com/maps/api/place/textsearch/json?" .
            "query=" . urlencode($keyword) .
            "&location=21.0442704,-86.8747223" .
            "&radius=400" .
            "&key=" . config('services.maps.key');

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        if ($response === false) {

            curl_close($curl);

            return false;
        }

        $data = json_decode($response, true);

        if ($data['status'] != "OK") {
            return false;
        }

        return $data['results'];
    }

    /*
    |--------------------------------------------------------------------------
    | AWS SEARCH (NO USADO ACTUALMENTE)
    |--------------------------------------------------------------------------
    */

    public function sendAws($keyword = '')
    {
        return false;
    }
}
