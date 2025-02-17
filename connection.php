<?php
$serverName = "localhost";
$userName = "root";
$password = "";
$conn = mysqli_connect($serverName, $userName, $password);
if (!$conn) {
    die("Failed to connect: " . mysqli_connect_error());
}

$createDatabase = "CREATE DATABASE IF NOT EXISTS prototype2";
if (!mysqli_query($conn, $createDatabase)) {
    die("Failed to create database: " . mysqli_error($conn));
}

// Select the created database
mysqli_select_db($conn, 'prototype2');

// Add a timestamp column if it doesn't already exist
$createTable = "CREATE TABLE IF NOT EXISTS weather (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pressure FLOAT NOT NULL,
    humidity FLOAT NOT NULL,
    wind FLOAT NOT NULL,
    wind_speed FLOAT NOT NULL,
    city VARCHAR(255),
    temp INT NOT NULL,
    img VARCHAR(255),
    weatherCondition VARCHAR(255),
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($conn, $createTable)) {
    die("Failed to create table: " . mysqli_error($conn));
}

// Determine the city
if (isset($_GET['q'])) {
    $cityName = $_GET['q'];
} else {
    $cityName = "Atmore";
}

// Check if weather data is available and less than 2 hours old
$selectAllData = "SELECT * FROM weather WHERE city = '$cityName' AND TIMESTAMPDIFF(HOUR, timestamp, NOW()) < 2";
$result = mysqli_query($conn, $selectAllData);

if (mysqli_num_rows($result) == 0) {
    // Fetch new data from the OpenWeather API
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$cityName&appid={your_api_key}&units=metric";
    $response = @file_get_contents($url);

    if ($response === FALSE) {
        http_response_code(404);
        die(json_encode(['error' => 'City not found']));
    }

    $data = json_decode($response, true);
    if (isset($data['main'])) {
        $humidity = $data['main']['humidity'];
        $wind = $data['wind']['speed'];
        $pressure = $data['main']['pressure'];
        $wind_speed = $data['wind']['deg'];
        $city = $data['name'];
        $temp = $data['main']['temp'];
        $weatherCondition = $data['weather'][0]['description'];
        $img = $data['weather'][0]['icon'];

        // Delete old data for the city
        $deleteOldData = "DELETE FROM weather WHERE city = '$cityName'";
        mysqli_query($conn, $deleteOldData);

        // Insert new weather data
        $insertData = "INSERT INTO weather (humidity, wind, pressure, wind_speed, city, temp, weatherCondition, img)
           VALUES ('$humidity', '$wind', '$pressure', '$wind_speed','$city','$temp','$weatherCondition', '$img')";
        if (!mysqli_query($conn, $insertData)) {
            die("Failed to insert data: " . mysqli_error($conn));
        }

        // Fetch the newly inserted data
        $result = mysqli_query($conn, "SELECT * FROM weather WHERE city = '$cityName'");
    }
}

// Fetch rows as an array
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

// Encoding fetched data to JSON and sending as response
$json_data = json_encode($rows);
header('Content-Type: application/json');
echo $json_data;
