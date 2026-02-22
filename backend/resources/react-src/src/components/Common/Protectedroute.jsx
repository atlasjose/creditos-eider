import { Navigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import Loading from './Loading';

/**
 * ProtectedRoute - Protege rutas según autenticación y roles
 */
const ProtectedRoute = ({ children, roles = [] }) => {
  const { user, loading, isAuthenticated } = useAuth();

  if (loading) {
    return <Loading fullScreen message="Verificando acceso..." />;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  // Si se especifican roles, verificar que el usuario tenga uno de ellos
  if (roles.length > 0 && !roles.includes(user?.rol?.nombre_rol)) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div className="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
          <div className="text-6xl mb-4">🚫</div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">
            Acceso Denegado
          </h2>
          <p className="text-gray-600 mb-6">
            No tienes permisos para acceder a esta sección.
          </p>
          <button
            onClick={() => window.history.back()}
            className="bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-600 transition-colors w-full"
          >
            Volver
          </button>
        </div>
      </div>
    );
  }

  return children;
};

export default ProtectedRoute;