let temp = document.querySelector(".temp");
let date = document.querySelector(".date");
let city = document.querySelector(".city");
let weatherCondition = document.querySelector(".weatherCondn");
let pressure = document.querySelector(".pressure");
let wind = document.querySelector(".wind");
let humidity = document.querySelector(".humidity");
let btn = document.querySelector("#btn");
let searchBar = document.querySelector(".searchBar");
let weatherImg = document.querySelector(".weather-img");
let weather = document.querySelector(".weather");
let error = document.querySelector(".error");
let errorMsg = document.querySelector(".error p");
let wind_Speed = document.querySelector(".wind_Speed");

// Function to Fetch Data
async function fetchData(cityName) {
    let data;
    if (navigator.onLine) {
        console.log("Online");
        try {
            let response = await fetch(`http://localhost/ISA/Prototype-2/connection.php?q=${cityName}`);
            if (response.status === 404) {
                error.style.display = "block";
                weather.style.display = "none";
                return;
            }
            data = await response.json();
            localStorage.setItem(cityName, JSON.stringify(data));
        } catch (err) {
            if (error) {
                error.style.display = "block";
                errorMsg.innerText = "City not found. Please try again...";
            }
            if (weather) weather.style.display = "none";
            return;
        }
    }else{
        console.log("Offline");
        data = JSON.parse(localStorage.getItem(cityName));
        if (!data) {
            if (error) {
                error.style.display = "block";
                errorMsg.innerText = "No data available for this city in offline mode.";
            }
            if (weather) weather.style.display = "none";
            return;
        }
    }

    // TO show Data
    city.innerText = data[0].city;
    temp.innerText = Math.round(data[0].temp) + "°C";
    pressure.innerText = data[0].pressure + " hpa";
    wind.innerText = data[0].wind + " m/s";
    humidity.innerText = data[0].humidity + " %";
    wind_Speed.innerText = data[0].wind_speed + "°";
    weatherCondition.innerText = data[0].weatherCondition;
    weatherImg.src = `https://openweathermap.org/img/wn/${data[0].img}@2x.png`;

    // TO show Date
    let time = data[0].dt || Date.now() / 1000;
    let dat = new Date(time * 1000);
    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    date.innerText = dat.toLocaleDateString('en-US', options);

    // TO hide Error Message
    error.style.display = "none";
    weather.style.display = "block";
}

fetchData("Atmore");

// Event Listener for Button
btn.addEventListener("click", () => {
    fetchData(searchBar.value);
});
