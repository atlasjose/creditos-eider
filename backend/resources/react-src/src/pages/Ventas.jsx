import { useState, useEffect } from 'react';
import { getVentas, createVenta, getProductos } from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { formatMoney, formatDate } from '../utils/helpers';
import Loading from '../components/Common/Loading';

const Ventas = () => {
  const { user } = useAuth();
  const [ventas, setVentas] = useState([]);
  const [productos, setProductos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  
  const [formData, setFormData] = useState({
    id_tarjeta: '',
    id_producto: '',
    cantidad: 1,
    modalidad: 'SEMANAL',
    cuotas: 12,
  });

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [ventasRes, productosRes] = await Promise.all([
        getVentas({ limit: 20 }),
        getProductos({ activo: true }),
      ]);
      setVentas(ventasRes.data.data || []);
      setProductos(productosRes.data.data || []);
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
      await createVenta(formData);
      alert('✅ Venta registrada exitosamente');
      setFormData({
        id_tarjeta: '',
        id_producto: '',
        cantidad: 1,
        modalidad: 'SEMANAL',
        cuotas: 12,
      });
      setShowForm(false);
      loadData();
    } catch (err) {
      alert('❌ Error: ' + (err.response?.data?.message || 'Error al registrar venta'));
    } finally {
      setLoading(false);
    }
  };

  const productoSeleccionado = productos.find(
    (p) => p.id_producto === Number(formData.id_producto)
  );

  const calcularMontoTotal = () => {
    if (!productoSeleccionado) return 0;
    return productoSeleccionado.precio_venta * formData.cantidad;
  };

  if (loading && ventas.length === 0) {
    return <Loading fullScreen message="Cargando ventas..." />;
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg sticky top-0 z-10">
        <div className="px-4 py-4">
          <div className="flex items-center justify-between">
            <h1 className="text-2xl font-bold">Ventas</h1>
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
        {/* Botón Nueva Venta */}
        <button
          onClick={() => setShowForm(!showForm)}
          className="w-full bg-gradient-to-r from-green-500 to-teal-600 text-white font-bold py-4 rounded-lg mb-6 hover:from-green-600 hover:to-teal-700 shadow-lg"
        >
          {showForm ? '❌ Cancelar' : '🛒 Nueva Venta'}
        </button>

        {/* Formulario */}
        {showForm && (
          <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 className="text-xl font-bold mb-4">Registrar Venta</h3>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-2">ID Tarjeta</label>
                <input
                  type="number"
                  value={formData.id_tarjeta}
                  onChange={(e) => setFormData({ ...formData, id_tarjeta: e.target.value })}
                  className="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  placeholder="Número de tarjeta del deudor"
                  required
                />
                <p className="text-xs text-gray-500 mt-1">
                  Tarjeta de crédito del cliente
                </p>
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Producto</label>
                <select
                  value={formData.id_producto}
                  onChange={(e) => setFormData({ ...formData, id_producto: e.target.value })}
                  className="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Seleccionar producto...</option>
                  {productos.map((p) => (
                    <option key={p.id_producto} value={p.id_producto}>
                      {p.nombre_producto} - {formatMoney(p.precio_venta)}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Cantidad</label>
                <input
                  type="number"
                  min="1"
                  value={formData.cantidad}
                  onChange={(e) => setFormData({ ...formData, cantidad: Number(e.target.value) })}
                  className="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Modalidad de Pago</label>
                <select
                  value={formData.modalidad}
                  onChange={(e) => setFormData({ ...formData, modalidad: e.target.value })}
                  className="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                  <option value="DIARIO">Diario</option>
                  <option value="SEMANAL">Semanal</option>
                  <option value="QUINCENAL">Quincenal</option>
                  <option value="MENSUAL">Mensual</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Número de Cuotas</label>
                <input
                  type="number"
                  min="1"
                  max="36"
                  value={formData.cuotas}
                  onChange={(e) => setFormData({ ...formData, cuotas: Number(e.target.value) })}
                  className="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  required
                />
                <p className="text-xs text-gray-500 mt-1">
                  Máximo 36 cuotas
                </p>
              </div>

              {/* Resumen */}
              {productoSeleccionado && (
                <div className="bg-blue-50 rounded-lg p-4 border border-blue-200">
                  <h4 className="font-semibold mb-2">💰 Resumen de la Venta</h4>
                  <div className="space-y-1 text-sm">
                    <div className="flex justify-between">
                      <span>Producto:</span>
                      <span className="font-semibold">{productoSeleccionado.nombre_producto}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Precio Unitario:</span>
                      <span className="font-semibold">{formatMoney(productoSeleccionado.precio_venta)}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Cantidad:</span>
                      <span className="font-semibold">{formData.cantidad}</span>
                    </div>
                    <div className="flex justify-between text-lg font-bold border-t pt-2 mt-2">
                      <span>Total:</span>
                      <span className="text-blue-600">{formatMoney(calcularMontoTotal())}</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Modalidad:</span>
                      <span className="font-semibold">{formData.modalidad}</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Cuotas:</span>
                      <span className="font-semibold">{formData.cuotas}</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Valor por cuota:</span>
                      <span className="font-semibold">
                        {formatMoney(calcularMontoTotal() / formData.cuotas)}
                      </span>
                    </div>
                  </div>
                </div>
              )}

              <button
                type="submit"
                disabled={loading}
                className="w-full bg-green-500 text-white font-bold py-3 rounded-lg hover:bg-green-600 disabled:opacity-50 shadow-lg"
              >
                {loading ? 'Registrando...' : '✅ Confirmar Venta'}
              </button>
            </form>
          </div>
        )}

        {/* Lista de Ventas */}
        <h3 className="text-xl font-bold mb-4">Últimas Ventas</h3>
        <div className="space-y-3">
          {ventas.length === 0 ? (
            <div className="text-center py-12">
              <div className="text-6xl mb-4">📦</div>
              <p className="text-gray-600">No hay ventas registradas</p>
            </div>
          ) : (
            ventas.map((venta) => (
              <div key={venta.id_venta} className="bg-white rounded-lg shadow-md p-4">
                <div className="flex justify-between items-start mb-3">
                  <div>
                    <h4 className="font-bold text-lg">
                      {venta.producto?.nombre_producto || 'Producto'}
                    </h4>
                    <p className="text-sm text-gray-600">
                      Venta #{venta.id_venta} - {formatDate(venta.fecha_venta)}
                    </p>
                  </div>
                  <span
                    className={`px-3 py-1 rounded-full text-xs font-semibold ${
                      venta.estado_venta === 'ACTIVA'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    }`}
                  >
                    {venta.estado_venta}
                  </span>
                </div>

                <div className="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <p className="text-gray-600">Cantidad</p>
                    <p className="font-semibold">{venta.cantidad} unidad(es)</p>
                  </div>
                  <div>
                    <p className="text-gray-600">Total</p>
                    <p className="font-semibold text-blue-600">
                      {formatMoney(venta.monto_total)}
                    </p>
                  </div>
                  {venta.deudor && (
                    <div className="col-span-2">
                      <p className="text-gray-600">Cliente</p>
                      <p className="font-semibold">
                        {venta.deudor.nombres} {venta.deudor.apellidos}
                      </p>
                    </div>
                  )}
                </div>
              </div>
            ))
          )}
        </div>
      </main>
    </div>
  );
};

export default Ventas;