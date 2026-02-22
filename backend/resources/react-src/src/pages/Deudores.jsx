import { useState, useEffect } from 'react';
import { getDeudores } from '../services/api';
import { formatPhone } from '../utils/helpers';
import Loading from '../components/Common/Loading';

const Deudores = () => {
  const [deudores, setDeudores] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    loadDeudores();
  }, []);

  const loadDeudores = async () => {
    try {
      setLoading(true);
      const response = await getDeudores();
      setDeudores(response.data.data || []);
    } catch (err) {
      setError('Error al cargar deudores');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const filteredDeudores = deudores.filter((deudor) => {
    const search = searchTerm.toLowerCase();
    return (
      deudor.nombres?.toLowerCase().includes(search) ||
      deudor.apellidos?.toLowerCase().includes(search) ||
      deudor.cedula?.includes(search) ||
      deudor.codigo_deudor?.toLowerCase().includes(search)
    );
  });

  if (loading) return <Loading fullScreen message="Cargando deudores..." />;

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Header */}
      <header className="bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg sticky top-0 z-10">
        <div className="px-4 py-4">
          <div className="flex items-center justify-between mb-4">
            <h1 className="text-2xl font-bold">Deudores</h1>
            <button
              onClick={() => window.history.back()}
              className="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors"
            >
              ← Volver
            </button>
          </div>

          {/* Search */}
          <div className="relative">
            <input
              type="text"
              placeholder="Buscar por nombre, cédula o código..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full px-4 py-3 rounded-lg text-gray-800 pl-10 focus:outline-none focus:ring-2 focus:ring-white"
            />
            <svg
              className="absolute left-3 top-3.5 w-5 h-5 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
          </div>
        </div>
      </header>

      {/* Content */}
      <main className="px-4 py-6">
        {error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
            {error}
          </div>
        )}

        <div className="mb-4 text-sm text-gray-600">
          {filteredDeudores.length} deudores encontrados
        </div>

        {filteredDeudores.length === 0 ? (
          <div className="text-center py-12">
            <div className="text-6xl mb-4">🔍</div>
            <p className="text-gray-600">No se encontraron deudores</p>
          </div>
        ) : (
          <div className="space-y-3">
            {filteredDeudores.map((deudor) => (
              <div
                key={deudor.id_deudor}
                className="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow"
              >
                <div className="flex items-start justify-between">
                  <div className="flex-1">
                    <h3 className="font-bold text-lg text-gray-800">
                      {deudor.nombres} {deudor.apellidos}
                    </h3>
                    <p className="text-sm text-gray-600 mt-1">
                      {deudor.codigo_deudor}
                    </p>
                    <div className="mt-2 space-y-1">
                      <p className="text-sm text-gray-700">
                        📄 CC: {deudor.cedula}
                      </p>
                      <p className="text-sm text-gray-700">
                        📞 {formatPhone(deudor.telefono_principal)}
                      </p>
                      <p className="text-sm text-gray-700">
                        📍 {deudor.direccion}
                      </p>
                      {deudor.latitud && deudor.longitud && (
                        <p className="text-xs text-green-600">
                          ✓ GPS: {deudor.latitud.toFixed(6)}, {deudor.longitud.toFixed(6)}
                        </p>
                      )}
                    </div>
                  </div>

                  {/* GPS Icon */}
                  {deudor.latitud && deudor.longitud && (
                    <div className="ml-2">
                      <div className="bg-green-100 text-green-600 rounded-full p-2">
                        📍
                      </div>
                    </div>
                  )}
                </div>

                {/* Tarjeta info */}
                {deudor.tarjeta && (
                  <div className="mt-3 pt-3 border-t border-gray-200">
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Saldo:</span>
                      <span className="font-semibold text-red-600">
                        ${deudor.tarjeta.saldo_actual?.toLocaleString()}
                      </span>
                    </div>
                    <div className="flex justify-between text-sm mt-1">
                      <span className="text-gray-600">Límite:</span>
                      <span className="font-semibold text-gray-800">
                        ${deudor.tarjeta.limite_credito?.toLocaleString()}
                      </span>
                    </div>
                  </div>
                )}
              </div>
            ))}
          </div>
        )}
      </main>
    </div>
  );
};

export default Deudores;