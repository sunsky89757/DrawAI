<?php
function generateImage($model, $prompt, $size, $apiKey, $baseUrl = null) {
    $url = $baseUrl ? rtrim($baseUrl, '/') . '/v1/images/generations' : 'https://api.openai.com/v1/images/generations';
    
    $data = [
        "model" => $model,
        "prompt" => $prompt,
        "n" => 1,
        "size" => $size
    ];

    $payload = json_encode($data);

    $headers = [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['error' => curl_error($ch)]);
        curl_close($ch);
        return;
    }

    curl_close($ch);

    return $response;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model = $_POST['model'];
    $prompt = $_POST['prompt'];
    $size = $_POST['size'];
    $apiKey = $_POST['apiKey'];
    $baseUrl = isset($_POST['apiBaseUrl']) ? $_POST['apiBaseUrl'] : null;

    $imageData = generateImage($model, $prompt, $size, $apiKey, $baseUrl);
    $data = json_decode($imageData, true);

    if (isset($data['data'])) {
        $results = [];
        foreach ($data['data'] as $item) {
            if (isset($item['url'])) {
                $results[] = ['url' => htmlspecialchars($item['url'])];
            }
        }
        echo json_encode($results);
    } else {
        echo json_encode(['error' => 'Error fetching images.']);
    }
}
?>