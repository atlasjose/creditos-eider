import { useAuth } from '../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';

const Dashboard = () => {
  const { user, logout, isJefe, isCobrador, isVendedor } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Header */}
      <header className="bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg">
        <div className="max-w-7xl mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-2xl font-bold">Créditos Eider</h1>
              <p className="text-sm text-blue-100">
                {user?.nombres} {user?.apellidos} - {user?.rol?.nombre_rol}
              </p>
            </div>
            <button
              onClick={handleLogout}
              className="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors"
            >
              Salir
            </button>
          </div>
        </div>
      </header>

      {/* Content */}
      <main className="max-w-7xl mx-auto px-4 py-6">
        <h2 className="text-2xl font-bold text-gray-800 mb-6">
          Panel de Control
        </h2>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {/* Card: Deudores */}
          <div className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-800">Deudores</h3>
              <span className="text-3xl">👥</span>
            </div>
            <p className="text-gray-600 text-sm">Ver lista de deudores</p>
          </div>

          {/* Card: Mapa */}
          <div className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-800">Mapa</h3>
              <span className="text-3xl">🗺️</span>
            </div>
            <p className="text-gray-600 text-sm">Ver deudores en mapa</p>
          </div>

          {/* Card: Pagos (solo COBRADOR y JEFE) */}
          {(isCobrador() || isJefe()) && (
            <div className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-800">Pagos</h3>
                <span className="text-3xl">💰</span>
              </div>
              <p className="text-gray-600 text-sm">Registrar pagos</p>
            </div>
          )}

          {/* Card: Ventas (solo VENDEDOR y JEFE) */}
          {(isVendedor() || isJefe()) && (
            <div className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-800">Ventas</h3>
                <span className="text-3xl">🛒</span>
              </div>
              <p className="text-gray-600 text-sm">Registrar ventas</p>
            </div>
          )}

          {/* Card: Comisiones (solo COBRADOR y JEFE) */}
          {(isCobrador() || isJefe()) && (
            <div className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-800">Comisiones</h3>
                <span className="text-3xl">📊</span>
              </div>
              <p className="text-gray-600 text-sm">Ver comisiones</p>
            </div>
          )}

          {/* Card: Productos (solo JEFE) */}
          {isJefe() && (
            <div className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-800">Productos</h3>
                <span className="text-3xl">📦</span>
              </div>
              <p className="text-gray-600 text-sm">Gestionar productos</p>
            </div>
          )}

          {/* Card: Rutas (solo JEFE) */}
          {isJefe() && (
            <div className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-800">Rutas</h3>
                <span className="text-3xl">🛣️</span>
              </div>
              <p className="text-gray-600 text-sm">Gestionar rutas</p>
            </div>
          )}

          {/* Card: Usuarios (solo JEFE) */}
          {isJefe() && (
            <div className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-800">Usuarios</h3>
                <span className="text-3xl">👤</span>
              </div>
              <p className="text-gray-600 text-sm">Gestionar usuarios</p>
            </div>
          )}
        </div>
      </main>
    </div>
  );
};

export default Dashboard;