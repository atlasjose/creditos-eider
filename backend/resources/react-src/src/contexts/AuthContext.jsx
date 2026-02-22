import { createContext, useContext, useState, useEffect } from 'react';
import { login as apiLogin, logout as apiLogout, me } from '../services/api';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const loadUser = async () => {
      const token = localStorage.getItem('token');
      const storedUser = localStorage.getItem('user');

      if (token && storedUser) {
        try {
          setUser(JSON.parse(storedUser));
          const response = await me();
          setUser(response.data.data);
          localStorage.setItem('user', JSON.stringify(response.data.data));
        } catch (err) {
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          setUser(null);
        }
      }
      setLoading(false);
    };

    loadUser();
  }, []);

  const login = async (email, password) => {
    try {
      setError(null);
      setLoading(true);
      
      const response = await apiLogin(email, password);
      const { user: userData, token } = response.data.data;

      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(userData));
      setUser(userData);
      
      return { success: true };
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Error al iniciar sesión';
      setError(errorMessage);
      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      await apiLogout();
    } catch (err) {
      console.error('Error al cerrar sesión:', err);
    } finally {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      setUser(null);
    }
  };

  const updateUser = (userData) => {
    setUser(userData);
    localStorage.setItem('user', JSON.stringify(userData));
  };

  const isJefe = () => user?.rol?.nombre_rol === 'JEFE';
  const isCobrador = () => user?.rol?.nombre_rol === 'COBRADOR';
  const isVendedor = () => user?.rol?.nombre_rol === 'VENDEDOR';

  const value = {
    user,
    loading,
    error,
    login,
    logout,
    updateUser,
    isJefe,
    isCobrador,
    isVendedor,
    isAuthenticated: !!user,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe usarse dentro de AuthProvider');
  }
  return context;
};

export default AuthContext;