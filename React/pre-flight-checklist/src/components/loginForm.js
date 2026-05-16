// src/components/LoginForm.js

import { useState } from 'react';

const LoginForm = () => {
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');

    const handleLogin = async () => {
        try {
            if (username && password) {
                const token = '82bc1d50677d0db3b29783fd3e09c2ae8f9bf74b';
                localStorage.setItem('authToken', token);
                localStorage.setItem('username', username);
            } else {
                console.error('Please provide username and password');
            }
        } catch (error) {
            console.error('Erreur de connexion:', error);
        }
    };

    return (
        <div>
            <h2>Connexion</h2>
            <form onSubmit={handleLogin}>
                <div>
                    <label>Username</label>
                    <input type="text" value={username} onChange={(e) => setUsername(e.target.value)} />
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
                </div>
                <button type="submit">Se connecter</button>
            </form>
        </div>
    );
};

export default LoginForm;
