import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../services/AuthContext';

const ProtectedRoute = ({ element: Component, ...rest }) => {
  const { token, role } = useAuth();

  if (!token || role !== 'admin') {
    return <Navigate to="/not-found" />;
  }

  return <Component {...rest} />;
};

export default ProtectedRoute;
