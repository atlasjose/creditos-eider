import { useState, useEffect } from 'react';
import { getPagos, createPago, getResumenDia } from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { formatMoney, formatDate } from '../utils/helpers';
import Loading from '../components/Common/Loading';

const Pagos = () => {
  const { user } = useAuth();
  const [pagos, setPagos] = useState([]);
  const [resumen, setResumen] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  
  // Form state
  const [formData, setFormData] = useState({
    id_cuota: '',
    monto_abonado: '',
    metodo_pago: 'EFECTIVO',
  });

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [pagosRes, resumenRes] = await Promise.all([
        getPagos({ limit: 20 }),
        getResumenDia(),
      ]);
      setPagos(pagosRes.data.data || []);
      setResumen(resumenRes.data.data || null);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      await createPago(formData);
      alert('✅ Pago registrado exitosamente');
      setFormData({ id_cuota: '', monto_abonado: '', metodo_pago: 'EFECTIVO' });
      setShowForm(false);
      loadData();
    } catch (err) {
      alert('❌ Error: ' + (err.response?.data?.message || 'Error al registrar pago'));
    } finally {
      setLoading(false);
    }
  };

  if (loading && pagos.length === 0) {
    return <Loading fullScreen message="Cargando pagos..." />;
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg sticky top-0 z-10">
        <div className="px-4 py-4">
          <div className="flex items-center justify-between">
            <h1 className="text-2xl font-bold">Pagos</h1>
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
        {/* Resumen del día */}
        {resumen && (
          <div className="bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-lg p-6 mb-6 shadow-lg">
            <h2 className="text-lg font-bold mb-4">📊 Resumen del Día</h2>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-green-100">Total Pagos</p>
                <p className="text-2xl font-bold">{resumen.total_pagos || 0}</p>
              </div>
              <div>
                <p className="text-sm text-green-100">Recaudado</p>
                <p className="text-2xl font-bold">{formatMoney(resumen.total_recaudado || 0)}</p>
              </div>
              <div>
                <p className="text-sm text-green-100">Comisión</p>
                <p className="text-xl font-bold">{formatMoney(resumen.comision_generada || 0)}</p>
              </div>
              <div>
                <p className="text-sm text-green-100">Efectivo</p>
                <p className="text-xl font-bold">
                  {formatMoney(resumen.metodos_pago?.EFECTIVO?.monto || 0)}
                </p>
              </div>
            </div>
          </div>
        )}

        {/* Botón Registrar */}
        <button
          onClick={() => setShowForm(!showForm)}
          className="w-full bg-blue-500 text-white font-bold py-4 rounded-lg mb-6 hover:bg-blue-600 shadow-lg"
        >
          {showForm ? '❌ Cancelar' : '➕ Registrar Pago'}
        </button>

        {/* Formulario */}
        {showForm && (
          <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 className="text-xl font-bold mb-4">Nuevo Pago</h3>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-2">ID Cuota</label>
                <input
                  type="number"
                  value={formData.id_cuota}
                  onChange={(e) => setFormData({ ...formData, id_cuota: e.target.value })}
                  className="w-full px-4 py-3 border rounded-lg"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Monto</label>
                <input
                  type="number"
                  value={formData.monto_abonado}
                  onChange={(e) => setFormData({ ...formData, monto_abonado: e.target.value })}
                  className="w-full px-4 py-3 border rounded-lg"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Método de Pago</label>
                <select
                  value={formData.metodo_pago}
                  onChange={(e) => setFormData({ ...formData, metodo_pago: e.target.value })}
                  className="w-full px-4 py-3 border rounded-lg"
                >
                  <option value="EFECTIVO">Efectivo</option>
                  <option value="TRANSFERENCIA">Transferencia</option>
                  <option value="DATAFONO">Datafonó</option>
                  <option value="OTRO">Otro</option>
                </select>
              </div>

              <button
                type="submit"
                className="w-full bg-green-500 text-white font-bold py-3 rounded-lg hover:bg-green-600"
              >
                ✅ Registrar Pago
              </button>
            </form>
          </div>
        )}

        {/* Lista de Pagos */}
        <h3 className="text-xl font-bold mb-4">Últimos Pagos</h3>
        <div className="space-y-3">
          {pagos.map((pago) => (
            <div key={pago.id_pago} className="bg-white rounded-lg shadow p-4">
              <div className="flex justify-between items-start mb-2">
                <div>
                  <p className="font-bold text-lg">{formatMoney(pago.monto_abonado)}</p>
                  <p className="text-sm text-gray-600">Cuota #{pago.id_cuota}</p>
                </div>
                <span className="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full">
                  {pago.metodo_pago}
                </span>
              </div>
              <div className="text-xs text-gray-500">
                {formatDate(pago.fecha_pago)}
              </div>
              {pago.comision_generada > 0 && (
                <div className="mt-2 text-xs text-green-600">
                  💰 Comisión: {formatMoney(pago.comision_generada)}
                </div>
              )}
            </div>
          ))}
        </div>
      </main>
    </div>
  );
};

export default Pagos;