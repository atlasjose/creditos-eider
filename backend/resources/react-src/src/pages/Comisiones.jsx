import { useState, useEffect } from 'react';
import { getComisiones, getComisionesDelCobrador, calcularComisionAutomatico } from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { formatMoney, formatDate } from '../utils/helpers';
import Loading from '../components/Common/Loading';

const Comisiones = () => {
  const { user, isJefe } = useAuth();
  const [comisiones, setComisiones] = useState([]);
  const [loading, setLoading] = useState(true);
  const [totales, setTotales] = useState({
    pendientes: 0,
    pagadas: 0,
    total: 0,
  });

  useEffect(() => {
    loadComisiones();
  }, []);

  const loadComisiones = async () => {
    try {
      setLoading(true);
      let response;
      
      if (isJefe()) {
        // JEFE ve todas las comisiones
        response = await getComisiones();
      } else {
        // COBRADOR ve solo sus comisiones
        response = await getComisionesDelCobrador(user.id_usuario);
      }

      const comisionesData = response.data.data || [];
      setComisiones(comisionesData);

      // Calcular totales
      const pendientes = comisionesData
        .filter((c) => !c.pagada)
        .reduce((sum, c) => sum + parseFloat(c.monto_comision), 0);
      
      const pagadas = comisionesData
        .filter((c) => c.pagada)
        .reduce((sum, c) => sum + parseFloat(c.monto_comision), 0);

      setTotales({
        pendientes,
        pagadas,
        total: pendientes + pagadas,
      });
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleCalcularAutomatico = async (periodicidad) => {
    if (!confirm(`¿Calcular comisiones ${periodicidad}es para todos los cobradores?`)) {
      return;
    }

    try {
      setLoading(true);
      await calcularComisionAutomatico(periodicidad);
      alert('✅ Comisiones calculadas exitosamente');
      loadComisiones();
    } catch (err) {
      alert('❌ Error: ' + (err.response?.data?.message || 'Error al calcular comisiones'));
    } finally {
      setLoading(false);
    }
  };

  if (loading && comisiones.length === 0) {
    return <Loading fullScreen message="Cargando comisiones..." />;
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg sticky top-0 z-10">
        <div className="px-4 py-4">
          <div className="flex items-center justify-between">
            <h1 className="text-2xl font-bold">Comisiones</h1>
            <button
              onClick={() => window.history.back()}
              className="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg"
            >
              ← Volver
            </button>
          </div>
        </div>
      </header>

      <main className="px-4 py-6">
        {/* Resumen */}
        <div className="bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-lg p-6 mb-6 shadow-lg">
          <h2 className="text-lg font-bold mb-4">💰 Resumen de Comisiones</h2>
          <div className="grid grid-cols-3 gap-4">
            <div>
              <p className="text-sm text-purple-100">Pendientes</p>
              <p className="text-xl font-bold">{formatMoney(totales.pendientes)}</p>
            </div>
            <div>
              <p className="text-sm text-purple-100">Pagadas</p>
              <p className="text-xl font-bold">{formatMoney(totales.pagadas)}</p>
            </div>
            <div>
              <p className="text-sm text-purple-100">Total</p>
              <p className="text-xl font-bold">{formatMoney(totales.total)}</p>
            </div>
          </div>
        </div>

        {/* Botones de acción (solo JEFE) */}
        {isJefe() && (
          <div className="grid grid-cols-3 gap-2 mb-6">
            <button
              onClick={() => handleCalcularAutomatico('SEMANAL')}
              className="bg-blue-500 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-600 text-sm"
            >
              📅 Semanal
            </button>
            <button
              onClick={() => handleCalcularAutomatico('QUINCENAL')}
              className="bg-green-500 text-white font-semibold py-3 px-4 rounded-lg hover:bg-green-600 text-sm"
            >
              📅 Quincenal
            </button>
            <button
              onClick={() => handleCalcularAutomatico('MENSUAL')}
              className="bg-purple-500 text-white font-semibold py-3 px-4 rounded-lg hover:bg-purple-600 text-sm"
            >
              📅 Mensual
            </button>
          </div>
        )}

        {/* Filtros */}
        <div className="bg-white rounded-lg shadow-md p-4 mb-6">
          <div className="flex gap-2 overflow-x-auto">
            <button className="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm whitespace-nowrap">
              Todas
            </button>
            <button className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm whitespace-nowrap">
              Pendientes
            </button>
            <button className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm whitespace-nowrap">
              Pagadas
            </button>
          </div>
        </div>

        {/* Lista de Comisiones */}
        <h3 className="text-xl font-bold mb-4">Historial</h3>
        <div className="space-y-3">
          {comisiones.length === 0 ? (
            <div className="text-center py-12">
              <div className="text-6xl mb-4">💰</div>
              <p className="text-gray-600">No hay comisiones registradas</p>
            </div>
          ) : (
            comisiones.map((comision) => (
              <div
                key={comision.id_comision}
                className="bg-white rounded-lg shadow-md p-4"
              >
                <div className="flex justify-between items-start mb-3">
                  <div>
                    <h4 className="font-bold text-lg text-gray-800">
                      {formatMoney(comision.monto_comision)}
                    </h4>
                    <p className="text-sm text-gray-600">
                      {comision.cobrador?.nombres} {comision.cobrador?.apellidos}
                    </p>
                    <p className="text-xs text-gray-500 mt-1">
                      {comision.periodicidad} - {formatDate(comision.fecha_inicio)} a{' '}
                      {formatDate(comision.fecha_fin)}
                    </p>
                  </div>
                  <span
                    className={`px-3 py-1 rounded-full text-xs font-semibold ${
                      comision.pagada
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800'
                    }`}
                  >
                    {comision.pagada ? '✓ Pagada' : '⏳ Pendiente'}
                  </span>
                </div>

                <div className="grid grid-cols-2 gap-3 text-sm border-t pt-3">
                  <div>
                    <p className="text-gray-600">Total Cobrado</p>
                    <p className="font-semibold">
                      {formatMoney(comision.total_cobrado)}
                    </p>
                  </div>
                  <div>
                    <p className="text-gray-600">Porcentaje</p>
                    <p className="font-semibold">{comision.porcentaje_aplicado}%</p>
                  </div>
                  {comision.pagada && (
                    <>
                      <div>
                        <p className="text-gray-600">Fecha de Pago</p>
                        <p className="font-semibold text-xs">
                          {formatDate(comision.fecha_pago)}
                        </p>
                      </div>
                      <div>
                        <p className="text-gray-600">Pagado por</p>
                        <p className="font-semibold text-xs">
                          {comision.jefe_pagador?.nombres || 'N/A'}
                        </p>
                      </div>
                    </>
                  )}
                </div>

                {!comision.pagada && isJefe() && (
                  <button className="w-full mt-3 bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 text-sm font-semibold">
                    ✓ Marcar como Pagada
                  </button>
                )}
              </div>
            ))
          )}
        </div>
      </main>
    </div>
  );
};

export default Comisiones;