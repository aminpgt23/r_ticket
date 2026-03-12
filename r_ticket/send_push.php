<?php
function createAccessToken($serviceAccountFile)
{
    if (!file_exists($serviceAccountFile)) {
        die("SERVICE ACCOUNT FILE NOT FOUND\n");
    }

    $json = json_decode(file_get_contents($serviceAccountFile), true);
    if (!$json) {
        die("SERVICE ACCOUNT JSON INVALID\n");
    }

    $header = ['alg'=>'RS256','typ'=>'JWT'];
    $iat = time();

    $payload = [
        "iss"   => $json["client_email"],
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud"   => "https://oauth2.googleapis.com/token",
        "iat"   => $iat,
        "exp"   => $iat + 3600
    ];

    $b64 = fn($v)=>rtrim(strtr(base64_encode(json_encode($v)),'+/','-_'),'=');

    $jwtHeader = $b64($header);
    $jwtPayload = $b64($payload);

    if (!openssl_sign("$jwtHeader.$jwtPayload", $signature, $json['private_key'], 'SHA256')) {
        die("OPENSSL SIGN FAILED\n");
    }

    $jwt = "$jwtHeader.$jwtPayload.".rtrim(strtr(base64_encode($signature),'+/','-_'),'=');

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_POSTFIELDS => http_build_query([
            "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
            "assertion"  => $jwt
        ])
    ]);

    $res = curl_exec($ch);

    if ($res === false) {
        die("CURL ERROR: ".curl_error($ch)."\n");
    }

    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $http\n";
    echo "RESPONSE: $res\n";

    $jsonRes = json_decode($res, true);
    return $jsonRes['access_token'] ?? null;
}


function sendPushV1($token,$title,$body,$ticketId)
{
    $projectId = "rticket-812d3";
    $serviceAccount = __DIR__."/service-account.json";

    $accessToken = createAccessToken($serviceAccount);
    if(!$accessToken){
        return json_encode(["error"=>"ACCESS_TOKEN_FAILED"]);
    }

    $payload = [
        "message"=>[
            "token"=>$token,

            // ⬅️ DATA ONLY MESSAGE (WAJIB)
            "data"=>[
                "title"=>$title,
                "body"=>$body,
                "ticket_id"=>(string)$ticketId,
                "click_url"=>"https://amynt.my.id/r_ticket/"
            ],

            "android"=>["priority"=>"high"],
            "webpush"=>[
                "headers"=>["Urgency"=>"high"]
            ]
        ]
    ];

    $ch = curl_init("https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
    curl_setopt_array($ch,[
        CURLOPT_POST=>1,
        CURLOPT_RETURNTRANSFER=>1,
        CURLOPT_HTTPHEADER=>[
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS=>json_encode($payload)
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}
