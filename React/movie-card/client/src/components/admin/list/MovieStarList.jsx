import React, { useState, useEffect } from 'react';
import movieStarService from '../../../services/movieStarService';
import { Link } from 'react-router-dom';
import { Select, Input } from 'antd';

const { Option } = Select;

const MovieStarList = ({token}) => {
    const [movieStars, setMovieStars] = useState([]);
    const [filterOption, setFilterOption] = useState('');
    const [filteredMovieStars, setFilteredMovieStars] = useState([]);
    const [titleFilter, setTitleFilter] = useState('');
    const [nameFilter, setNameFilter] = useState('');

    useEffect(() => {
        const fetchMovieStars = async () => {
            try {
                let fetchedMovieStars;

                if (filterOption === 'top_stars') {
                    fetchedMovieStars = await movieStarService.getTopMovieStars();
                } else {
                    fetchedMovieStars = await movieStarService.getAllMovieStars();
                }

                setMovieStars(fetchedMovieStars);
                setFilteredMovieStars(fetchedMovieStars);
            } catch (error) {
                console.error('Error fetching movie stars:', error);
            }
        };

        fetchMovieStars();
    }, [filterOption]);

    useEffect(() => {
        const filteredStars = movieStars.filter(star => {
            if (filterOption === 'title_movies') {
                return star.movie_title && star.movie_title.toLowerCase().includes(titleFilter.toLowerCase());
            } else if (filterOption === 'movieStarName') {
                return star.star_name && star.star_name.toLowerCase().includes(nameFilter.toLowerCase());
            } else {
                return true;
            }
        });

        setFilteredMovieStars(filteredStars);
    }, [movieStars, filterOption, titleFilter, nameFilter]);

    return (
        <div style={{ width: '75%', margin: 'auto', height:'88vh', overflowY:'auto' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem', height: '8vh', width: '100%' }}>
                <h2 style={{ color: 'white', margin: 0 }}>Movie Star Associations</h2>
                <div>
                    <Select value={filterOption} onChange={(value) => setFilterOption(value)} style={{ width: 200, marginRight: '0.5rem' }}>
                        <Option value="">-- Filtrer par --</Option>
                        <Option value="top_stars">Top Stars</Option>
                        <Option value="title_movies">Titre du film</Option>
                        <Option value="movieStarName">Nom de la star</Option>
                    </Select>
                    {filterOption === 'title_movies' && (
                        <Input placeholder="Titre du film" value={titleFilter} onChange={(e) => setTitleFilter(e.target.value)} style={{ width: 200, marginRight: '0.5rem' }} />
                    )}
                    {filterOption === 'movieStarName' && (
                        <Input placeholder="Nom de la star" value={nameFilter} onChange={(e) => setNameFilter(e.target.value)} style={{ width: 200, marginRight: '0.5rem' }} />
                    )}
                </div>
                <button style={{ width: '15%', height: '60%', background: '#fff', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', borderRadius: '5px', border: 'none', cursor: 'pointer', margin: '0' }}>
                    <Link to='/admin/post-movie' style={{ fontSize: '15px', textDecoration: 'none', color: '#000' }}>post new Movie Star</Link>
                </button>
            </div>
            <div style={{ overflowY: 'auto', height: '70vh' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse', borderSpacing: '0', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}>
                    <thead style={{ backgroundColor: '#f5f5f5' }}>
                        <tr>
                            <th style={{ padding: '10px', textAlign: 'left' }}>Movie Tile</th>
                            <th style={{ padding: '10px', textAlign: 'left' }}>Star Name</th>
                            {filterOption === 'top_stars' && <th style={{ padding: '10px', textAlign: 'left' }}>Num Movies</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {filteredMovieStars.map((movieStar, index) => (
                            <tr key={index} style={{ borderBottom: '1px solid #ddd', backgroundColor: 'rgb(169 184 196)' }}>
                                <td style={{ padding: '10px', textAlign: 'left' }}>{movieStar.movie_title || movieStar.title_movies}</td>
                                <td style={{ padding: '10px', textAlign: 'left' }}>{movieStar.star_name || movieStar.name}</td>
                                {filterOption === 'top_stars' && <td style={{ padding: '10px', textAlign: 'left' }}>{movieStar.num_movies}</td>}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default MovieStarList;
