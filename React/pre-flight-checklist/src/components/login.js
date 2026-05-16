import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Group4 from '../assets/Group 4.png';
import './signup-login.css';

const Login = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleLogin = async () => {
    try {
      
      
      if (username && password) {
        
        const token = '82bc1d50677d0db3b29783fd3e09c2ae8f9bf74b';
        localStorage.setItem('authToken', token);
        localStorage.setItem('username', username);
        navigate('/dashboard'); 
      } else {
        setError('Please enter username and password');
      }
    } catch (error) {
      console.error('Error logging in:', error);
      setError('Login failed. Please try again.');
    }
  };

  return (
    <div className="flex min-h-screen bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600">
      {/* Image Section */}
      <div className="hidden md:flex md:w-1/2 items-center justify-center p-8">
        <div className="bg-white/10 backdrop-blur-md rounded-3xl p-8">
          <img src={Group4} alt="Log in" className="w-full h-auto rounded-2xl shadow-2xl" />
        </div>
      </div>

      {/* Form Section */}
      <div className="flex flex-col justify-center items-center md:w-1/2 w-full p-6">
        <div className="w-full max-w-md">
          {/* Header */}
          <div className="text-center mb-8">
            <h1 className="text-4xl font-bold text-white mb-2">Welcome Back</h1>
            <p className="text-white/80">Sign in to your account</p>
          </div>

          {/* Card */}
          <div className="bg-white rounded-2xl shadow-2xl p-8">
            {error && (
              <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p className="text-red-600 text-sm font-medium">{error}</p>
              </div>
            )}

            <form
              onSubmit={(e) => {
                e.preventDefault();
                handleLogin();
              }}
            >
              <div className="mb-6">
                <label className="block text-gray-700 text-sm font-semibold mb-3" htmlFor="username">
                  Username
                </label>
                <input
                  className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:outline-none transition duration-200"
                  id="username"
                  type="text"
                  placeholder="Enter your username"
                  value={username}
                  onChange={(e) => {
                    setUsername(e.target.value);
                    setError('');
                  }}
                  required
                />
              </div>

              <div className="mb-6">
                <label className="block text-gray-700 text-sm font-semibold mb-3" htmlFor="password">
                  Password
                </label>
                <input
                  className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:outline-none transition duration-200"
                  id="password"
                  type="password"
                  placeholder="Enter your password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>

              <button
                className="w-full btn-primary mb-4"
                type="submit"
              >
                Sign In
              </button>
            </form>

            <div className="text-center mt-6">
              <p className="text-gray-600 text-sm">
                Don't have an account? <a href="/signup" className="text-indigo-600 font-semibold hover:text-indigo-700">Sign up</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Login;