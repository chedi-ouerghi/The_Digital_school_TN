import React, { useEffect, useState } from 'react';
import axios from 'axios';
import './actionstyle.css';
import { Input } from 'antd';

const baseApiUrl = 'http://localhost:5320';

const { TextArea } = Input;

const StarForm = ({ token }) => {
    const [name, setName] = useState('');
    const [dateOfBirth, setDateOfBirth] = useState('');
    const [country, setCountry] = useState('');
    const [description, setDescription] = useState('');
    const [image, setImage] = useState(null);

    const handleSubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('name', name);
        formData.append('date_of_birth', new Date(dateOfBirth).toISOString().split('T')[0]);
        formData.append('country', country);
        formData.append('description', description);
        if (image) {
            formData.append('image', image);
        }

        try {
            // Création de la star
            const response = await axios.post(`${baseApiUrl}/api/stars`, formData, {
                headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'multipart/form-data' }
            });
            const newStar = response.data;
            console.log('Star created successfully:', newStar);

            // Réinitialisation des champs du formulaire
            setName('');
            setDateOfBirth('');
            setCountry('');
            setDescription('');
            setImage(null);

            // Appel API supplémentaire après la création de la star
            const additionalAPIResponse = await axios.post(
                `${baseApiUrl}/api/stars/${newStar.starId}/additional`,
                {},
                { headers: { Authorization: `Bearer ${token}` } }
            );

            console.log('Additional API response:', additionalAPIResponse.data);
        } catch (error) {
            console.error('Error creating star:', error);
        }
    };

    
         const [weatherData, setWeatherData] = useState(null);

    useEffect(() => {
        // Au chargement initial, effectue une recherche météorologique pour Tunis
        handleWeatherSearch('Tunis');
    }, []); 
  
  const handleWeatherSearch = async (location) => {
    const APIKey = '51dc6ba13cd003bf6a7e78fa33094048';
    try {
        const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(location)}&appid=${APIKey}&units=metric`);
        const data = await response.json();
        console.log("Weather Data:", data);
        if (response.ok) {
            setWeatherData(data);
        } else {
            setWeatherData(null);
        }
    } catch (error) {
        console.log("Error:", error);
        setWeatherData(null);
    }
};
const renderWeatherData = () => {
    if (weatherData && weatherData.name) {
        // Déterminer l'icône à afficher en fonction de la condition météorologique
        const iconCode = weatherData.weather[0].icon;
        const iconUrl = `http://openweathermap.org/img/wn/${iconCode}.png`;

        // Déterminer la classe CSS pour l'arrière-plan en fonction du type de temps
        const weatherMain = weatherData.weather[0].main.toLowerCase();
        let weatherClass = '';
        switch (weatherMain) {
            case 'clear':
                weatherClass = 'sunny-bg'; // Classe CSS pour le temps ensoleillé
                break;
            case 'clouds':
                weatherClass = 'cloudy-bg'; // Classe CSS pour le temps nuageux
                break;
            case 'rain':
                weatherClass = 'rainy-bg'; // Classe CSS pour la pluie
                break;
            // Ajoutez d'autres cas selon vos besoins
            default:
                weatherClass = 'default-bg'; // Classe CSS par défaut
        }

        return (
            <div className={`text-center weather-container ${weatherClass}`}>
                <h2>{weatherData.name}, {weatherData.sys.country}</h2>
                <img src={iconUrl} alt={weatherData.weather[0].description} />
                <p>{weatherData.weather[0].description}</p>
                <p>Temperature: {weatherData.main.temp}°C</p>
                <p>Feels Like: {weatherData.main.feels_like}°C</p>
                <p>Wind Speed: {weatherData.wind.speed} m/s</p>
                <p>Humidity: {weatherData.main.humidity}%</p>
            </div>
        );
    } else {
        return <p className="text-danger">Please try again!</p>;
    }
    };
    
    return (
        <div style={{ height: '88vh' }}>
            <form onSubmit={handleSubmit} id="form_post_stars" encType="multipart/form-data">
                  <h2 className="title">Post Stars</h2>

                <Input
                    className="input"
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="Name"
                    required
                />
                <input
                    className="input"
                    type="date"
                    value={dateOfBirth}
                    onChange={(e) => setDateOfBirth(e.target.value)}
                    placeholder="Date of Birth"
                    required
                />
                <Input
                    className="input"
                    type="text"
                    value={country}
                    onChange={(e) => setCountry(e.target.value)}
                    placeholder="Country"
                    required
                />
                <Input.TextArea
                    className="input"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    placeholder="Description"
                    required
                />
                <input
                    className="input"
                    type="file"
                    onChange={(e) => setImage(e.target.files[0])}
                    placeholder="Image"
                    required
                />
                <button type="submit" className="btnsave">Save</button>
            </form>

            <div className='right_section'>
                <div className="container_weather">
                    <div className="weather-card">
                        <h1 className="text-center"> Weather</h1>
                        <div id="weather-info" className="mt-4">
                            {renderWeatherData()}
                        </div>
                    </div>
                </div>
      </div>
        </div>
    );
};

export default StarForm;
