import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { Input, message } from 'antd';
import "../auth/auth.css";
import { useAuth } from '../../../services/AuthContext';

const Login = () => {
   const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

const handleSubmit = async (e) => {
  e.preventDefault();
  setError('');

  try {
    const response = await axios.post('http://localhost:5320/api/auth/login', { email, password });
    const { token, user } = response.data;

    login(token, user.role); 
    navigate(`/profile/${user.id}`);
    message.success('Connexion réussie');
  } catch (err) {
    if (err.response && err.response.data && err.response.data.message) {
      setError(err.response.data.message);
    } else {
      setError('Une erreur est survenue lors de la connexion');
    }
  }
};


  return (
    <div style={{ height: '93vh' }}>
      <div className="background">
        <div className="shape" />
        <div className="shape" />
      </div>
      {error && <div className="error">{error}</div>}
      <form onSubmit={handleSubmit} className='form-login'>
        <div className='div1_form'>
          <label className='label_login'>Email:</label>
          <Input type="email" name='email' value={email} onChange={(e) => setEmail(e.target.value)} />
        </div>
        <div className='div1_form'>
          <label className='label_login'>Password:</label>
          <Input type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
        </div>
        <button type="submit">Login</button>
        <div className="social">
          <div className="go">
            <i className="fab fa-google" /> Google
          </div>
          <div className="fb">
            <i className="fab fa-facebook" /> Facebook
          </div>
        </div>
      </form>
    </div>
  );
};

export default Login;
