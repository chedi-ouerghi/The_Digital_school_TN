import axios from 'axios';

const instance = axios.create({
    baseURL: 'https://greenvelvet.alwaysdata.net/pfc',
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
    }
});


instance.interceptors.request.use(
    (config) => {
        
        const token = localStorage.getItem('authToken');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
            config.headers.token = token; 
        }
        return config;
    },
    (error) => {
        
        return Promise.reject(error);
    }
);


instance.interceptors.response.use(
    (response) => {
        
        return response;
    },
    (error) => {
        
        return Promise.reject(error);
    }
);

export default instance;