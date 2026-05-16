import { createContext, useState, useContext } from 'react';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [token, setToken] = useState(localStorage.getItem('token') || null);
  const [role, setRole] = useState(localStorage.getItem('role') || null);

  const login = (newToken, newRole) => {
    setToken(newToken);
    setRole(newRole);
    localStorage.setItem('token', newToken); // Stockage du token dans le localStorage
    localStorage.setItem('role', newRole); // Stockage du rôle dans le localStorage
  };

  const logout = () => {
    setToken(null);
    setRole(null);
    localStorage.removeItem('token'); // Suppression du token du localStorage lors de la déconnexion
    localStorage.removeItem('role'); // Suppression du rôle du localStorage lors de la déconnexion
  };

  return (
    <AuthContext.Provider value={{ token, role, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
