import axios from 'axios';
import Cookies from 'js-cookie';

const API_URL = 'http://localhost:5320/api/movies';

const movieService = {
    getAllMovies: async () => {
        const response = await axios.get(`${API_URL}?limit=10`);
        return response.data;
    },

    getMovieById: async (id) => {
        const response = await axios.get(`${API_URL}/${id}`);
        return response.data;
    },

    createMovie: async (movieData) => {
        const token = Cookies.get('token'); 
        const response = await axios.post(`${API_URL}/post`, movieData, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'multipart/form-data'
            }
        });
        return response.data;
    },

    updateMovie: async (id, movieData, headers) => {
        const token = Cookies.get('token'); 
        const response = await axios.put(`${API_URL}/${id}`, movieData, {   
            headers: {
                'Authorization': `Bearer ${token}`
            } 
        });
        return response.data;
    },

    deleteMovie: async (id, headers) => {
        const token = Cookies.get('token'); 
        const response = await axios.delete(`${API_URL}/${id}`, {   
            headers: {
                'Authorization': `Bearer ${token}`
            } 
        });
        return response.data;
    },
};

export default movieService;
