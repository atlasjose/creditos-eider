import axios from 'axios';

/**
 * ============================================
 * API SERVICE - Sistema de Créditos Eider
 * ============================================
 * 
 * Servicio para comunicarse con el backend Laravel
 * Optimizado para móvil
 */

// Base URL de la API
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Crear instancia de axios
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000, // 15 segundos timeout para móvil
});

// Interceptor para agregar token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Interceptor para manejar errores
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// ============================================
// AUTH
// ============================================

export const login = (email, password) => api.post('/login', { email, password });
export const logout = () => api.post('/logout');
export const me = () => api.get('/me');
export const register = (data) => api.post('/register', data);
export const changePassword = (oldPassword, newPassword) => 
  api.post('/change-password', { old_password: oldPassword, new_password: newPassword });

// ============================================
// DEUDORES
// ============================================

export const getDeudores = (params = {}) => api.get('/deudores', { params });
export const getDeudor = (id) => api.get(`/deudores/${id}`);
export const createDeudor = (data) => api.post('/deudores', data);
export const updateDeudor = (id, data) => api.put(`/deudores/${id}`, data);
export const deleteDeudor = (id) => api.delete(`/deudores/${id}`);
export const updateUbicacion = (id, latitud, longitud) => 
  api.put(`/deudores/${id}/ubicacion`, { latitud, longitud });
export const getDeudoresCercanos = (latitud, longitud, radio_km = 5) => 
  api.post('/deudores/cercanos', { latitud, longitud, radio_km });

// ============================================
// PAGOS
// ============================================

export const getPagos = (params = {}) => api.get('/pagos', { params });
export const getPago = (id) => api.get(`/pagos/${id}`);
export const createPago = (data) => api.post('/pagos', data);
export const deletePago = (id) => api.delete(`/pagos/${id}`);
export const getPagosDelCobrador = (id) => api.get(`/pagos/cobrador/${id}`);
export const getResumenDia = () => api.get('/pagos/resumen-dia');

// ============================================
// COMISIONES
// ============================================

export const getComisiones = (params = {}) => api.get('/comisiones', { params });
export const getComision = (id) => api.get(`/comisiones/${id}`);
export const calcularComision = (data) => api.post('/comisiones/calcular', data);
export const marcarComisionPagada = (id, fechaPago) => 
  api.post(`/comisiones/${id}/pagar`, { fecha_pago: fechaPago });
export const calcularComisionAutomatico = (periodicidad) => 
  api.post('/comisiones/calcular-automatico', { periodicidad });
export const getComisionesDelCobrador = (id) => api.get(`/comisiones/cobrador/${id}`);

// ============================================
// RUTAS
// ============================================

export const getRutas = (params = {}) => api.get('/rutas', { params });
export const getRuta = (id) => api.get(`/rutas/${id}`);
export const createRuta = (data) => api.post('/rutas', data);
export const updateRuta = (id, data) => api.put(`/rutas/${id}`, data);
export const asignarCobrador = (id, idCobrador) => 
  api.post(`/rutas/${id}/asignar-cobrador`, { id_cobrador: idCobrador });
export const desactivarRuta = (id) => api.post(`/rutas/${id}/desactivar`);
export const activarRuta = (id) => api.post(`/rutas/${id}/activar`);
export const getDeudoresDeRuta = (id) => api.get(`/rutas/${id}/deudores`);
export const getRutasDelCobrador = (id) => api.get(`/rutas/cobrador/${id}`);

// ============================================
// PRODUCTOS
// ============================================

export const getProductos = (params = {}) => api.get('/productos', { params });
export const getProducto = (id) => api.get(`/productos/${id}`);
export const createProducto = (data) => api.post('/productos', data);
export const updateProducto = (id, data) => api.put(`/productos/${id}`, data);
export const deleteProducto = (id) => api.delete(`/productos/${id}`);
export const activarProducto = (id) => api.post(`/productos/${id}/activar`);
export const getMasVendidos = () => api.get('/productos/mas-vendidos');

// ============================================
// VENTAS
// ============================================

export const getVentas = (params = {}) => api.get('/ventas', { params });
export const getVenta = (id) => api.get(`/ventas/${id}`);
export const createVenta = (data) => api.post('/ventas', data);
export const cancelarVenta = (id) => api.post(`/ventas/${id}/cancelar`);
export const getVentasDelVendedor = (id) => api.get(`/ventas/vendedor/${id}`);

// ============================================
// UTILIDADES
// ============================================

export const testAPI = () => api.get('/test');

export default api;